<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PanelDataTablesTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    private function makeDomain(string $provider = 'doku'): Domain
    {
        return Domain::create([
            'name'       => 'toko-a.com',
            'domain'     => 'toko-a.com',
            'provider'   => $provider,
            'target_url' => 'https://toko-a.com/webhook',
            'secret_key' => '-',
            'is_active'  => true,
        ]);
    }

    public function test_domain_datatable_returns_server_side_shape(): void
    {
        $this->actingAsAdmin();
        $this->makeDomain();

        $res = $this->getJson(route('panel.domains.data', [
            'draw' => 1, 'start' => 0, 'length' => 10,
            'order' => [['column' => 0, 'dir' => 'asc']],
            'columns' => [['data' => 'name']],
        ]));

        $res->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.provider', 'doku');
    }

    public function test_domain_crud_over_ajax(): void
    {
        $this->actingAsAdmin();

        $this->postJson(route('panel.domains.store'), [
            'provider'   => 'doku',
            'target_url' => 'https://shop-b.com/callback',
            'notes'      => 'Production',
            'is_active'  => 1,
        ])->assertOk()->assertJsonPath('success', true);

        $domain = Domain::firstWhere('domain', 'shop-b.com');
        $this->assertSame('doku', $domain->provider);

        // duplikat domain+provider ditolak sebagai 422 JSON
        $this->postJson(route('panel.domains.store'), [
            'provider'   => 'doku',
            'target_url' => 'https://shop-b.com/lain',
            'is_active'  => 1,
        ])->assertStatus(422)->assertJsonValidationErrors('target_url');

        $this->putJson(route('panel.domains.update', $domain), [
            'provider'   => 'midtrans',
            'target_url' => 'https://shop-b.com/callback',
            'is_active'  => 0,
        ])->assertOk();

        $this->assertFalse($domain->fresh()->is_active);

        $this->deleteJson(route('panel.domains.destroy', $domain))->assertOk();
        $this->assertModelMissing($domain);
    }

    public function test_log_datatable_filters_and_pagination(): void
    {
        $this->actingAsAdmin();
        $domain = $this->makeDomain();

        foreach (['success', 'failed', 'success'] as $status) {
            WebhookLog::create([
                'domain_id' => $domain->id,
                'provider'  => 'doku',
                'payload'   => ['a' => 1],
                'status'    => $status,
            ]);
        }

        $base = [
            'draw' => 2, 'start' => 0, 'length' => 2,
            'order' => [['column' => 0, 'dir' => 'desc']],
            'columns' => [['data' => 'created_at']],
        ];

        $this->getJson(route('panel.logs.data', $base))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 3)
            ->assertJsonCount(2, 'data');

        $this->getJson(route('panel.logs.data', $base + ['status' => 'failed']))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1);
    }

    public function test_log_prune_keeps_newest(): void
    {
        $this->actingAsAdmin();
        $domain = $this->makeDomain();

        foreach (range(1, 60) as $i) {
            WebhookLog::create([
                'domain_id' => $domain->id,
                'provider'  => 'doku',
                'payload'   => ['i' => $i],
                'status'    => 'success',
            ]);
        }

        $this->deleteJson(route('panel.logs.prune'), ['keep' => 50])
            ->assertOk()
            ->assertJsonPath('deleted', 10);

        $this->assertSame(50, WebhookLog::count());
    }

    public function test_doku_webhook_is_relayed_and_logged(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $domain = $this->makeDomain();

        $this->withHeaders([
            'Client-Id'         => 'BRN-0001',
            'Request-Id'        => 'req-1',
            'Request-Timestamp' => '2026-09-09T00:00:00Z',
            'Signature'         => 'HMACSHA256=abc',
        ])->postJson(route('handleApi'), [
            'order'           => ['invoice_number' => 'INV-1', 'amount' => 100000],
            'transaction'     => ['status' => 'SUCCESS'],
            'service'         => ['id' => 'VIRTUAL_ACCOUNT'],
            'additional_info' => ['domain' => 'toko-a.com'],
        ])->assertOk();

        $log = WebhookLog::latest('id')->first();
        $this->assertSame('doku', $log->provider);
        $this->assertSame($domain->id, $log->domain_id);
        $this->assertSame('SUCCESS', $log->event_type);
        $this->assertSame('toko-a.com', $log->custom_field1);
        $this->assertSame('success', $log->status);

        Http::assertSent(fn ($req) => $req->hasHeader('Signature', 'HMACSHA256=abc')
            && $req->hasHeader('Client-Id', 'BRN-0001'));
    }

    public function test_doku_domain_falls_back_to_invoice_prefix(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->makeDomain();

        $this->withHeaders(['Client-Id' => 'BRN-0001', 'Signature' => 'x'])
            ->postJson(route('handleApi'), [
                'order'       => ['invoice_number' => 'toko-a.com|INV-2'],
                'transaction' => ['status' => 'SUCCESS'],
            ])->assertOk();

        $this->assertSame('toko-a.com', WebhookLog::latest('id')->first()->custom_field1);
    }
}
