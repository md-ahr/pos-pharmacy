<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalePayment>
 */
class SalePaymentFactory extends Factory
{
    protected $model = SalePayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'amount' => fake()->randomFloat(2, 10, 500),
            'reference' => fake()->optional()->uuid(),
            'paid_at' => now(),
        ];
    }
}
