<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?Customer $customer = null;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public function mount(?Customer $customer = null): void
    {
        $this->customer = $customer;

        if ($customer === null) {
            return;
        }

        $this->fill([
            'name' => $customer->name ?? '',
            'phone' => $customer->phone ?? '',
            'email' => $customer->email ?? '',
            'address' => $customer->address ?? '',
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $data = collect($validated)->map(function ($value, $key) {
            if (in_array($key, ['name', 'phone', 'email', 'address'], true) && $value === '') {
                return null;
            }

            return $value;
        })->all();

        if ($data['name'] === null && $data['phone'] === null && $data['email'] === null) {
            $this->addError('name', 'Provide at least a name, phone, or email.');

            return;
        }

        if ($this->customer === null) {
            $customer = Customer::query()->create($data);
        } else {
            $this->customer->update($data);
            $customer = $this->customer;
        }

        session()->flash('success', 'Customer saved successfully.');
        $this->redirectRoute('pharmacy.customers.show', $customer, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customers.customer-form')->layout('layouts.pharmacy', [
            'title' => $this->customer ? 'Edit Customer' : 'Add Customer',
            'nav' => 'customers',
        ]);
    }
}
