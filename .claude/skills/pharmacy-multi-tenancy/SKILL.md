---
name: pharmacy-multi-tenancy
description: "Implements and reviews multi-tenant isolation for the Pharmacy POS app. Use when creating tenant-scoped models, BelongsToTenant trait, TenantScope, registration provisioning, branch context, middleware, tenant isolation tests, or debugging cross-tenant data leaks. Also use for queued jobs and Artisan commands that must explicitly set tenant context."
---

# Pharmacy Multi-Tenancy

## Implementation

```
app/Models/Concerns/BelongsToTenant.php   # trait
app/Models/Scopes/TenantScope.php         # global scope
```

Trait boot should apply `TenantScope` and auto-set `tenant_id` on create from authenticated user.

## Registration flow

On self-serve signup (extend Tyro register):

```php
DB::transaction(function () {
    $tenant = Tenant::create([...]);
    $branch = $tenant->branches()->create(['is_main' => true, ...]);
    $user = User::create(['tenant_id' => $tenant->id, 'branch_id' => $branch->id, 'role' => 'owner', ...]);
});
```

Rollback entirely on any failure.

## Branch context

- Session stores active `branch_id` after login.
- `branch_id` null on user = access all branches (owner/manager).
- Cashier/pharmacist restricted to assigned branch.
- "Switch branch" UI for tenant-wide roles only.

## Middleware

- Ensure tenant + branch context set before POS/inventory routes.
- Role gates: owner, manager, pharmacist, cashier per `PHARMACY_POS_PLAN.md`.

## Jobs & console (critical)

`TenantScope` uses `Auth::user()`. Background work must:

```php
// Option A: pass tenant_id into job, scope queries explicitly
Product::withoutGlobalScope(TenantScope::class)
    ->where('tenant_id', $this->tenantId)
    ->...

// Option B: authenticate/bind tenant context before scoped queries
```

Never run unscoped queries across all tenants without authorization.

## Required tests

`tests/Feature/TenantIsolationTest.php`:

- Create tenant A + B with products/stock.
- Authenticate as tenant A user — assert tenant B records invisible.
- Repeat for create/update/delete paths on new features.

See [rules/tenant-scope.md](rules/tenant-scope.md) for scope implementation notes.
