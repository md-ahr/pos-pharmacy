<?php

namespace Database\Seeders;

use App\Data\CartLine;
use App\Data\PaymentLine;
use App\Enums\PaymentMethod;
use App\Enums\StockAdjustmentReason;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PurchaseOrderService;
use App\Services\RegisterShiftService;
use App\Services\StockAdjustmentService;
use App\Services\StockTransferService;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PharmacySeeder extends Seeder
{
    /** @var array<string, Product> */
    private array $products = [];

    public function run(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'AHR Demo Pharmacy',
            'slug' => 'demo-pharmacy',
            'email' => 'ahr.web.pro@gmail.com',
            'phone' => '+1 555 0100',
            'address' => '123 Health Street, Medical District',
        ]);

        $mainBranch = Branch::factory()->main()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Branch',
            'address' => '123 Health Street',
        ]);

        $downtownBranch = Branch::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Downtown Branch',
            'code' => 'DT01',
            'address' => '45 Market Avenue',
            'is_main' => false,
        ]);

        $owner = User::factory()->owner($tenant)->create([
            'name' => 'AHR Shop Owner',
            'email' => 'ahr.web.pro@gmail.com',
        ]);

        $manager = User::factory()->manager($tenant, $mainBranch)->create([
            'name' => 'Sarah Manager',
            'email' => 'manager@pharmacy.test',
        ]);

        $pharmacist = User::factory()->pharmacist($tenant, $mainBranch)->create([
            'name' => 'Dr. Ali Pharmacist',
            'email' => 'pharmacist@pharmacy.test',
        ]);

        $cashier = User::factory()->cashier($tenant, $downtownBranch)->create([
            'name' => 'Fatima Cashier',
            'email' => 'cashier@pharmacy.test',
        ]);

        TenantSetting::factory()->forTenant($tenant, $mainBranch)->create([
            'currency_code' => 'USD',
            'tax_rate' => '0.1500',
            'receipt_header' => 'AHR Demo Pharmacy — Thank you for choosing us.',
            'receipt_footer' => 'Get well soon!',
        ]);

        $categories = $this->seedCategories($tenant);
        $manufacturers = $this->seedManufacturers($tenant);
        $suppliers = $this->seedSuppliers($tenant);

        $this->seedProducts($tenant, $categories, $manufacturers, $mainBranch);
        $customers = $this->seedCustomers($tenant);

        $this->seedPurchaseOrders($mainBranch, $suppliers, $owner);
        $this->seedStockMovements($mainBranch, $downtownBranch, $manager);
        $this->seedRegisterShifts($mainBranch, $downtownBranch, $owner, $cashier);
        $this->seedSales($mainBranch, $owner, $pharmacist, $customers);

        $this->command?->info('Demo pharmacy seeded for ahr.web.pro@gmail.com (password: password)');
        $this->command?->info('Staff: manager@pharmacy.test, pharmacist@pharmacy.test, cashier@pharmacy.test');
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(Tenant $tenant): array
    {
        $names = [
            'general' => 'General Medicines',
            'antibiotics' => 'Antibiotics',
            'otc' => 'OTC & Wellness',
            'vitamins' => 'Vitamins & Supplements',
        ];

        $categories = [];

        foreach ($names as $key => $name) {
            $categories[$key] = Category::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => $name,
            ]);
        }

        return $categories;
    }

    /**
     * @return array<string, Manufacturer>
     */
    private function seedManufacturers(Tenant $tenant): array
    {
        return [
            'acme' => Manufacturer::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Acme Pharma',
            ]),
            'wellcare' => Manufacturer::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'WellCare Labs',
            ]),
            'nature' => Manufacturer::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'NaturePlus',
            ]),
        ];
    }

    /**
     * @return array<string, Supplier>
     */
    private function seedSuppliers(Tenant $tenant): array
    {
        return [
            'medsupply' => Supplier::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'MedSupply Co',
                'email' => 'orders@medsupply.test',
            ]),
            'pharmawholesale' => Supplier::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Pharma Wholesale Ltd',
                'email' => 'sales@pharmawholesale.test',
            ]),
        ];
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Manufacturer>  $manufacturers
     */
    private function seedProducts(Tenant $tenant, array $categories, array $manufacturers, Branch $mainBranch): void
    {
        $catalog = [
            [
                'key' => 'paracetamol',
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'sku' => 'PARA-500',
                'base_unit' => 'tablet',
                'category' => 'general',
                'manufacturer' => 'acme',
                'reorder_level' => 100,
                'batches' => [
                    ['batch_no' => 'BATCH-PARA-01', 'months' => 6, 'qty' => 300, 'cost' => '2.00', 'sell' => '5.00'],
                    ['batch_no' => 'BATCH-PARA-02', 'months' => 18, 'qty' => 700, 'cost' => '1.80', 'sell' => '5.00'],
                ],
                'units' => [
                    ['unit_name' => 'strip', 'factor' => 10, 'price' => '45.00'],
                ],
            ],
            [
                'key' => 'amoxicillin',
                'name' => 'Amoxicillin 250mg',
                'generic_name' => 'Amoxicillin',
                'sku' => 'AMOX-250',
                'base_unit' => 'capsule',
                'category' => 'antibiotics',
                'manufacturer' => 'acme',
                'requires_prescription' => true,
                'reorder_level' => 50,
                'batches' => [
                    ['batch_no' => 'BATCH-AMOX-01', 'months' => 10, 'qty' => 400, 'cost' => '4.00', 'sell' => '12.00'],
                ],
            ],
            [
                'key' => 'cough_syrup',
                'name' => 'Cough Syrup 100ml',
                'generic_name' => 'Dextromethorphan',
                'sku' => 'COUGH-100',
                'base_unit' => 'ml',
                'category' => 'otc',
                'manufacturer' => 'wellcare',
                'reorder_level' => 20,
                'batches' => [
                    ['batch_no' => 'BATCH-COUGH-01', 'months' => 14, 'qty' => 80, 'cost' => '3.50', 'sell' => '8.50'],
                ],
            ],
            [
                'key' => 'ibuprofen',
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'sku' => 'IBU-400',
                'base_unit' => 'tablet',
                'category' => 'general',
                'manufacturer' => 'acme',
                'reorder_level' => 50,
                'batches' => [
                    ['batch_no' => 'BATCH-IBU-01', 'months' => 12, 'qty' => 18, 'cost' => '1.50', 'sell' => '4.00'],
                ],
            ],
            [
                'key' => 'vitamin_c',
                'name' => 'Vitamin C 1000mg',
                'generic_name' => 'Ascorbic Acid',
                'sku' => 'VITC-1000',
                'base_unit' => 'tablet',
                'category' => 'vitamins',
                'manufacturer' => 'nature',
                'reorder_level' => 30,
                'batches' => [
                    ['batch_no' => 'BATCH-VITC-NEAR', 'days' => 45, 'qty' => 120, 'cost' => '2.50', 'sell' => '7.00'],
                ],
            ],
            [
                'key' => 'aspirin',
                'name' => 'Aspirin 75mg',
                'generic_name' => 'Acetylsalicylic Acid',
                'sku' => 'ASP-75',
                'base_unit' => 'tablet',
                'category' => 'general',
                'manufacturer' => 'acme',
                'reorder_level' => 40,
                'batches' => [
                    ['batch_no' => 'BATCH-ASP-EXP', 'days' => -30, 'qty' => 25, 'cost' => '1.00', 'sell' => '3.00'],
                    ['batch_no' => 'BATCH-ASP-01', 'months' => 16, 'qty' => 200, 'cost' => '1.20', 'sell' => '3.50'],
                ],
            ],
            [
                'key' => 'metformin',
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin',
                'sku' => 'MET-500',
                'base_unit' => 'tablet',
                'category' => 'general',
                'manufacturer' => 'wellcare',
                'reorder_level' => 60,
                'batches' => [
                    ['batch_no' => 'BATCH-MET-01', 'months' => 20, 'qty' => 350, 'cost' => '2.00', 'sell' => '6.00'],
                ],
            ],
            [
                'key' => 'omeprazole',
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'sku' => 'OME-20',
                'base_unit' => 'capsule',
                'category' => 'general',
                'manufacturer' => 'wellcare',
                'reorder_level' => 40,
                'batches' => [
                    ['batch_no' => 'BATCH-OME-01', 'months' => 15, 'qty' => 250, 'cost' => '3.00', 'sell' => '9.00'],
                ],
            ],
            [
                'key' => 'loratadine',
                'name' => 'Loratadine 10mg',
                'generic_name' => 'Loratadine',
                'sku' => 'LOR-10',
                'base_unit' => 'tablet',
                'category' => 'otc',
                'manufacturer' => 'nature',
                'reorder_level' => 35,
                'batches' => [
                    ['batch_no' => 'BATCH-LOR-01', 'months' => 11, 'qty' => 180, 'cost' => '1.80', 'sell' => '5.50'],
                ],
            ],
            [
                'key' => 'hand_sanitizer',
                'name' => 'Hand Sanitizer 500ml',
                'generic_name' => 'Ethanol 70%',
                'sku' => 'SAN-500',
                'base_unit' => 'ml',
                'category' => 'otc',
                'manufacturer' => 'nature',
                'reorder_level' => 10,
                'batches' => [
                    ['batch_no' => 'BATCH-SAN-01', 'months' => 24, 'qty' => 40, 'cost' => '4.00', 'sell' => '10.00'],
                ],
            ],
            [
                'key' => 'bandages',
                'name' => 'Elastic Bandage 10cm',
                'generic_name' => null,
                'sku' => 'BAND-10',
                'base_unit' => 'unit',
                'category' => 'otc',
                'manufacturer' => 'acme',
                'reorder_level' => 15,
                'batches' => [
                    ['batch_no' => 'BATCH-BAND-01', 'months' => 36, 'qty' => 60, 'cost' => '2.00', 'sell' => '6.00'],
                ],
            ],
            [
                'key' => 'insulin',
                'name' => 'Insulin Pen 3ml',
                'generic_name' => 'Insulin Glargine',
                'sku' => 'INS-3ML',
                'base_unit' => 'unit',
                'category' => 'general',
                'manufacturer' => 'wellcare',
                'requires_prescription' => true,
                'reorder_level' => 10,
                'batches' => [
                    ['batch_no' => 'BATCH-INS-01', 'months' => 8, 'qty' => 24, 'cost' => '35.00', 'sell' => '65.00'],
                ],
            ],
        ];

        foreach ($catalog as $item) {
            $product = Product::factory()->create([
                'tenant_id' => $tenant->id,
                'category_id' => $categories[$item['category']]->id,
                'manufacturer_id' => $manufacturers[$item['manufacturer']]->id,
                'name' => $item['name'],
                'generic_name' => $item['generic_name'],
                'sku' => $item['sku'],
                'base_unit' => $item['base_unit'],
                'reorder_level' => $item['reorder_level'],
                'requires_prescription' => $item['requires_prescription'] ?? false,
                'barcode' => fake()->ean13(),
            ]);

            ProductUnit::factory()->default()->create([
                'product_id' => $product->id,
                'unit_name' => $item['base_unit'],
            ]);

            foreach ($item['units'] ?? [] as $unit) {
                ProductUnit::factory()->create([
                    'product_id' => $product->id,
                    'unit_name' => $unit['unit_name'],
                    'conversion_factor' => $unit['factor'],
                    'selling_price' => $unit['price'],
                ]);
            }

            foreach ($item['batches'] as $batchData) {
                $expiryDate = $this->resolveExpiryDate($batchData);

                $batch = Batch::factory()->create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'batch_no' => $batchData['batch_no'],
                    'expiry_date' => $expiryDate,
                    'cost_price' => $batchData['cost'],
                    'selling_price' => $batchData['sell'],
                ]);

                Stock::factory()->create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $mainBranch->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => $batchData['qty'],
                ]);
            }

            $this->products[$item['key']] = $product;
        }
    }

    /**
     * @param  array{batch_no: string, qty: int, cost: string, sell: string, months?: int, days?: int}  $batchData
     */
    private function resolveExpiryDate(array $batchData): CarbonInterface
    {
        if (isset($batchData['days'])) {
            return now()->addDays($batchData['days']);
        }

        return now()->addMonths($batchData['months'] ?? 12);
    }

    /**
     * @return Collection<int, Customer>
     */
    private function seedCustomers(Tenant $tenant): Collection
    {
        $walkIn = Customer::factory()->walkIn()->create([
            'tenant_id' => $tenant->id,
        ]);

        $named = collect([
            ['name' => 'Ahmed Hassan', 'phone' => '+1 555 1001', 'email' => 'ahmed.hassan@example.test'],
            ['name' => 'Fatima Noor', 'phone' => '+1 555 1002', 'email' => 'fatima.noor@example.test'],
            ['name' => 'John Smith', 'phone' => '+1 555 1003', 'email' => 'john.smith@example.test'],
            ['name' => 'Maria Garcia', 'phone' => '+1 555 1004', 'email' => 'maria.garcia@example.test'],
        ])->map(fn (array $data) => Customer::factory()->create([
            'tenant_id' => $tenant->id,
            ...$data,
        ]));

        return collect([$walkIn])->merge($named);
    }

    /**
     * @param  array<string, Supplier>  $suppliers
     */
    private function seedPurchaseOrders(Branch $branch, array $suppliers, User $owner): void
    {
        $poService = app(PurchaseOrderService::class);

        $draft = $poService->createDraft(
            branch: $branch,
            supplier: $suppliers['medsupply'],
            lines: [
                ['product_id' => $this->products['metformin']->id, 'quantity' => 200, 'unit_cost' => '1.80'],
                ['product_id' => $this->products['omeprazole']->id, 'quantity' => 150, 'unit_cost' => '2.50'],
            ],
            createdBy: $owner,
            notes: 'Monthly restock — awaiting approval.',
        );

        $ordered = $poService->createDraft(
            branch: $branch,
            supplier: $suppliers['pharmawholesale'],
            lines: [
                ['product_id' => $this->products['loratadine']->id, 'quantity' => 100, 'unit_cost' => '1.50'],
            ],
            createdBy: $owner,
            notes: 'Ordered — in transit.',
        );
        $ordered = $poService->markOrdered($ordered);

        $received = $poService->createDraft(
            branch: $branch,
            supplier: $suppliers['medsupply'],
            lines: [
                ['product_id' => $this->products['bandages']->id, 'quantity' => 50, 'unit_cost' => '1.75'],
            ],
            createdBy: $owner,
            notes: 'Received last week.',
        );
        $received = $poService->markOrdered($received);
        $receivedItem = $received->items->first();

        if ($receivedItem !== null) {
            $poService->receive($received, [[
                'purchase_order_item_id' => $receivedItem->id,
                'batch_no' => 'BATCH-BAND-PO',
                'expiry_date' => now()->addMonths(24),
                'selling_price' => '6.50',
            ]]);
        }

        unset($draft);
    }

    private function seedStockMovements(Branch $mainBranch, Branch $downtownBranch, User $manager): void
    {
        $paracetamolBatch = Batch::query()
            ->where('product_id', $this->products['paracetamol']->id)
            ->orderBy('expiry_date')
            ->first();

        if ($paracetamolBatch !== null) {
            app(StockTransferService::class)->initiate(
                fromBranch: $mainBranch,
                toBranch: $downtownBranch,
                product: $this->products['paracetamol'],
                batch: $paracetamolBatch,
                quantity: 50,
                initiatedBy: $manager,
                notes: 'Initial stock for downtown branch.',
            );
        }

        $ibuprofenBatch = Batch::query()
            ->where('product_id', $this->products['ibuprofen']->id)
            ->first();

        if ($ibuprofenBatch !== null) {
            app(StockAdjustmentService::class)->adjust(
                branch: $mainBranch,
                product: $this->products['ibuprofen'],
                batch: $ibuprofenBatch,
                quantityDelta: -2,
                reason: StockAdjustmentReason::Damage,
                adjustedBy: $manager,
                notes: 'Damaged blister pack during shelving.',
            );
        }
    }

    private function seedRegisterShifts(Branch $mainBranch, Branch $downtownBranch, User $owner, User $cashier): void
    {
        $shiftService = app(RegisterShiftService::class);

        $shiftService->openShift($mainBranch, $owner, '200.00');
        $shiftService->openShift($downtownBranch, $cashier, '150.00');
    }

    /**
     * @param  Collection<int, Customer>  $customers
     */
    private function seedSales(Branch $branch, User $owner, User $pharmacist, Collection $customers): void
    {
        $checkout = app(CheckoutService::class);
        $ahmed = $customers->firstWhere('name', 'Ahmed Hassan');
        $fatima = $customers->firstWhere('name', 'Fatima Noor');

        $todaySales = [
            [
                'cashier' => $owner,
                'lines' => [
                    new CartLine($this->products['paracetamol']->id, null, 2),
                    new CartLine($this->products['cough_syrup']->id, null, 1),
                ],
                'payments' => [new PaymentLine(PaymentMethod::Cash, '100.00')],
                'customer_id' => $ahmed?->id,
                'sold_at' => now(),
            ],
            [
                'cashier' => $owner,
                'lines' => [new CartLine($this->products['loratadine']->id, null, 3)],
                'payments' => [new PaymentLine(PaymentMethod::Card, '50.00', 'CARD-1001')],
                'customer_id' => null,
                'sold_at' => now()->subHours(2),
            ],
            [
                'cashier' => $owner,
                'lines' => [new CartLine($this->products['hand_sanitizer']->id, null, 2)],
                'payments' => [new PaymentLine(PaymentMethod::Mobile, '50.00', 'MOMO-7788')],
                'customer_id' => $fatima?->id,
                'sold_at' => now()->subHours(5),
            ],
        ];

        foreach ($todaySales as $saleData) {
            $this->completeAndBackdate($checkout, $branch, $saleData);
        }

        $historicalSales = [
            [
                'cashier' => $owner,
                'lines' => [new CartLine($this->products['omeprazole']->id, null, 1)],
                'payments' => [new PaymentLine(PaymentMethod::Cash, '50.00')],
                'customer_id' => null,
                'sold_at' => now()->subDays(3),
            ],
            [
                'cashier' => $owner,
                'lines' => [new CartLine($this->products['bandages']->id, null, 2)],
                'payments' => [new PaymentLine(PaymentMethod::Cash, '50.00')],
                'customer_id' => $ahmed?->id,
                'sold_at' => now()->subDays(3)->subHours(3),
            ],
            [
                'cashier' => $owner,
                'lines' => [new CartLine($this->products['vitamin_c']->id, null, 1)],
                'payments' => [new PaymentLine(PaymentMethod::Card, '50.00')],
                'customer_id' => null,
                'sold_at' => now()->subDays(7),
            ],
        ];

        foreach ($historicalSales as $saleData) {
            $this->completeAndBackdate($checkout, $branch, $saleData);
        }

        $prescriptionSale = $checkout->complete(
            branch: $branch,
            cashier: $pharmacist,
            lines: [
                new CartLine(
                    productId: $this->products['amoxicillin']->id,
                    productUnitId: null,
                    quantity: 2,
                    batchId: null,
                    lineDiscount: '0.00',
                    isPrescriptionItem: true,
                ),
            ],
            payments: [new PaymentLine(PaymentMethod::Cash, '100.00')],
            prescriptionRequired: true,
            prescriberName: 'Dr. Ahmed Hassan',
            prescriberRegNo: 'MED-12345',
            customerId: $ahmed?->id,
        );
        $this->backdateSale($prescriptionSale, now()->subDay());

        $checkout->hold(
            branch: $branch,
            cashier: $owner,
            lines: [
                new CartLine($this->products['metformin']->id, null, 2),
                new CartLine($this->products['insulin']->id, null, 1, null, '0.00', true),
            ],
            prescriptionRequired: true,
            prescriberName: 'Dr. Sara Malik',
            prescriberRegNo: 'MED-67890',
            customerId: $fatima?->id,
        );
    }

    /**
     * @param  array{cashier: User, lines: list<CartLine>, payments: list<PaymentLine>, customer_id: ?int, sold_at: CarbonInterface}  $saleData
     */
    private function completeAndBackdate(CheckoutService $checkout, Branch $branch, array $saleData): void
    {
        $sale = $checkout->complete(
            branch: $branch,
            cashier: $saleData['cashier'],
            lines: $saleData['lines'],
            payments: $saleData['payments'],
            customerId: $saleData['customer_id'],
        );

        $this->backdateSale($sale, $saleData['sold_at']);
    }

    private function backdateSale(Sale $sale, CarbonInterface $soldAt): void
    {
        $sale->update(['sold_at' => $soldAt]);
        $sale->payments()->update(['paid_at' => $soldAt]);
    }
}
