<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function displayName(): string
    {
        if ($this->name !== null && $this->name !== '') {
            return $this->name;
        }

        if ($this->phone !== null && $this->phone !== '') {
            return $this->phone;
        }

        if ($this->email !== null && $this->email !== '') {
            return $this->email;
        }

        return 'Walk-in customer';
    }
}
