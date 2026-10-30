# Savoury Spot — Deployment Prep (Phase 10)

This is a **preparation** guide, not a production-ready claim. Ship only after `docs/ACCEPTANCE_CHECKLIST.md` is green.

## Target shape

- PHP 8.2+ FPM or equivalent
- MySQL 8 / MariaDB 10.4+
- HTTPS reverse proxy (nginx/Caddy/Apache)
- Node 20+ available in CI/build host for `npm run build`
- Writable `storage/` and `bootstrap/cache/`

## Build & release steps

1. Clone/checkout the release tag or approved commit.
2. `composer install --no-dev --optimize-autoloader`
3. Copy `.env.example` → `.env` on the server (never commit secrets).
4. Set at minimum:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-domain`
   - `APP_KEY=` (`php artisan key:generate` once)
   - `DB_*` to an isolated production database
   - `SESSION_DRIVER=database`
   - `SESSION_ENCRYPT=true`
   - `SESSION_SECURE_COOKIE=true`
   - `LOG_LEVEL=error` (or `warning`)
5. `php artisan migrate --force`
6. `php artisan db:seed --class=RolePermissionSeeder --force` (roles/permissions only)
7. `php artisan db:seed --class=ProductSeeder --force` **only** if provisional menu is still desired; otherwise load owner menu via admin
8. `php artisan storage:link`
9. `npm ci && npm run build`
10. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
11. `php artisan savoury:create-admin` (interactive; do not bake passwords into scripts committed to git)
12. Point the web root at `public/`
13. Confirm `/up` health endpoint returns OK behind the proxy

## Security checklist on the host

- [ ] TLS certificate valid; HTTP→HTTPS redirect
- [ ] `APP_DEBUG=false`
- [ ] `.env` not web-accessible
- [ ] Directory listing disabled
- [ ] Cron or scheduler only if you later add scheduled jobs (none required for MVP)
- [ ] Backups for MySQL + `storage/app`
- [ ] Admin accounts limited; inactive users flipped off when staff leave

## Rollback sketch

1. Redeploy previous release artifact/commit.
2. Restore DB backup taken before migrate if schema changed.
3. Clear caches: `php artisan optimize:clear`

## What this MVP does **not** include yet

- Online payment gateways
- Multi-branch / multi-tenant
- Redis/queue workers beyond sync/database defaults
- Automated Playwright CI (manual browser checklist is the Phase 10 evidence path)
