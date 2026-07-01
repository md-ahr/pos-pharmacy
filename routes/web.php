<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SaleReceiptController;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Customers\CustomersIndex;
use App\Livewire\Dashboard\Welcome;
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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tyro-dashboard.index');
    }

    return view('welcome');
});

Route::middleware(['web', 'guest'])->group(function (): void {
    if (config('tyro-login.registration.enabled', true)) {
        Route::get(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'showRegistrationForm'])
            ->name('tyro-login.register');

        Route::post(config('tyro-login.routes.register', 'register'), [RegisterController::class, 'register'])
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

    Route::get('/reports', fn () => view('pharmacy.stub', ['title' => 'Reports']))
        ->middleware('privilege:reports.view')
        ->name('pharmacy.reports');

    Route::get('/settings', fn () => view('pharmacy.stub', ['title' => 'Settings']))
        ->middleware('privilege:settings.manage')
        ->name('pharmacy.settings');

    Route::get('/dashboard/welcome', Welcome::class)
        ->name('pharmacy.dashboard.welcome');
});
