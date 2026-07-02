<?php

use App\Livewire\Inventory\CategoryForm;
use App\Livewire\Inventory\ManufacturerForm;
use App\Models\Category;
use App\Models\Manufacturer;
use Database\Seeders\PharmacyRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PharmacyRoleSeeder::class);
});

test('owner can access category and manufacturer screens', function () {
    createPharmacyContext();

    $this->get(route('pharmacy.inventory.categories'))->assertOk();
    $this->get(route('pharmacy.inventory.categories.create'))->assertOk();
    $this->get(route('pharmacy.inventory.manufacturers'))->assertOk();
    $this->get(route('pharmacy.inventory.manufacturers.create'))->assertOk();
});

test('category form creates and updates categories', function () {
    ['tenant' => $tenant] = createPharmacyContext();
    $parent = Category::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'General Medicines',
    ]);

    Livewire::test(CategoryForm::class)
        ->set('name', 'Antibiotics')
        ->set('parent_id', $parent->id)
        ->call('save')
        ->assertRedirect(route('pharmacy.inventory.categories'));

    $category = Category::query()->where('name', 'Antibiotics')->first();

    expect($category)->not->toBeNull()
        ->and($category->parent_id)->toBe($parent->id);

    Livewire::test(CategoryForm::class, ['category' => $category])
        ->set('name', 'Antibiotics & Antivirals')
        ->set('parent_id', null)
        ->call('save')
        ->assertRedirect(route('pharmacy.inventory.categories'));

    expect($category->fresh())
        ->name->toBe('Antibiotics & Antivirals')
        ->parent_id->toBeNull();
});

test('category name must be unique per tenant', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    Category::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'OTC',
    ]);

    Livewire::test(CategoryForm::class)
        ->set('name', 'OTC')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('category cannot be its own parent', function () {
    ['tenant' => $tenant] = createPharmacyContext();
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);

    Livewire::test(CategoryForm::class, ['category' => $category])
        ->set('parent_id', $category->id)
        ->call('save')
        ->assertHasErrors(['parent_id']);
});

test('manufacturer form creates and updates manufacturers', function () {
    createPharmacyContext();

    Livewire::test(ManufacturerForm::class)
        ->set('name', 'Acme Pharma')
        ->call('save')
        ->assertRedirect(route('pharmacy.inventory.manufacturers'));

    $manufacturer = Manufacturer::query()->where('name', 'Acme Pharma')->first();

    expect($manufacturer)->not->toBeNull();

    Livewire::test(ManufacturerForm::class, ['manufacturer' => $manufacturer])
        ->set('name', 'Acme Pharmaceuticals')
        ->call('save')
        ->assertRedirect(route('pharmacy.inventory.manufacturers'));

    expect($manufacturer->fresh()->name)->toBe('Acme Pharmaceuticals');
});

test('manufacturer name must be unique per tenant', function () {
    ['tenant' => $tenant] = createPharmacyContext();

    Manufacturer::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'WellCare Labs',
    ]);

    Livewire::test(ManufacturerForm::class)
        ->set('name', 'WellCare Labs')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('inventory nav includes categories and manufacturers tabs', function () {
    ['user' => $user] = createPharmacyContext();

    $this->actingAs($user)
        ->get(route('pharmacy.inventory.categories'))
        ->assertOk()
        ->assertSee('href="'.route('pharmacy.inventory.categories').'" class="btn btn-sm btn-primary"', false)
        ->assertSee('href="'.route('pharmacy.inventory.manufacturers').'" class="btn btn-sm btn-ghost"', false);
});
