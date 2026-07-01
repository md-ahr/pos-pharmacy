---
name: pharmacy-deployment-coolify
description: "Deploys the Pharmacy POS Laravel app to Hostinger VPS using Coolify. Use when creating Dockerfile, docker-compose, GitHub Actions CI, deploy scripts, queue worker config, scheduler setup, environment variables, PostgreSQL backups, or SSL/proxy configuration. Do not use Laravel Forge patterns unless explicitly requested."
---

# Pharmacy Deployment — Hostinger VPS + Coolify

## Target architecture

```
Hostinger VPS
  └── Coolify (Docker + reverse proxy + SSL)
        ├── Laravel app container (PHP 8.3+)
        ├── PostgreSQL service
        ├── Queue worker process
        └── Scheduler (cron / schedule:work)
```

## Build pipeline

1. GitHub push → Actions run `php artisan test --compact`.
2. Merge to `main` → Coolify auto-deploy (or manual trigger).

### Build steps (Nixpacks or Dockerfile)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### Post-deploy commands (Coolify)

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Environment variables (Coolify UI — never in repo)

| Variable | Notes |
|----------|-------|
| `APP_KEY` | Generated once |
| `APP_ENV=production` | |
| `APP_URL` | Public HTTPS URL |
| `DB_*` | PostgreSQL connection |
| `QUEUE_CONNECTION` | `database` or `redis` |
| Mail credentials | For notifications |

## Processes

| Process | Command |
|---------|---------|
| Web | `php-fpm` + nginx (Coolify default) |
| Queue | `php artisan queue:work --tries=3 --timeout=90` |
| Scheduler | `* * * * * php artisan schedule:run` or `schedule:work` |

## SSL

Enable Let's Encrypt in Coolify proxy — do not commit certificates.

## Backups

- Schedule `pg_dump` to off-site storage (S3-compatible or Hostinger backup).
- Coolify volume snapshots for app storage if using local disks.

## Local dev vs production

- Local: Laravel Herd at `pos-pharmecy.test` — do not run `php artisan serve` for normal dev.
- Production: never run `migrate` without `--force` in deploy script only.

## Forbidden defaults

- Laravel Forge recipes
- Heroku buildpacks
- Vapor.yml unless explicitly requested

See [checklist.md](checklist.md) for pre-launch verification.
