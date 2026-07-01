<?php

namespace App\Models;

use App\Enums\RegisterShiftStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\RegisterShiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisterShift extends Model
{
    /** @use HasFactory<RegisterShiftFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'register_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'status',
        'opened_at',
        'closed_at',
        'opening_float',
        'expected_cash',
        'counted_cash',
        'cash_variance',
        'card_total',
        'sales_total',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RegisterShiftStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_float' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'cash_variance' => 'decimal:2',
            'card_total' => 'decimal:2',
            'sales_total' => 'decimal:2',
        ];
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}
