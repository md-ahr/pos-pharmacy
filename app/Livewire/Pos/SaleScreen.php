<?php

namespace App\Livewire\Pos;

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Services\BranchContext;
use App\Services\CheckoutService;
use App\Services\PosPricingService;
use App\Services\ProductSearchService;
use App\Services\SaleRefundService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class SaleScreen extends Component
{
    public string $search = '';

    /** @var list<array<string, mixed>> */
    public array $cart = [];

    public string $saleDiscount = '0.00';

    public bool $prescriptionRequired = false;

    public string $prescriberName = '';

    public string $prescriberRegNo = '';

    public bool $showPayment = false;

    public string $paymentMethod = 'cash';

    public string $paymentAmount = '';

    public string $paymentReference = '';

    /** @var list<array{method: string, amount: string, reference: ?string}> */
    public array $payments = [];

    public ?int $heldSaleId = null;

    public ?int $completedSaleId = null;

    public ?int $refundSaleId = null;

    public function updatedSearch(ProductSearchService $searchService): void
    {
        unset($searchService);
    }

    public function addProduct(int $productId, PosPricingService $pricing, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('search', 'No active branch selected.');

            return;
        }

        $product = Product::query()->with('units')->findOrFail($productId);
        $unit = $product->units->firstWhere('is_default', true) ?? $product->units->first();
        $batch = $pricing->suggestBatch($branch, $product);

        if ($batch === null) {
            $this->addError('search', "No available stock for {$product->name}.");

            return;
        }

        $unitPrice = $pricing->resolveUnitPrice($product, $unit, $batch);
        $batches = $pricing->availableBatches($branch, $product);

        $this->cart[] = [
            'key' => (string) Str::uuid(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_unit_id' => $unit?->id,
            'unit_name' => $unit?->unit_name ?? $product->base_unit,
            'conversion_factor' => $unit?->conversion_factor ?? 1,
            'quantity' => 1,
            'batch_id' => $batch->id,
            'batch_no' => $batch->batch_no,
            'unit_price' => $unitPrice,
            'line_discount' => '0.00',
            'is_prescription_item' => $product->requires_prescription,
            'requires_prescription' => $product->requires_prescription,
            'line_subtotal' => $unitPrice,
            'available_batches' => $batches->map(fn ($item) => [
                'id' => $item->id,
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date->format('Y-m-d'),
            ])->all(),
            'units' => $product->units->map(fn (ProductUnit $productUnit) => [
                'id' => $productUnit->id,
                'unit_name' => $productUnit->unit_name,
                'conversion_factor' => $productUnit->conversion_factor,
                'is_default' => $productUnit->is_default,
            ])->all(),
        ];

        $this->search = '';
        $this->syncPrescriptionFlag();
    }

    public function removeLine(string $key): void
    {
        $this->cart = array_values(array_filter(
            $this->cart,
            fn (array $line): bool => $line['key'] !== $key,
        ));
        $this->syncPrescriptionFlag();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeLine($key);

            return;
        }

        foreach ($this->cart as $index => $line) {
            if ($line['key'] !== $key) {
                continue;
            }

            $this->cart[$index]['quantity'] = $quantity;
            $this->cart[$index]['line_subtotal'] = bcmul($line['unit_price'], (string) $quantity, 2);
        }
    }

    public function updateUnit(string $key, ?int $unitId, PosPricingService $pricing, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            return;
        }

        foreach ($this->cart as $index => $line) {
            if ($line['key'] !== $key) {
                continue;
            }

            $product = Product::query()->with('units')->findOrFail($line['product_id']);
            $unit = $unitId !== null ? $product->units->firstWhere('id', $unitId) : null;
            $batch = $product->batches()->find($line['batch_id']) ?? $pricing->suggestBatch($branch, $product);

            if ($batch === null) {
                return;
            }

            $unitPrice = $pricing->resolveUnitPrice($product, $unit, $batch);

            $this->cart[$index]['product_unit_id'] = $unit?->id;
            $this->cart[$index]['unit_name'] = $unit?->unit_name ?? $product->base_unit;
            $this->cart[$index]['conversion_factor'] = $unit?->conversion_factor ?? 1;
            $this->cart[$index]['unit_price'] = $unitPrice;
            $this->cart[$index]['line_subtotal'] = bcmul($unitPrice, (string) $line['quantity'], 2);
        }
    }

    public function updateBatch(string $key, int $batchId, PosPricingService $pricing): void
    {
        foreach ($this->cart as $index => $line) {
            if ($line['key'] !== $key) {
                continue;
            }

            $product = Product::query()->with('units')->findOrFail($line['product_id']);
            $batch = $product->batches()->findOrFail($batchId);
            $unit = $line['product_unit_id'] !== null
                ? $product->units->firstWhere('id', $line['product_unit_id'])
                : null;
            $unitPrice = $pricing->resolveUnitPrice($product, $unit, $batch);

            $this->cart[$index]['batch_id'] = $batch->id;
            $this->cart[$index]['batch_no'] = $batch->batch_no;
            $this->cart[$index]['unit_price'] = $unitPrice;
            $this->cart[$index]['line_subtotal'] = bcmul($unitPrice, (string) $line['quantity'], 2);
        }
    }

    public function togglePrescriptionItem(string $key): void
    {
        foreach ($this->cart as $index => $line) {
            if ($line['key'] === $key) {
                $this->cart[$index]['is_prescription_item'] = ! $line['is_prescription_item'];
            }
        }

        $this->syncPrescriptionFlag();
    }

    public function openPayment(): void
    {
        if ($this->cart === []) {
            $this->addError('search', 'Add at least one item before checkout.');

            return;
        }

        $this->showPayment = true;
        $this->paymentAmount = $this->totals()['total'];
        $this->payments = [];
    }

    public function addPaymentLine(): void
    {
        $this->validate([
            'paymentMethod' => ['required', 'in:cash,card,mobile,other'],
            'paymentAmount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->payments[] = [
            'method' => $this->paymentMethod,
            'amount' => number_format((float) $this->paymentAmount, 2, '.', ''),
            'reference' => $this->paymentReference !== '' ? $this->paymentReference : null,
        ];

        $this->paymentAmount = $this->remainingDue();
        $this->paymentReference = '';
    }

    public function removePayment(int $index): void
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
        $this->paymentAmount = $this->remainingDue();
    }

    public function completeSale(CheckoutService $checkout, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('search', 'No active branch selected.');

            return;
        }

        if ($this->payments === []) {
            $this->addPaymentLine();
        }

        try {
            $heldSale = $this->heldSaleId !== null
                ? Sale::query()->findOrFail($this->heldSaleId)
                : null;

            $sale = $checkout->complete(
                branch: $branch,
                cashier: auth()->user(),
                lines: $this->cartLines(),
                payments: $this->paymentLines(),
                saleDiscount: $this->saleDiscount,
                prescriptionRequired: $this->prescriptionRequired,
                prescriberName: $this->prescriberName !== '' ? $this->prescriberName : null,
                prescriberRegNo: $this->prescriberRegNo !== '' ? $this->prescriberRegNo : null,
                heldSale: $heldSale,
            );

            $this->resetCart();
            $this->completedSaleId = $sale->id;
            $this->showPayment = false;
            session()->flash('success', "Sale {$sale->invoice_no} completed.");
        } catch (\Throwable $exception) {
            $this->addError('search', $exception->getMessage());
        }
    }

    public function holdSale(CheckoutService $checkout, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('search', 'No active branch selected.');

            return;
        }

        if ($this->cart === []) {
            $this->addError('search', 'Add at least one item to hold.');

            return;
        }

        try {
            $sale = $checkout->hold(
                branch: $branch,
                cashier: auth()->user(),
                lines: $this->cartLines(),
                saleDiscount: $this->saleDiscount,
                prescriptionRequired: $this->prescriptionRequired,
                prescriberName: $this->prescriberName !== '' ? $this->prescriberName : null,
                prescriberRegNo: $this->prescriberRegNo !== '' ? $this->prescriberRegNo : null,
            );

            $this->resetCart();
            session()->flash('success', "Sale {$sale->invoice_no} held.");
        } catch (\Throwable $exception) {
            $this->addError('search', $exception->getMessage());
        }
    }

    public function resumeHeld(int $saleId, PosPricingService $pricing, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();
        $sale = Sale::query()->with(['items.product.units', 'items.batch', 'items.productUnit'])->findOrFail($saleId);

        if ($sale->status !== SaleStatus::Held || $branch === null || $sale->branch_id !== $branch->id) {
            $this->addError('search', 'Unable to resume the selected held sale.');

            return;
        }

        $this->resetCart();
        $this->heldSaleId = $sale->id;
        $this->saleDiscount = '0.00';
        $this->prescriptionRequired = $sale->prescription_required;
        $this->prescriberName = (string) ($sale->prescriber_name ?? '');
        $this->prescriberRegNo = (string) ($sale->prescriber_reg_no ?? '');

        foreach ($sale->items as $item) {
            $product = $item->product;
            $batches = $pricing->availableBatches($branch, $product);

            $this->cart[] = [
                'key' => (string) Str::uuid(),
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_unit_id' => $item->product_unit_id,
                'unit_name' => $item->productUnit?->unit_name ?? $product->base_unit,
                'conversion_factor' => $item->productUnit?->conversion_factor ?? 1,
                'quantity' => $item->quantity,
                'batch_id' => $item->batch_id,
                'batch_no' => $item->batch->batch_no,
                'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                'line_discount' => number_format((float) $item->discount_amount, 2, '.', ''),
                'is_prescription_item' => $item->is_prescription_item,
                'requires_prescription' => $product->requires_prescription,
                'line_subtotal' => bcmul(number_format((float) $item->unit_price, 2, '.', ''), (string) $item->quantity, 2),
                'available_batches' => $batches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                ])->all(),
                'units' => $product->units->map(fn (ProductUnit $productUnit) => [
                    'id' => $productUnit->id,
                    'unit_name' => $productUnit->unit_name,
                    'conversion_factor' => $productUnit->conversion_factor,
                    'is_default' => $productUnit->is_default,
                ])->all(),
            ];
        }
    }

    public function refundSale(SaleRefundService $refundService, BranchContext $branchContext): void
    {
        $branch = $branchContext->activeBranch();

        if ($branch === null || $this->refundSaleId === null) {
            return;
        }

        $sale = Sale::query()->findOrFail($this->refundSaleId);

        try {
            $refundService->refund($sale, $branch);
            $this->refundSaleId = null;
            session()->flash('success', "Sale {$sale->invoice_no} refunded.");
        } catch (\Throwable $exception) {
            $this->addError('search', $exception->getMessage());
        }
    }

    public function clearCart(): void
    {
        $this->resetCart();
    }

    /**
     * @return array{subtotal: string, line_discount_total: string, net: string, tax: string, total: string}
     */
    public function totals(): array
    {
        return app(CheckoutService::class)->previewTotals(
            array_map(fn (array $line): array => [
                'line_subtotal' => $line['line_subtotal'],
                'line_discount' => $line['line_discount'],
            ], $this->cart),
            $this->saleDiscount,
        );
    }

    public function remainingDue(): string
    {
        $total = $this->totals()['total'];
        $paid = '0.00';

        foreach ($this->payments as $payment) {
            $paid = bcadd($paid, $payment['amount'], 2);
        }

        $remaining = bcsub($total, $paid, 2);

        return bccomp($remaining, '0.00', 2) > 0 ? $remaining : '0.00';
    }

    public function render(ProductSearchService $searchService, BranchContext $branchContext): View
    {
        $branch = $branchContext->activeBranch();
        $results = $this->search !== '' ? $searchService->search($this->search) : collect();

        $heldSales = $branch !== null
            ? Sale::query()
                ->where('branch_id', $branch->id)
                ->where('status', SaleStatus::Held)
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        $recentSales = $branch !== null
            ? Sale::query()
                ->where('branch_id', $branch->id)
                ->whereIn('status', [SaleStatus::Completed, SaleStatus::Refunded, SaleStatus::PartiallyRefunded])
                ->latest('sold_at')
                ->limit(10)
                ->get()
            : collect();

        return view('livewire.pos.sale-screen', [
            'searchResults' => $results,
            'heldSales' => $heldSales,
            'recentSales' => $recentSales,
            'paymentMethods' => PaymentMethod::cases(),
            'totals' => $this->totals(),
            'branch' => $branch,
        ])->layout('layouts.pharmacy', [
            'title' => 'POS',
        ]);
    }

    private function resetCart(): void
    {
        $this->cart = [];
        $this->heldSaleId = null;
        $this->saleDiscount = '0.00';
        $this->prescriptionRequired = false;
        $this->prescriberName = '';
        $this->prescriberRegNo = '';
        $this->payments = [];
        $this->showPayment = false;
        $this->paymentAmount = '';
        $this->paymentReference = '';
    }

    private function syncPrescriptionFlag(): void
    {
        $this->prescriptionRequired = collect($this->cart)->contains(
            fn (array $line): bool => $line['is_prescription_item'] || $line['requires_prescription'],
        );
    }

    /**
     * @return list<CartLine>
     */
    private function cartLines(): array
    {
        return array_map(
            fn (array $line): CartLine => CartLine::fromArray([
                'product_id' => $line['product_id'],
                'product_unit_id' => $line['product_unit_id'],
                'quantity' => $line['quantity'],
                'batch_id' => $line['batch_id'],
                'line_discount' => $line['line_discount'],
                'is_prescription_item' => $line['is_prescription_item'],
            ]),
            $this->cart,
        );
    }

    /**
     * @return list<PaymentLine>
     */
    private function paymentLines(): array
    {
        return array_map(
            fn (array $payment): PaymentLine => PaymentLine::fromArray($payment),
            $this->payments,
        );
    }
}
