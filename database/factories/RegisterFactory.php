<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Register;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Register>
 */
class RegisterFactory extends Factory
{
    protected $model = Register::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'branch_id' => Branch::factory(),
            'name' => 'Main Register',
            'is_active' => true,
        ];
    }
}
