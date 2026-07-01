<?php

namespace App\Livewire\Inventory;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?Supplier $supplier = null;

    public string $name = '';

    public string $contact_name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public bool $is_active = true;

    public function mount(?Supplier $supplier = null): void
    {
        $this->supplier = $supplier;

        if ($supplier === null) {
            return;
        }

        $this->fill([
            'name' => $supplier->name,
            'contact_name' => $supplier->contact_name ?? '',
            'phone' => $supplier->phone ?? '',
            'email' => $supplier->email ?? '',
            'address' => $supplier->address ?? '',
            'is_active' => $supplier->is_active,
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $data = collect($validated)->map(function ($value, $key) {
            if (in_array($key, ['contact_name', 'phone', 'email', 'address'], true) && $value === '') {
                return null;
            }

            return $value;
        })->all();

        if ($this->supplier === null) {
            Supplier::query()->create($data);
        } else {
            $this->supplier->update($data);
        }

        session()->flash('success', 'Supplier saved successfully.');
        $this->redirectRoute('pharmacy.inventory.suppliers', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.inventory.supplier-form')->layout('layouts.pharmacy', [
            'title' => $this->supplier ? 'Edit Supplier' : 'Add Supplier',
            'nav' => 'inventory',
        ]);
    }
}
