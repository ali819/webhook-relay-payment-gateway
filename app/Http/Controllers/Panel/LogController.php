<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\WebhookLog;
use App\Services\WebhookForwarder;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = WebhookLog::with('domain')->latest();

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        $logs    = $query->paginate(20)->withQueryString();
        $domains = Domain::orderBy('name')->get();

        return view('panel.logs.index', compact('logs', 'domains'));
    }

    public function show(WebhookLog $log)
    {
        return view('panel.logs.show', compact('log'));
    }

    public function retry(WebhookLog $log, WebhookForwarder $forwarder)
    {
        if (!$log->domain) {
            return back()->withErrors(['error' => 'Domain tidak ditemukan, tidak bisa retry.']);
        }

        $result = $forwarder->forward($log->domain, $log->payload);

        $log->update([
            'response_code' => $result['response_code'],
            'duration_ms'   => $result['duration_ms'],
            'status'        => $result['status'],
            'error_message' => $result['error_message'],
        ]);

        return back()->with('success', 'Webhook berhasil dikirim ulang. Status: ' . $result['status']);
    }

    public function prune(Request $request)
    {
        $request->validate([
            'keep' => 'required|in:1000,100,50',
        ]);

        $keep    = (int) $request->keep;
        $lastIds = WebhookLog::latest()->limit($keep)->pluck('id');
        $deleted = WebhookLog::whereNotIn('id', $lastIds)->delete();

        return back()->with('success', "Berhasil menghapus {$deleted} log lama.");
    }

}
