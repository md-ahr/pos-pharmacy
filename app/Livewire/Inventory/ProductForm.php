<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public string $name = '';

    public string $generic_name = '';

    public string $sku = '';

    public string $barcode = '';

    public string $base_unit = 'tablet';

    public ?int $category_id = null;

    public ?int $manufacturer_id = null;

    public int $reorder_level = 0;

    public bool $requires_prescription = false;

    public bool $is_active = true;

    /** @var array<int, array{unit_name: string, conversion_factor: int, barcode: string, selling_price: string, is_default: bool}> */
    public array $units = [];

    public ?string $latestBatchCostPrice = null;

    public function mount(?Product $product = null): void
    {
        $this->product = $product;

        if ($product === null) {
            $this->units = [
                ['unit_name' => 'tablet', 'conversion_factor' => 1, 'barcode' => '', 'selling_price' => '', 'is_default' => true],
            ];

            return;
        }

        $this->fill([
            'name' => $product->name,
            'generic_name' => $product->generic_name ?? '',
            'sku' => $product->sku ?? '',
            'barcode' => $product->barcode ?? '',
            'base_unit' => $product->base_unit,
            'category_id' => $product->category_id,
            'manufacturer_id' => $product->manufacturer_id,
            'reorder_level' => $product->reorder_level,
            'requires_prescription' => $product->requires_prescription,
            'is_active' => $product->is_active,
        ]);

        $latestBatch = $product->batches()
            ->latest('received_at')
            ->latest('id')
            ->first();

        $this->latestBatchCostPrice = $latestBatch?->cost_price !== null
            ? number_format((float) $latestBatch->cost_price, 2, '.', '')
            : null;

        $this->units = $product->units->map(fn (ProductUnit $unit) => [
            'unit_name' => $unit->unit_name,
            'conversion_factor' => $unit->conversion_factor,
            'barcode' => $unit->barcode ?? '',
            'selling_price' => $unit->selling_price !== null ? (string) $unit->selling_price : '',
            'is_default' => $unit->is_default,
        ])->all();

        if ($this->units === []) {
            $this->units = [
                ['unit_name' => $product->base_unit, 'conversion_factor' => 1, 'barcode' => '', 'selling_price' => '', 'is_default' => true],
            ];
        }
    }

    public function addUnit(): void
    {
        $this->units[] = [
            'unit_name' => '',
            'conversion_factor' => 1,
            'barcode' => '',
            'selling_price' => '',
            'is_default' => false,
        ];
    }

    public function removeUnit(int $index): void
    {
        if (count($this->units) <= 1) {
            return;
        }

        unset($this->units[$index]);
        $this->units = array_values($this->units);
    }

    public function save(): void
    {
        $tenantId = auth()->user()?->tenant_id;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->where('tenant_id', $tenantId)->ignore($this->product?->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->where('tenant_id', $tenantId)->ignore($this->product?->id)],
            'base_unit' => ['required', 'string', 'max:50'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'manufacturer_id' => ['nullable', 'exists:manufacturers,id'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['boolean'],
            'is_active' => ['boolean'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_name' => ['required', 'string', 'max:50'],
            'units.*.conversion_factor' => ['required', 'integer', 'min:1'],
            'units.*.barcode' => ['nullable', 'string', 'max:100'],
            'units.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.is_default' => ['boolean'],
        ]);

        $defaultCount = collect($validated['units'])->where('is_default', true)->count();
        if ($defaultCount !== 1) {
            $this->addError('units', 'Exactly one sellable unit must be marked as default.');

            return;
        }

        $productData = collect($validated)->except('units')->map(function ($value, $key) {
            if (in_array($key, ['generic_name', 'sku', 'barcode'], true) && $value === '') {
                return null;
            }

            return $value;
        })->all();

        if ($this->product === null) {
            $product = Product::query()->create($productData);
        } else {
            $this->product->update($productData);
            $product = $this->product;
        }

        $product->units()->delete();

        foreach ($validated['units'] as $unit) {
            $product->units()->create([
                'unit_name' => $unit['unit_name'],
                'conversion_factor' => $unit['conversion_factor'],
                'barcode' => $unit['barcode'] !== '' ? $unit['barcode'] : null,
                'selling_price' => $unit['selling_price'] !== '' ? $unit['selling_price'] : null,
                'is_default' => $unit['is_default'],
            ]);
        }

        session()->flash('success', 'Product saved successfully.');

        $this->redirectRoute('pharmacy.inventory.products', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.inventory.product-form', [
            'categories' => Category::query()->orderBy('name')->get(),
            'manufacturers' => Manufacturer::query()->orderBy('name')->get(),
            'latestBatchCostPrice' => $this->latestBatchCostPrice,
        ])->layout('layouts.pharmacy', [
            'title' => $this->product ? 'Edit Product' : 'Add Product',
            'nav' => 'inventory',
        ]);
    }
}
