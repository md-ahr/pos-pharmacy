<?php

namespace Database\Factories;

use App\Enums\RegisterShiftStatus;
use App\Models\Branch;
use App\Models\Register;
use App\Models\RegisterShift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegisterShift>
 */
class RegisterShiftFactory extends Factory
{
    protected $model = RegisterShift::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => Branch::factory(),
            'register_id' => Register::factory(),
            'opened_by_user_id' => User::factory(),
            'closed_by_user_id' => null,
            'status' => RegisterShiftStatus::Open,
            'opened_at' => now(),
            'closed_at' => null,
            'opening_float' => '100.00',
            'expected_cash' => null,
            'counted_cash' => null,
            'cash_variance' => null,
            'card_total' => null,
            'sales_total' => null,
            'notes' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegisterShiftStatus::Closed,
            'closed_at' => now(),
            'expected_cash' => '150.00',
            'counted_cash' => '150.00',
            'cash_variance' => '0.00',
            'sales_total' => '50.00',
        ]);
    }
}
