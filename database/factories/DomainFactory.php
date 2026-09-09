<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        $host = $this->faker->unique()->domainName();

        return [
            'name'       => $host,
            'domain'     => $host,
            'provider'   => $this->faker->randomElement(Domain::PROVIDERS),
            'target_url' => 'https://' . $host . '/webhook/payment',
            'secret_key' => '-',
            'is_active'  => $this->faker->boolean(80),
            'notes'      => $this->faker->randomElement(['Production', 'Sandbox', 'Local Test', null]),
        ];
    }

    public function provider(string $provider): static
    {
        return $this->state(fn () => ['provider' => $provider]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
