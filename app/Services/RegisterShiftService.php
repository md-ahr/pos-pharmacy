<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\RegisterShiftStatus;
use App\Enums\SaleStatus;
use App\Models\Branch;
use App\Models\Register;
use App\Models\RegisterShift;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegisterShiftService
{
    public function ensureRegister(Branch $branch): Register
    {
        return Register::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'name' => 'Main Register',
            ],
            [
                'tenant_id' => $branch->tenant_id,
                'is_active' => true,
            ],
        );
    }

    public function openShift(Branch $branch, User $user, string $openingFloat): RegisterShift
    {
        if ($branch->tenant_id !== $user->tenant_id) {
            throw new AuthorizationException('Branch does not belong to your pharmacy.');
        }

        $register = $this->ensureRegister($branch);

        if ($this->openShiftForBranch($branch) !== null) {
            throw new InvalidArgumentException('A register shift is already open for this branch.');
        }

        return RegisterShift::query()->create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'register_id' => $register->id,
            'opened_by_user_id' => $user->id,
            'status' => RegisterShiftStatus::Open,
            'opened_at' => now(),
            'opening_float' => number_format((float) $openingFloat, 2, '.', ''),
        ]);
    }

    public function closeShift(RegisterShift $shift, User $user, string $countedCash, ?string $notes = null): RegisterShift
    {
        if ($shift->status !== RegisterShiftStatus::Open) {
            throw new InvalidArgumentException('This shift is already closed.');
        }

        if ($shift->tenant_id !== $user->tenant_id) {
            throw new AuthorizationException('You cannot close a shift from another pharmacy.');
        }

        return DB::transaction(function () use ($shift, $user, $countedCash, $notes): RegisterShift {
            $totals = $this->calculateShiftTotals($shift);
            $expectedCash = bcadd($shift->opening_float, $totals['cash_sales'], 2);
            $variance = bcsub($countedCash, $expectedCash, 2);

            $shift->update([
                'closed_by_user_id' => $user->id,
                'status' => RegisterShiftStatus::Closed,
                'closed_at' => now(),
                'expected_cash' => $expectedCash,
                'counted_cash' => number_format((float) $countedCash, 2, '.', ''),
                'cash_variance' => $variance,
                'card_total' => $totals['card_sales'],
                'sales_total' => $totals['sales_total'],
                'notes' => $notes,
            ]);

            return $shift->fresh();
        });
    }

    public function openShiftForBranch(Branch $branch): ?RegisterShift
    {
        return RegisterShift::query()
            ->where('branch_id', $branch->id)
            ->where('status', RegisterShiftStatus::Open)
            ->latest('opened_at')
            ->first();
    }

    /**
     * @return array{cash_sales: string, card_sales: string, sales_total: string}
     */
    public function calculateShiftTotals(RegisterShift $shift): array
    {
        $cashSales = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.branch_id', $shift->branch_id)
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->where('sale_payments.method', PaymentMethod::Cash->value)
            ->where('sales.sold_at', '>=', $shift->opened_at)
            ->when($shift->closed_at, fn ($query) => $query->where('sales.sold_at', '<=', $shift->closed_at))
            ->sum('sale_payments.amount');

        $cardSales = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.branch_id', $shift->branch_id)
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->where('sale_payments.method', PaymentMethod::Card->value)
            ->where('sales.sold_at', '>=', $shift->opened_at)
            ->when($shift->closed_at, fn ($query) => $query->where('sales.sold_at', '<=', $shift->closed_at))
            ->sum('sale_payments.amount');

        $salesTotal = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.branch_id', $shift->branch_id)
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->where('sales.sold_at', '>=', $shift->opened_at)
            ->when($shift->closed_at, fn ($query) => $query->where('sales.sold_at', '<=', $shift->closed_at))
            ->sum('sale_payments.amount');

        return [
            'cash_sales' => number_format((float) $cashSales, 2, '.', ''),
            'card_sales' => number_format((float) $cardSales, 2, '.', ''),
            'sales_total' => number_format((float) $salesTotal, 2, '.', ''),
        ];
    }

    /**
     * @return array{
     *     shift: RegisterShift|null,
     *     opening_float: string,
     *     cash_sales: string,
     *     expected_cash: string
     * }
     */
    public function currentShiftSummary(Branch $branch): array
    {
        $shift = $this->openShiftForBranch($branch);

        if ($shift === null) {
            return [
                'shift' => null,
                'opening_float' => '0.00',
                'cash_sales' => '0.00',
                'expected_cash' => '0.00',
            ];
        }

        $totals = $this->calculateShiftTotals($shift);
        $expectedCash = bcadd($shift->opening_float, $totals['cash_sales'], 2);

        return [
            'shift' => $shift,
            'opening_float' => (string) $shift->opening_float,
            'cash_sales' => $totals['cash_sales'],
            'expected_cash' => $expectedCash,
        ];
    }
}
