<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BranchForm extends Component
{
    public ?Branch $branch = null;

    public string $name = '';

    public string $code = '';

    public string $address = '';

    public string $phone = '';

    public bool $isMain = false;

    public bool $isActive = true;

    public function mount(?Branch $branch = null): void
    {
        $this->branch = $branch;

        if ($branch === null) {
            return;
        }

        $this->fill([
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address ?? '',
            'phone' => $branch->phone ?? '',
            'isMain' => $branch->is_main,
            'isActive' => $branch->is_active,
        ]);
    }

    public function save(): void
    {
        $tenantId = Auth::user()?->tenant_id;

        abort_if($tenantId === null, 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('branches', 'code')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->branch?->id),
            ],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'isMain' => ['boolean'],
            'isActive' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'address' => $validated['address'] !== '' ? $validated['address'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'is_main' => $validated['isMain'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->branch === null) {
            $branch = Branch::query()->create(array_merge($data, ['tenant_id' => $tenantId]));
        } else {
            $this->branch->update($data);
            $branch = $this->branch;
        }

        if ($validated['isMain']) {
            Branch::query()
                ->where('tenant_id', $tenantId)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }

        session()->flash('success', 'Branch saved successfully.');
        $this->redirectRoute('pharmacy.settings.branches', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.branch-form')->layout('layouts.pharmacy', [
            'title' => $this->branch ? 'Edit Branch' : 'Add Branch',
            'nav' => 'settings',
        ]);
    }
}
