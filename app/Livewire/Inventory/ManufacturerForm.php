<?php

namespace App\Livewire\Inventory;

use App\Models\Manufacturer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManufacturerForm extends Component
{
    public ?Manufacturer $manufacturer = null;

    public string $name = '';

    public function mount(?Manufacturer $manufacturer = null): void
    {
        $this->manufacturer = $manufacturer;

        if ($manufacturer === null) {
            return;
        }

        $this->name = $manufacturer->name;
    }

    public function save(): void
    {
        $tenantId = auth()->user()?->tenant_id;

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('manufacturers', 'name')->where('tenant_id', $tenantId)->ignore($this->manufacturer?->id),
            ],
        ]);

        if ($this->manufacturer === null) {
            Manufacturer::query()->create($validated);
        } else {
            $this->manufacturer->update($validated);
        }

        session()->flash('success', 'Manufacturer saved successfully.');
        $this->redirectRoute('pharmacy.inventory.manufacturers', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.inventory.manufacturer-form')->layout('layouts.pharmacy', [
            'title' => $this->manufacturer ? 'Edit Manufacturer' : 'Add Manufacturer',
            'nav' => 'inventory',
        ]);
    }
}
