<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SaleReceiptController;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Customers\CustomersIndex;
use App\Livewire\Inventory\BatchIntake;
use App\Livewire\Inventory\ProductForm;
use App\Livewire\Inventory\ProductsIndex;
use App\Livewire\Inventory\PurchaseOrderForm;
use App\Livewire\Inventory\PurchaseOrdersIndex;
use App\Livewire\Inventory\StockAdjustment;
use App\Livewire\Inventory\StockTransfer;
use App\Livewire\Inventory\SupplierForm;
use App\Livewire\Inventory\SuppliersIndex;
use App\Livewire\Pos\SaleScreen;
use App\Livewire\Reports\ExpiryReport;
use App\Livewire\Reports\InventoryValuationReport;
use App\Livewire\Reports\ProfitMarginReport;
use App\Livewire\Reports\ReportsDashboard;
use App\Livewire\Reports\SalesReport;
use App\Livewire\Reports\TaxReport;
use App\Livewire\Settings\BranchesIndex;
use App\Livewire\Settings\BranchForm;
use App\Livewire\Settings\RegisterShiftManager;
use App\Livewire\Settings\StaffForm;
use App\Livewire\Settings\StaffIndex;
use App\Livewire\Settings\TenantSettingsForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});

Route::middleware(['web', 'guest'])->group(function (): void {
    if (config('tyro-login.registration.enabled', true)) {
        Route::get(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'showRegistrationForm'])
            ->name('tyro-login.register');

        Route::post(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'register'])
            ->middleware('throttle:registration')
            ->name('tyro-login.register.submit');
    }
});

Route::middleware(['auth', 'pharmacy.context'])->group(function (): void {
    Route::get('/pos', SaleScreen::class)
        ->middleware('privilege:pos.access')
        ->name('pharmacy.pos');

    Route::get('/pos/receipt/{sale}', [SaleReceiptController::class, 'show'])
        ->middleware('privilege:pos.access')
        ->name('pharmacy.pos.receipt');

    Route::get('/pos/receipt/{sale}/pdf', [SaleReceiptController::class, 'pdf'])
        ->middleware('privilege:pos.access')
        ->name('pharmacy.pos.receipt.pdf');

    Route::middleware('privilege:pos.access')->prefix('customers')->group(function (): void {
        Route::get('/', CustomersIndex::class)->name('pharmacy.customers');
        Route::get('/create', CustomerForm::class)->name('pharmacy.customers.create');
        Route::get('/{customer}/edit', CustomerForm::class)->name('pharmacy.customers.edit');
        Route::get('/{customer}', CustomerShow::class)->name('pharmacy.customers.show');
    });

    Route::middleware('privilege:inventory.manage')->prefix('inventory')->group(function (): void {
        Route::get('/', function () {
            return view('pharmacy.inventory-hub');
        })->name('pharmacy.inventory');

        Route::get('/products', ProductsIndex::class)->name('pharmacy.inventory.products');
        Route::get('/products/create', ProductForm::class)->name('pharmacy.inventory.products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('pharmacy.inventory.products.edit');

        Route::get('/batch-intake', BatchIntake::class)->name('pharmacy.inventory.batch-intake');

        Route::get('/suppliers', SuppliersIndex::class)->name('pharmacy.inventory.suppliers');
        Route::get('/suppliers/create', SupplierForm::class)->name('pharmacy.inventory.suppliers.create');
        Route::get('/suppliers/{supplier}/edit', SupplierForm::class)->name('pharmacy.inventory.suppliers.edit');

        Route::get('/purchase-orders', PurchaseOrdersIndex::class)->name('pharmacy.inventory.purchase-orders');
        Route::get('/purchase-orders/create', PurchaseOrderForm::class)->name('pharmacy.inventory.purchase-orders.create');
        Route::get('/purchase-orders/{purchaseOrder}/edit', PurchaseOrderForm::class)->name('pharmacy.inventory.purchase-orders.edit');

        Route::get('/adjustments', StockAdjustment::class)->name('pharmacy.inventory.adjustments');
        Route::get('/transfers', StockTransfer::class)->name('pharmacy.inventory.transfers');
    });

    Route::middleware('privilege:reports.view')->prefix('reports')->group(function (): void {
        Route::get('/', ReportsDashboard::class)->name('pharmacy.reports');
        Route::get('/sales', SalesReport::class)->name('pharmacy.reports.sales');
        Route::get('/profit-margin', ProfitMarginReport::class)->name('pharmacy.reports.profit-margin');
        Route::get('/inventory-valuation', InventoryValuationReport::class)->name('pharmacy.reports.inventory-valuation');
        Route::get('/expiry', ExpiryReport::class)->name('pharmacy.reports.expiry');
        Route::get('/tax', TaxReport::class)->name('pharmacy.reports.tax');
        Route::get('/export/pdf', [ReportExportController::class, 'pdf'])->name('pharmacy.reports.export.pdf');
        Route::get('/export/excel', [ReportExportController::class, 'excel'])->name('pharmacy.reports.export.excel');
    });

    Route::middleware('privilege:settings.manage')->prefix('settings')->group(function (): void {
        Route::redirect('/', '/settings/general')->name('pharmacy.settings');
        Route::get('/general', TenantSettingsForm::class)->name('pharmacy.settings.general');
        Route::get('/branches', BranchesIndex::class)->name('pharmacy.settings.branches');
        Route::get('/branches/create', BranchForm::class)->name('pharmacy.settings.branches.create');
        Route::get('/branches/{branch}/edit', BranchForm::class)->name('pharmacy.settings.branches.edit');
        Route::get('/staff', StaffIndex::class)->name('pharmacy.settings.staff');
        Route::get('/staff/create', StaffForm::class)->name('pharmacy.settings.staff.create');
        Route::get('/staff/{user}/edit', StaffForm::class)->name('pharmacy.settings.staff.edit');
        Route::get('/register', RegisterShiftManager::class)->name('pharmacy.settings.register');
    });

});
