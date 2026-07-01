# TenantScope Implementation Notes

## BelongsToTenant trait

```php
protected static function bootBelongsToTenant(): void
{
    static::addGlobalScope(new TenantScope);

    static::creating(function (Model $model): void {
        if (auth()->check() && ! $model->tenant_id) {
            $model->tenant_id = auth()->user()->tenant_id;
        }
    });
}
```

Adjust to match existing auth user shape once Tyro is installed.

## TenantScope

Apply `where('tenant_id', auth()->user()->tenant_id)` when user is authenticated and has `tenant_id`.

When no authenticated user: **do not filter** (allows migrations/seeds) OR throw in production routes — document behavior in scope.

## Intentional bypass

Only for super-admin tooling (if ever built):

```php
Model::withoutGlobalScope(TenantScope::class)
```

Must be wrapped in explicit authorization check.

## Common mistakes

| Mistake | Fix |
|---------|-----|
| Manual `where tenant_id` on scoped model | Remove — redundant |
| Job queries all tenants' data | Pass `tenant_id`, scope explicitly |
| `User` model scoped same as products | Users may need different scope rules |
| Registration without transaction | Wrap Tenant+Branch+User in one transaction |
