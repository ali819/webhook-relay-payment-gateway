<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\WebhookLog;
use App\Services\WebhookForwarder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index()
    {
        // Tabel diisi lewat AJAX (lihat data()); di sini cukup daftar domain
        // untuk dropdown filter.
        $domains = Domain::orderBy('name')->get(['id', 'name']);

        return view('panel.logs.index', compact('domains'));
    }

    /**
     * Endpoint server-side DataTables untuk log.
     *
     * Catatan performa: recordsTotal memakai perkiraan baris dari
     * information_schema saat tabel sudah sangat besar, karena COUNT(*)
     * penuh pada tabel jutaan baris memblokir setiap draw.
     */
    public function data(Request $request)
    {
        $sortable = [
            'created_at'    => 'webhook_logs.created_at',
            'provider'      => 'webhook_logs.provider',
            'event_type'    => 'webhook_logs.event_type',
            'custom_field1' => 'webhook_logs.custom_field1',
            'status'        => 'webhook_logs.status',
            'response_code' => 'webhook_logs.response_code',
            'duration_ms'   => 'webhook_logs.duration_ms',
        ];

        $query = WebhookLog::query()->with('domain:id,name');

        $filtered = false;

        if ($request->filled('domain_id')) {
            $query->where('domain_id', (int) $request->input('domain_id'));
            $filtered = true;
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
            $filtered = true;
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
            $filtered = true;
        }
        if ($request->filled('date_from')) {
            $query->where('webhook_logs.created_at', '>=', $request->input('date_from') . ' 00:00:00');
            $filtered = true;
        }
        if ($request->filled('date_to')) {
            $query->where('webhook_logs.created_at', '<=', $request->input('date_to') . ' 23:59:59');
            $filtered = true;
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $filtered = true;
            $query->where(function ($q) use ($search) {
                $q->where('custom_field1', 'like', $search . '%')  // prefix -> index terpakai
                  ->orWhere('event_type', 'like', $search . '%');
            });
        }

        $recordsTotal = $this->totalLogs();
        $recordsFiltered = $filtered ? (clone $query)->count() : $recordsTotal;

        $orderCol = $request->input('columns.' . $request->input('order.0.column') . '.data');
        $orderDir = strtolower((string) $request->input('order.0.dir')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortable[$orderCol] ?? 'webhook_logs.id', $orderDir);

        $length = (int) $request->input('length', 20);
        $length = $length > 0 ? min($length, 200) : 20;

        $rows = $query->offset(max(0, (int) $request->input('start', 0)))->limit($length)->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows->map(fn (WebhookLog $log) => [
                'id'            => $log->id,
                'created_at'    => $log->created_at?->format('d/m/Y H:i:s'),
                'domain'        => $log->domain?->name,
                'provider'      => $log->provider,
                'event_type'    => $log->event_type,
                'custom_field1' => $log->custom_field1,
                'status'        => $log->status,
                'response_code' => $log->response_code,
                'duration_ms'   => $log->duration_ms,
                'show_url'      => route('panel.logs.show', $log),
            ]),
        ]);
    }

    /**
     * COUNT(*) penuh untuk tabel kecil, estimasi InnoDB untuk tabel besar.
     */
    private function totalLogs(): int
    {
        if (DB::getDriverName() !== 'mysql') {
            return WebhookLog::count();
        }

        $estimate = (int) optional(
            \DB::selectOne(
                'SELECT TABLE_ROWS AS rows_est FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                ['webhook_logs']
            )
        )->rows_est;

        return $estimate > 500000 ? $estimate : WebhookLog::count();
    }

    public function show(Request $request, WebhookLog $log)
    {
        $log->loadMissing('domain:id,name');

        if ($request->expectsJson()) {
            return response()->json([
                'id'            => $log->id,
                'created_at'    => $log->created_at?->format('d/m/Y H:i:s'),
                'domain'        => $log->domain?->name,
                'provider'      => $log->provider,
                'event_type'    => $log->event_type,
                'custom_field1' => $log->custom_field1,
                'status'        => $log->status,
                'response_code' => $log->response_code,
                'duration_ms'   => $log->duration_ms,
                'error_message' => $log->error_message,
                'payload'       => $log->payload,
                'retry_url'     => route('panel.logs.retry', $log),
                'can_retry'     => (bool) $log->domain,
            ]);
        }

        return view('panel.logs.show', compact('log'));
    }

    public function retry(Request $request, WebhookLog $log, WebhookForwarder $forwarder)
    {
        if (!$log->domain) {
            $message = 'Domain tidak ditemukan, tidak bisa retry.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['error' => $message]);
        }

        $result = $forwarder->forward($log->domain, $log->payload);

        $log->update([
            'response_code' => $result['response_code'],
            'duration_ms'   => $result['duration_ms'],
            'status'        => $result['status'],
            'error_message' => $result['error_message'],
        ]);

        $message = 'Webhook berhasil dikirim ulang. Status: ' . $result['status'];

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message, 'status' => $result['status']])
            : back()->with('success', $message);
    }

    public function prune(Request $request)
    {
        $request->validate([
            'keep' => 'required|in:1000,100,50',
        ]);

        $keep = (int) $request->keep;

        // Pakai batas id, bukan whereNotIn dengan ribuan id — jauh lebih murah
        // dan langsung memakai primary key saat tabel sudah besar.
        $cutoffId = WebhookLog::orderByDesc('id')->offset($keep - 1)->limit(1)->value('id');

        $deleted = $cutoffId ? WebhookLog::where('id', '<', $cutoffId)->delete() : 0;

        $message = "Berhasil menghapus {$deleted} log lama.";

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message, 'deleted' => $deleted])
            : back()->with('success', $message);
    }
}
