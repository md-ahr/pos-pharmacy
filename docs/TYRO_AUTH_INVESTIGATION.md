# Tyro Auth & RBAC Investigation (Phase 1)

Investigation date: 2026-07-01. Determines how Phase 3 extends auth for multi-tenant pharmacy roles.

## Packages installed

| Package | Version | Role |
|---------|---------|------|
| `hasinhayder/tyro` | ^1.10 | Custom RBAC (roles + privileges), Sanctum API, audit logs |
| `hasinhayder/tyro-login` | ^2.12 | Web auth: login, register, password reset, 2FA, magic links, social |
| `hasinhayder/tyro-dashboard` | ^1.46 | Admin UI: users, roles, privileges, settings, dynamic CRUD |

**Not Spatie.** Tyro ships its own `roles` / `privileges` / pivot tables — do not install `spatie/laravel-permission`.

## Database tables (Tyro migrations)

| Table | Purpose |
|-------|---------|
| `roles` | `name`, `slug` (unique) |
| `user_roles` | User ↔ role pivot |
| `privileges` | `name`, `slug`, `description` |
| `privilege_role` | Role ↔ privilege pivot |
| `tyro_audit_logs` | Auth/RBAC change audit trail |
| `personal_access_tokens` | Sanctum API tokens |

Additional Tyro Login tables: `social_accounts`, 2FA columns on `users`, invitation system, `media`.

## User model integration

`App\Models\User` uses:

- `HasinHayder\Tyro\Concerns\HasTyroRoles` — `assignRole()`, `hasRole()`, `hasPrivilege()`, Gate integration
- `HasinHayder\TyroLogin\Traits\HasTwoFactorAuth` — optional 2FA
- `Laravel\Sanctum\HasApiTokens` — API tokens

## Default seeded roles

From `HasinHayder\Tyro\Database\Seeders\RoleSeeder`:

| Slug | Name |
|------|------|
| `admin` | Administrator |
| `super-admin` | Super Admin |
| `user` | User |
| `customer` | Customer |
| `editor` | Editor |
| `*` | All (wildcard) |

Default seeded privileges include `report.generate`, `users.manage`, `roles.manage`, `billing.view`, and wildcard `*`.

## Web routes (Tyro Login)

Registered automatically when `tyro-login` is installed:

- `GET/POST /login`, `/register`, `/logout`
- Password reset, email verification, 2FA, magic links (feature-flagged in `config/tyro-login.php`)

Config: `config/tyro-login.php` — redirects, registration, branding, lockout, OTP.

New registrations assign role slug `user` by default (`tyro.assign_default_role`).

## Dashboard routes (Tyro Dashboard)

Prefix: `/dashboard` (`TYRO_DASHBOARD_PREFIX`). Middleware: `web`, `auth`.

Admin UI for users, roles, privileges, audits, settings. Config: `config/tyro-dashboard.php`.

`admin_roles` => `['admin', 'super-admin']` for full admin access.

## API routes (Tyro core)

Prefix: `/api` with Sanctum guard. Role/privilege/user CRUD for programmatic management.

## Phase 3 extension plan

1. **Do not replace** Tyro Login controllers — extend registration to atomically create `Tenant` → main `Branch` → owner `User`.
2. **Add pharmacy roles** as new Tyro role slugs: `owner`, `manager`, `pharmacist`, `cashier` (via seeder or `tyro:role-create`).
3. **Add pharmacy privileges** (e.g. `pos.access`, `inventory.manage`, `reports.view`, `settings.manage`) and attach to roles.
4. **Extend `users` table** with `tenant_id`, `branch_id`, `is_active` — pharmacy `role` may mirror Tyro role slug or stay derived from Tyro assignment.
5. **Session context** after login: active `tenant_id`, `branch_id` for scoped queries.
6. **Middleware** using `$user->hasRole()` / `$user->hasPrivilege()` — Tyro Gate rules already registered by `HasTyroRoles`.

## Useful Artisan commands

```bash
php artisan tyro:seed-all              # roles, privileges, bootstrap admin
php artisan tyro:role-list
php artisan tyro:privilege-list
php artisan tyro-dashboard:createsuperuser
php artisan tyro-login:install         # publish login views/config
```

## Config files

- `config/tyro.php` — RBAC models, tables, audit, cache
- `config/tyro-login.php` — auth pages, redirects, registration
- `config/tyro-dashboard.php` — dashboard prefix, branding, admin roles
