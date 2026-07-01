<?php

namespace App\Livewire\Settings;

use App\Enums\PharmacyRole;
use App\Models\Branch;
use App\Models\User;
use App\Services\StaffManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class StaffForm extends Component
{
    public ?User $staff = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = PharmacyRole::Cashier->value;

    public ?int $branchId = null;

    public bool $isActive = true;

    public function mount(?User $user = null): void
    {
        abort_if($user !== null && $user->tenant_id !== Auth::user()?->tenant_id, 404);

        $this->staff = $user;

        if ($user === null) {
            return;
        }

        $this->fill([
            'name' => $user->name,
            'email' => $user->email,
            'role' => (string) $user->role,
            'branchId' => $user->branch_id,
            'isActive' => $user->is_active,
        ]);
    }

    public function save(StaffManagementService $staffService): void
    {
        $tenant = Auth::user()?->tenant;

        abort_if($tenant === null, 403);

        $passwordRules = $this->staff === null
            ? ['required', 'confirmed', Password::defaults()]
            : ['nullable', 'confirmed', Password::defaults()];

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->staff?->id),
            ],
            'password' => $passwordRules,
            'role' => ['required', Rule::enum(PharmacyRole::class)],
            'branchId' => ['nullable', 'integer'],
            'isActive' => ['boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'branch_id' => $validated['branchId'],
            'is_active' => $validated['isActive'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        if ($this->staff === null) {
            $staff = $staffService->create($tenant, $payload);
        } else {
            $staff = $staffService->update($this->staff, $tenant, $payload);
        }

        session()->flash('success', 'Staff member saved.');
        $this->redirectRoute('pharmacy.settings.staff.edit', $staff, navigate: true);
    }

    public function render(): View
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.settings.staff-form', [
            'roles' => PharmacyRole::cases(),
            'branches' => $branches,
        ])->layout('layouts.pharmacy', [
            'title' => $this->staff ? 'Edit Staff' : 'Add Staff',
            'nav' => 'settings',
        ]);
    }
}
