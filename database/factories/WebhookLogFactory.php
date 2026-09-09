<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<WebhookLog>
 */
class WebhookLogFactory extends Factory
{
    protected $model = WebhookLog::class;

    /** Cache domain supaya seeding ribuan log tidak query per baris. */
    private static ?Collection $pool = null;

    public function definition(): array
    {
        return $this->attributesFor($this->randomDomain());
    }

    /** Log yang konsisten untuk satu domain tertentu (provider, payload, key). */
    public function forDomain(Domain $domain): static
    {
        return $this->state(fn () => $this->attributesFor($domain));
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public static function flushPool(): void
    {
        static::$pool = null;
    }

    private function randomDomain(): Domain
    {
        static::$pool ??= Domain::all();

        if (static::$pool->isEmpty()) {
            static::$pool = collect([Domain::factory()->create()]);
        }

        return static::$pool->random();
    }

    private function attributesFor(Domain $domain): array
    {
        $status    = $this->faker->randomElement(['success', 'success', 'success', 'failed', 'domain_not_found']);
        $ok        = $status === 'success';
        $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            // Log "domain_not_found" memang tidak punya domain terkait.
            'domain_id'     => $status === 'domain_not_found' ? null : $domain->id,
            'provider'      => $domain->provider,
            'event_type'    => $this->eventTypeFor($domain->provider, $ok),
            'custom_field1' => $domain->domain,
            'payload'       => $this->payloadFor($domain, $ok),
            'response_code' => $ok ? 200 : $this->faker->randomElement([404, 500, 502, null]),
            'duration_ms'   => $this->faker->numberBetween(30, 3000),
            'status'        => $status,
            'error_message' => $ok ? null : 'Connection timed out setelah 10 detik.',
            'created_at'    => $createdAt,
            'updated_at'    => $createdAt,
        ];
    }

    /** Event mengikuti status log supaya data dummy tidak saling bertentangan. */
    private function eventTypeFor(string $provider, bool $ok): string
    {
        return match ($provider) {
            'midtrans' => $ok
                ? $this->faker->randomElement(['settlement', 'capture'])
                : $this->faker->randomElement(['pending', 'expire', 'deny']),
            'xendit'   => $ok
                ? $this->faker->randomElement(['payment.succeeded', 'invoice.paid'])
                : $this->faker->randomElement(['invoice.expired', 'payment.failed']),
            'doku'     => $ok ? 'SUCCESS' : $this->faker->randomElement(['PENDING', 'FAILED']),
            default    => 'unknown',
        };
    }

    private function payloadFor(Domain $domain, bool $ok): array
    {
        $invoice = 'INV-' . $this->faker->numberBetween(1000, 9999);
        $amount  = $this->faker->numberBetween(10000, 5000000);

        return match ($domain->provider) {
            'midtrans' => [
                'order_id'           => $invoice,
                'gross_amount'       => number_format($amount, 2, '.', ''),
                'payment_type'       => $this->faker->randomElement(['gopay', 'bank_transfer', 'qris']),
                'transaction_status' => $ok ? 'settlement' : 'expire',
                'custom_field1'      => $domain->domain,
                'signature_key'      => $this->faker->sha256(),
            ],
            'xendit' => [
                'id'          => $this->faker->uuid(),
                'external_id' => $invoice,
                'status'      => $ok ? 'PAID' : 'EXPIRED',
                'amount'      => $amount,
                'metadata'    => ['domain' => $domain->domain],
            ],
            default => [
                'order'           => ['invoice_number' => $invoice, 'amount' => $amount],
                'transaction'     => ['status' => $ok ? 'SUCCESS' : 'FAILED', 'date' => now()->toIso8601String()],
                'service'         => ['id' => $this->faker->randomElement(['VIRTUAL_ACCOUNT', 'QRIS', 'CREDIT_CARD'])],
                'additional_info' => ['domain' => $domain->domain],
            ],
        };
    }
}
