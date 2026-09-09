<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\WebhookLog;
use App\Services\WebhookForwarder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DomainController extends Controller
{
    public function index()
    {
        // Data tabel diambil lewat AJAX (lihat data()).
        return view('panel.domains.index');
    }

    /**
     * Endpoint server-side DataTables.
     * Filter & sort dibatasi ke kolom ber-index supaya paging tetap ringan
     * saat jumlah domain sudah besar.
     */
    public function data(Request $request)
    {
        $sortable = [
            'name'       => 'name',
            'alias'      => 'alias',
            'provider'   => 'provider',
            'notes'      => 'notes',
            'target_url' => 'target_url',
            'is_active'  => 'is_active',
        ];

        $recordsTotal = Domain::count();

        $query = Domain::query();

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }
        if ($request->input('is_active') !== null && $request->input('is_active') !== '') {
            $query->where('is_active', (int) $request->input('is_active'));
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('alias', $search)
                  ->orWhere('name', 'like', $search . '%')   // prefix -> index terpakai
                  ->orWhere('domain', 'like', $search . '%')
                  ->orWhere('target_url', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderCol = $request->input('columns.' . $request->input('order.0.column') . '.data');
        $orderDir = strtolower((string) $request->input('order.0.dir')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortable[$orderCol] ?? 'name', $orderDir)->orderBy('id', $orderDir);

        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;

        $rows = $query->offset(max(0, (int) $request->input('start', 0)))->limit($length)->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows->map(fn (Domain $d) => [
                'id'         => $d->id,
                'name'       => $d->name,
                'domain'     => $d->domain,
                'alias'      => $d->alias,
                'provider'   => $d->provider,
                'notes'      => $d->notes,
                'target_url' => $d->target_url,
                'is_active'  => (bool) $d->is_active,
                'logs_url'   => route('panel.logs.index', ['domain_id' => $d->id]),
                'test_url'   => route('panel.domains.test', $d),
            ]),
        ]);
    }

    /** Data satu domain untuk mengisi modal edit. */
    public function show(Domain $domain)
    {
        return response()->json([
            'id'         => $domain->id,
            'alias'      => $domain->alias,
            'domain'     => $domain->domain,
            'provider'   => $domain->provider,
            'target_url' => $domain->target_url,
            'notes'      => $domain->notes,
            'is_active'  => (bool) $domain->is_active,
            'has_logs'   => $domain->logs()->exists(),
        ]);
    }

    public function create()
    {
        return view('panel.domains.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $host = $this->hostOf($data['target_url']);

        if (Domain::where('domain', $host)->where('provider', $data['provider'])->exists()) {
            return $this->duplicateResponse($request, $data['provider']);
        }

        Domain::create([
            'name'       => $host,
            'domain'     => $host,
            'alias'      => Domain::generateAlias(),
            'provider'   => $data['provider'],
            'target_url' => $data['target_url'],
            'secret_key' => '-',
            'is_active'  => $request->boolean('is_active'),
            'notes'      => $data['notes'] ?? null,
        ]);

        return $this->okResponse($request, 'Domain berhasil ditambahkan.');
    }

    public function edit(Domain $domain)
    {
        return view('panel.domains.edit', compact('domain'));
    }

    public function update(Request $request, Domain $domain)
    {
        $data = $this->validated($request);

        $host = $this->hostOf($data['target_url']);

        if (Domain::where('domain', $host)
            ->where('provider', $data['provider'])
            ->where('id', '!=', $domain->id)
            ->exists()) {
            return $this->duplicateResponse($request, $data['provider']);
        }

        $domain->update([
            'name'       => $host,
            'domain'     => $host,
            // Domain lama yang aliasnya kosong (mis. hasil insert manual)
            // ikut terisi begitu disimpan.
            'alias'      => $domain->alias ?: Domain::generateAlias(),
            'provider'   => $data['provider'],
            'target_url' => $data['target_url'],
            'secret_key' => '-',
            'is_active'  => $request->boolean('is_active'),
            'notes'      => $data['notes'] ?? null,
        ]);

        return $this->okResponse($request, 'Domain berhasil diperbarui.');
    }

    public function destroy(Request $request, Domain $domain)
    {
        $domain->delete();

        return $this->okResponse($request, 'Domain berhasil dihapus.');
    }

    public function testForm(Domain $domain)
    {
        return view('panel.domains.test', compact('domain'));
    }

    public function testSend(Request $request, Domain $domain, WebhookForwarder $forwarder)
    {
        $request->validate([
            'payload' => 'required|json',
        ]);

        $payload = json_decode($request->payload, true);
        $result  = $forwarder->forward($domain, $payload);

        WebhookLog::create([
            'domain_id'     => $domain->id,
            'provider'      => $domain->provider,
            'event_type'    => 'test',
            'custom_field1' => $domain->domain,
            'payload'       => $payload,
            'response_code' => $result['response_code'],
            'duration_ms'   => $result['duration_ms'],
            'status'        => $result['status'],
            'error_message' => $result['error_message'],
        ]);

        return back()->with([
            'test_result' => $result,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'provider'   => ['required', Rule::in(Domain::PROVIDERS)],
            'target_url' => 'required|url',
            'notes'      => 'nullable|string|max:255',
        ]);
    }

    private function hostOf(string $url): string
    {
        return parse_url($url, PHP_URL_HOST) ?: $url;
    }

    private function duplicateResponse(Request $request, string $provider)
    {
        $message = 'Domain ini sudah terdaftar untuk provider ' . ucfirst($provider) . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors'  => ['target_url' => [$message]],
            ], 422);
        }

        return back()->withErrors(['target_url' => $message])->withInput();
    }

    private function okResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('panel.domains.index')->with('success', $message);
    }
}
