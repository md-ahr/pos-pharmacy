<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use App\Services\TenantSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TenantSettingsForm extends Component
{
    public string $currencyCode = 'USD';

    public string $taxRate = '0.1500';

    public string $receiptHeader = '';

    public string $receiptFooter = '';

    public ?int $defaultBranchId = null;

    public function mount(TenantSettingsService $settingsService): void
    {
        $tenant = Auth::user()?->tenant;

        abort_if($tenant === null, 403);

        $settings = $settingsService->forTenant($tenant);

        $this->fill([
            'currencyCode' => $settings->currency_code,
            'taxRate' => number_format((float) $settings->tax_rate, 4, '.', ''),
            'receiptHeader' => $settings->receipt_header ?? '',
            'receiptFooter' => $settings->receipt_footer ?? '',
            'defaultBranchId' => $settings->default_branch_id,
        ]);
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $tenant = Auth::user()?->tenant;

        abort_if($tenant === null, 403);

        $validated = $this->validate([
            'currencyCode' => ['required', 'string', 'size:3'],
            'taxRate' => ['required', 'numeric', 'min:0', 'max:1'],
            'receiptHeader' => ['nullable', 'string', 'max:2000'],
            'receiptFooter' => ['nullable', 'string', 'max:2000'],
            'defaultBranchId' => ['nullable', 'integer'],
        ]);

        $settingsService->update($tenant, [
            'currency_code' => strtoupper($validated['currencyCode']),
            'tax_rate' => number_format((float) $validated['taxRate'], 4, '.', ''),
            'receipt_header' => $validated['receiptHeader'] !== '' ? $validated['receiptHeader'] : null,
            'receipt_footer' => $validated['receiptFooter'] !== '' ? $validated['receiptFooter'] : null,
            'default_branch_id' => $validated['defaultBranchId'],
        ]);

        session()->flash('success', 'Pharmacy settings saved.');
    }

    public function render(): View
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('livewire.settings.tenant-settings-form', [
            'branches' => $branches,
        ])->layout('layouts.pharmacy', [
            'title' => 'General Settings',
            'nav' => 'settings',
        ]);
    }
}
