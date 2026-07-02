<?php

namespace App\Services;

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\SaleStatus;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private StockDeductionService $stockDeduction,
        private UnitConversionService $unitConversion,
        private InvoiceNumberService $invoiceNumbers,
        private PosPricingService $pricing,
        private TenantSettingsService $tenantSettings,
        private RegisterShiftService $registerShifts,
        private AuditLogService $auditLog,
    ) {}

    /**
     * @param  list<CartLine>  $lines
     * @param  list<PaymentLine>  $payments
     */
    public function complete(
        Branch $branch,
        User $cashier,
        array $lines,
        array $payments,
        string $saleDiscount = '0.00',
        bool $prescriptionRequired = false,
        ?string $prescriberName = null,
        ?string $prescriberRegNo = null,
        ?int $customerId = null,
        ?Sale $heldSale = null,
    ): Sale {
        if ($lines === []) {
            throw new InvalidArgumentException('Cart is empty.');
        }

        $this->assertOpenRegisterShift($branch);

        return DB::transaction(function () use (
            $branch,
            $cashier,
            $lines,
            $payments,
            $saleDiscount,
            $prescriptionRequired,
            $prescriberName,
            $prescriberRegNo,
            $customerId,
            $heldSale,
        ): Sale {
            $tenant = Tenant::query()->findOrFail($branch->tenant_id);
            $pricedLines = $this->priceLines($branch, $lines);
            $totals = $this->calculateTotals($branch, $pricedLines, $lines, $saleDiscount);
            $lineAmounts = $this->allocateLineAmounts($pricedLines, $lines, $saleDiscount, $totals['net'], $totals['tax']);

            $this->assertPaymentsCoverTotal($payments, $totals['total']);

            $paidAmount = $this->sumPayments($payments);
            $changeAmount = bcsub($paidAmount, $totals['total'], 2);

            if ($heldSale !== null) {
                $this->assertHeldSale($heldSale, $branch);
                $heldSale->items()->delete();
                $heldSale->payments()->delete();
                $sale = $heldSale;
            } else {
                $sale = new Sale;
                $sale->tenant_id = $tenant->id;
                $sale->invoice_no = $this->invoiceNumbers->next($tenant);
            }

            $sale->fill([
                'branch_id' => $branch->id,
                'user_id' => $cashier->id,
                'customer_id' => $customerId,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => bcadd($totals['line_discount_total'], $saleDiscount, 2),
                'tax_amount' => $totals['tax'],
                'total' => $totals['total'],
                'paid_amount' => $paidAmount,
                'change_amount' => bccomp($changeAmount, '0.00', 2) > 0 ? $changeAmount : '0.00',
                'status' => SaleStatus::Completed,
                'prescription_required' => $prescriptionRequired,
                'prescriber_name' => $prescriberName,
                'prescriber_reg_no' => $prescriberRegNo,
                'sold_at' => now(),
            ]);
            $sale->save();

            foreach ($pricedLines as $index => $pricedLine) {
                $cartLine = $lines[$index];
                $product = $pricedLine['product'];
                $unit = $pricedLine['unit'];
                $quantityBase = $this->unitConversion->toBaseUnits($product, $unit, $cartLine->quantity);
                $lineAmount = $lineAmounts[$index];
                $overrideBatch = $cartLine->batchId !== null
                    ? Batch::query()->findOrFail($cartLine->batchId)
                    : null;

                $deductions = $this->stockDeduction->deduct($branch, $product, $quantityBase, $overrideBatch);

                if ($deductions->count() === 1) {
                    SaleItem::query()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'batch_id' => $deductions->first()->batchId,
                        'product_unit_id' => $unit?->id,
                        'quantity' => $cartLine->quantity,
                        'quantity_base' => $quantityBase,
                        'unit_price' => $pricedLine['unit_price'],
                        'discount_amount' => $lineAmount['discount_amount'],
                        'tax_amount' => $lineAmount['tax_amount'],
                        'line_total' => $lineAmount['line_total'],
                        'is_prescription_item' => $cartLine->isPrescriptionItem,
                    ]);

                    continue;
                }

                $remainingDiscount = $lineAmount['discount_amount'];
                $remainingTax = $lineAmount['tax_amount'];

                foreach ($deductions as $deductionIndex => $deduction) {
                    $isLast = $deductionIndex === $deductions->count() - 1;
                    $portionDiscount = $isLast
                        ? $remainingDiscount
                        : bcmul(bcdiv((string) $deduction->quantityDeducted, (string) $quantityBase, 6), $lineAmount['discount_amount'], 2);
                    $portionTax = $isLast
                        ? $remainingTax
                        : bcmul(bcdiv((string) $deduction->quantityDeducted, (string) $quantityBase, 6), $lineAmount['tax_amount'], 2);
                    $portionSubtotal = bcmul($pricedLine['unit_price'], bcdiv((string) $deduction->quantityDeducted, (string) max($unit?->conversion_factor ?? 1, 1), 6), 2);
                    $portionNet = bcsub($portionSubtotal, $portionDiscount, 2);

                    SaleItem::query()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'batch_id' => $deduction->batchId,
                        'product_unit_id' => $unit?->id,
                        'quantity' => (int) bcdiv((string) $deduction->quantityDeducted, (string) max($unit?->conversion_factor ?? 1, 1)),
                        'quantity_base' => $deduction->quantityDeducted,
                        'unit_price' => $pricedLine['unit_price'],
                        'discount_amount' => $portionDiscount,
                        'tax_amount' => $portionTax,
                        'line_total' => bcadd($portionNet, $portionTax, 2),
                        'is_prescription_item' => $cartLine->isPrescriptionItem,
                    ]);

                    $remainingDiscount = bcsub($remainingDiscount, $portionDiscount, 2);
                    $remainingTax = bcsub($remainingTax, $portionTax, 2);
                }
            }

            foreach ($payments as $payment) {
                SalePayment::query()->create([
                    'sale_id' => $sale->id,
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                    'paid_at' => now(),
                ]);
            }

            $completedSale = $sale->fresh(['items', 'payments', 'branch', 'cashier']);

            $this->auditLog->log('sale.completed', $completedSale, null, [
                'invoice_no' => $completedSale->invoice_no,
                'total' => $completedSale->total,
                'branch_id' => $completedSale->branch_id,
            ], $cashier);

            return $completedSale;
        });
    }

    /**
     * @param  list<CartLine>  $lines
     */
    public function hold(
        Branch $branch,
        User $cashier,
        array $lines,
        string $saleDiscount = '0.00',
        bool $prescriptionRequired = false,
        ?string $prescriberName = null,
        ?string $prescriberRegNo = null,
        ?int $customerId = null,
    ): Sale {
        if ($lines === []) {
            throw new InvalidArgumentException('Cart is empty.');
        }

        return DB::transaction(function () use (
            $branch,
            $cashier,
            $lines,
            $saleDiscount,
            $prescriptionRequired,
            $prescriberName,
            $prescriberRegNo,
            $customerId,
        ): Sale {
            $tenant = Tenant::query()->findOrFail($branch->tenant_id);
            $pricedLines = $this->priceLines($branch, $lines);
            $totals = $this->calculateTotals($branch, $pricedLines, $lines, $saleDiscount);
            $lineAmounts = $this->allocateLineAmounts($pricedLines, $lines, $saleDiscount, $totals['net'], $totals['tax']);

            $sale = Sale::query()->create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'user_id' => $cashier->id,
                'customer_id' => $customerId,
                'invoice_no' => $this->invoiceNumbers->next($tenant),
                'subtotal' => $totals['subtotal'],
                'discount_amount' => bcadd($totals['line_discount_total'], $saleDiscount, 2),
                'tax_amount' => $totals['tax'],
                'total' => $totals['total'],
                'paid_amount' => '0.00',
                'change_amount' => '0.00',
                'status' => SaleStatus::Held,
                'prescription_required' => $prescriptionRequired,
                'prescriber_name' => $prescriberName,
                'prescriber_reg_no' => $prescriberRegNo,
                'sold_at' => null,
            ]);

            foreach ($pricedLines as $index => $pricedLine) {
                $cartLine = $lines[$index];
                $product = $pricedLine['product'];
                $unit = $pricedLine['unit'];
                $batch = $pricedLine['batch'];
                $lineAmount = $lineAmounts[$index];

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'product_unit_id' => $unit?->id,
                    'quantity' => $cartLine->quantity,
                    'quantity_base' => $this->unitConversion->toBaseUnits($product, $unit, $cartLine->quantity),
                    'unit_price' => $pricedLine['unit_price'],
                    'discount_amount' => $lineAmount['discount_amount'],
                    'tax_amount' => $lineAmount['tax_amount'],
                    'line_total' => $lineAmount['line_total'],
                    'is_prescription_item' => $cartLine->isPrescriptionItem,
                ]);
            }

            return $sale->fresh(['items']);
        });
    }

    /**
     * @param  list<array{line_subtotal: string, line_discount: string}>  $previewLines
     * @return array{subtotal: string, line_discount_total: string, net: string, tax: string, total: string}
     */
    public function previewTotals(array $previewLines, string $saleDiscount = '0.00', Tenant|int|null $tenant = null): array
    {
        $subtotal = '0.00';
        $lineDiscountTotal = '0.00';

        foreach ($previewLines as $line) {
            $subtotal = bcadd($subtotal, $line['line_subtotal'], 2);
            $lineDiscountTotal = bcadd($lineDiscountTotal, $line['line_discount'], 2);
        }

        return $this->calculateTotalsFromAmounts(
            $subtotal,
            $lineDiscountTotal,
            $saleDiscount,
            $this->taxRateForTenant($tenant),
        );
    }

    /**
     * @param  list<CartLine>  $lines
     * @return list<array{product: Product, unit: ?ProductUnit, batch: Batch, unit_price: string, line_subtotal: string}>
     */
    private function priceLines(Branch $branch, array $lines): array
    {
        $priced = [];

        foreach ($lines as $line) {
            if ($line->quantity <= 0) {
                throw new InvalidArgumentException('Line quantity must be positive.');
            }

            $product = Product::query()->with('units')->findOrFail($line->productId);
            $unit = $line->productUnitId !== null
                ? ProductUnit::query()->findOrFail($line->productUnitId)
                : null;

            $batch = $line->batchId !== null
                ? Batch::query()->findOrFail($line->batchId)
                : $this->pricing->suggestBatch($branch, $product);

            if ($batch === null) {
                throw new InvalidArgumentException("No available batch for {$product->name}.");
            }

            $unitPrice = $this->pricing->resolveUnitPrice($product, $unit, $batch);
            $lineSubtotal = bcmul($unitPrice, (string) $line->quantity, 2);

            $priced[] = [
                'product' => $product,
                'unit' => $unit,
                'batch' => $batch,
                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
            ];
        }

        return $priced;
    }

    /**
     * @param  list<array{line_subtotal: string}>  $pricedLines
     * @param  list<CartLine>  $cartLines
     * @return array{subtotal: string, line_discount_total: string, net: string, tax: string, total: string}
     */
    private function calculateTotals(Branch $branch, array $pricedLines, array $cartLines, string $saleDiscount): array
    {
        $subtotal = '0.00';
        $lineDiscountTotal = '0.00';

        foreach ($pricedLines as $index => $pricedLine) {
            $subtotal = bcadd($subtotal, $pricedLine['line_subtotal'], 2);
            $lineDiscountTotal = bcadd($lineDiscountTotal, $cartLines[$index]->lineDiscount, 2);
        }

        return $this->calculateTotalsFromAmounts(
            $subtotal,
            $lineDiscountTotal,
            $saleDiscount,
            $this->taxRateForTenant($branch->tenant_id),
        );
    }

    /**
     * @param  list<PaymentLine>  $payments
     */
    private function assertPaymentsCoverTotal(array $payments, string $total): void
    {
        if ($payments === []) {
            throw new InvalidArgumentException('At least one payment is required.');
        }

        $paid = $this->sumPayments($payments);

        if (bccomp($paid, $total, 2) < 0) {
            throw new InvalidArgumentException('Payment total is less than the sale total.');
        }
    }

    /**
     * @param  list<PaymentLine>  $payments
     */
    private function sumPayments(array $payments): string
    {
        $sum = '0.00';

        foreach ($payments as $payment) {
            $sum = bcadd($sum, $payment->amount, 2);
        }

        return $sum;
    }

    private function taxRateForTenant(Tenant|int|null $tenant = null): string
    {
        $tenantId = match (true) {
            $tenant instanceof Tenant => $tenant->id,
            is_int($tenant) => $tenant,
            default => auth()->user()?->tenant_id,
        };

        if ($tenantId === null) {
            return (string) config('pharmacy.pos.tax_rate', '0.00');
        }

        return $this->tenantSettings->taxRateFor($tenantId);
    }

    private function proportionalTax(string $lineNet, string $saleNet, string $saleTax): string
    {
        if (bccomp($saleNet, '0.00', 2) <= 0 || bccomp($saleTax, '0.00', 2) <= 0) {
            return '0.00';
        }

        return bcmul(bcdiv($lineNet, $saleNet, 6), $saleTax, 2);
    }

    /**
     * @param  list<array{line_subtotal: string}>  $pricedLines
     * @param  list<CartLine>  $cartLines
     * @return list<array{discount_amount: string, tax_amount: string, line_total: string}>
     */
    private function allocateLineAmounts(array $pricedLines, array $cartLines, string $saleDiscount, string $saleNet, string $saleTax): array
    {
        $lineAmounts = [];
        $remainingSaleDiscount = $saleDiscount;
        $remainingTax = $saleTax;
        $saleNetBeforeSaleDiscount = bcadd($saleNet, $saleDiscount, 2);
        $lastIndex = array_key_last($pricedLines);

        foreach ($pricedLines as $index => $pricedLine) {
            $lineNetBeforeSaleDiscount = bcsub($pricedLine['line_subtotal'], $cartLines[$index]->lineDiscount, 2);

            if ($index === $lastIndex) {
                $allocatedSaleDiscount = $remainingSaleDiscount;
            } elseif (bccomp($saleNetBeforeSaleDiscount, '0.00', 2) <= 0 || bccomp($remainingSaleDiscount, '0.00', 2) <= 0) {
                $allocatedSaleDiscount = '0.00';
            } else {
                $allocatedSaleDiscount = bcmul(
                    bcdiv($lineNetBeforeSaleDiscount, $saleNetBeforeSaleDiscount, 6),
                    $saleDiscount,
                    2,
                );
            }

            $lineNet = bcsub($lineNetBeforeSaleDiscount, $allocatedSaleDiscount, 2);

            if (bccomp($lineNet, '0.00', 2) < 0) {
                $lineNet = '0.00';
            }

            if ($index === $lastIndex) {
                $lineTax = $remainingTax;
            } else {
                $lineTax = $this->proportionalTax($lineNet, $saleNet, $saleTax);
            }

            $lineAmounts[] = [
                'discount_amount' => bcadd($cartLines[$index]->lineDiscount, $allocatedSaleDiscount, 2),
                'tax_amount' => $lineTax,
                'line_total' => bcadd($lineNet, $lineTax, 2),
            ];

            $remainingSaleDiscount = bcsub($remainingSaleDiscount, $allocatedSaleDiscount, 2);
            $remainingTax = bcsub($remainingTax, $lineTax, 2);
        }

        return $lineAmounts;
    }

    private function assertHeldSale(Sale $sale, Branch $branch): void
    {
        if ($sale->status !== SaleStatus::Held) {
            throw new InvalidArgumentException('Sale is not held.');
        }

        if ($sale->branch_id !== $branch->id) {
            throw new InvalidArgumentException('Held sale belongs to a different branch.');
        }
    }

    private function assertOpenRegisterShift(Branch $branch): void
    {
        if (! config('pharmacy.pos.require_open_shift', true)) {
            return;
        }

        if ($this->registerShifts->openShiftForBranch($branch) === null) {
            throw new InvalidArgumentException('Open a register shift before completing sales.');
        }
    }

    /**
     * @return array{subtotal: string, line_discount_total: string, net: string, tax: string, total: string}
     */
    private function calculateTotalsFromAmounts(
        string $subtotal,
        string $lineDiscountTotal,
        string $saleDiscount,
        string $taxRate,
    ): array {
        $net = bcsub(bcsub($subtotal, $lineDiscountTotal, 2), $saleDiscount, 2);

        if (bccomp($net, '0.00', 2) < 0) {
            $net = '0.00';
        }

        $tax = bcmul($net, $taxRate, 2);
        $total = bcadd($net, $tax, 2);

        return [
            'subtotal' => $subtotal,
            'line_discount_total' => $lineDiscountTotal,
            'net' => $net,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
