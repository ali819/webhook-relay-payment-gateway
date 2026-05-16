<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WebhookLog;
use App\Services\WebhookForwarder;

class DomainController extends Controller
{
    public function index()
    {
        $domains = Domain::latest()->paginate(10);
        return view('panel.domains.index', compact('domains'));
    }

    public function create()
    {
        return view('panel.domains.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'domain'     => ['required', 'string', 'max:100', 'unique:domains,domain', 'regex:/^[a-z0-9\-\.]+$/'],
            'provider'   => 'required|in:midtrans,xendit',
            'target_url' => 'required|url',
            'secret_key' => 'required|string',
        ]);

        Domain::create([
            'name'       => $request->name,
            'domain'     => $request->domain,
            'provider'   => $request->provider,
            'target_url' => $request->target_url,
            'secret_key' => $request->secret_key,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('panel.domains.index')
            ->with('success', 'Domain berhasil ditambahkan.');
    }

    public function edit(Domain $domain)
    {
        return view('panel.domains.edit', compact('domain'));
    }

    public function update(Request $request, Domain $domain)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'domain'     => ['required', 'string', 'max:100', Rule::unique('domains', 'domain')->ignore($domain->id), 'regex:/^[a-z0-9\-\.]+$/'],
            'provider'   => 'required|in:midtrans,xendit',
            'target_url' => 'required|url',
            'secret_key' => 'required|string',
        ]);

        $domain->update([
            'name'       => $request->name,
            'domain'     => $request->domain,
            'provider'   => $request->provider,
            'target_url' => $request->target_url,
            'secret_key' => $request->secret_key,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.domains.index')
            ->with('success', 'Domain berhasil diperbarui.');
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return redirect()->route('panel.domains.index')
            ->with('success', 'Domain berhasil dihapus.');
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

}
