# Coolify Pre-Launch Checklist

## Infrastructure

- [ ] Hostinger VPS provisioned (Ubuntu LTS, sufficient RAM for Postgres + app + worker)
- [ ] Coolify installed and admin secured
- [ ] PostgreSQL service created and reachable from app container
- [ ] GitHub repo connected to Coolify application

## Application

- [ ] All env vars set in Coolify (no secrets in git)
- [ ] `APP_DEBUG=false` in production
- [ ] `npm run build` assets present in deploy
- [ ] Migrations run successfully on deploy
- [ ] Queue worker running as separate process
- [ ] Scheduler cron active

## Security

- [ ] HTTPS enabled (Let's Encrypt)
- [ ] Registration endpoint rate-limited
- [ ] `.env` not in repository
- [ ] File permissions correct on `storage/` and `bootstrap/cache/`

## Operations

- [ ] PostgreSQL backup schedule configured
- [ ] Error monitoring configured (Sentry or similar)
- [ ] Health check `/up` responding
- [ ] Staff trained; parallel run plan documented

## Verify after deploy

```bash
curl -f https://your-domain.test/up
php artisan queue:monitor  # or check Coolify worker logs
```
