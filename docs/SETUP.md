# Savoury Spot — Local Setup (Phase 1)

## Prerequisites

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`
- Composer 2
- MySQL 8+ or MariaDB 10.4+
- Node.js/npm (frontend assets; optional for Phase 1 backend verification)
- Git

## First-time setup

From the Laravel app root (`savoury/`):

```bash
composer install
copy .env.example .env          # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

Create an isolated database (do not reuse production/shared DBs):

```sql
CREATE DATABASE savoury_spot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Set `DB_*` in `.env`, then:

```bash
php artisan migrate --seed
php artisan test
php artisan serve
```

## This workstation note (Oct 2026)

On the current Windows host, **Docker/WSL already occupies TCP 3306**, so XAMPP MariaDB was started on **3307**. Local `.env` uses:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=savoury_spot
DB_USERNAME=root
DB_PASSWORD=
```

Automated PHPUnit tests continue to use in-memory SQLite (`phpunit.xml`) and do not require MySQL.

## Full local verification checklist

- [ ] `composer install` succeeds
- [ ] `.env` exists with `APP_KEY` and MySQL settings
- [ ] `php artisan migrate:fresh --seed` creates schema + 3 provisional products
- [ ] `php artisan storage:link` for product images
- [ ] `npm install && npm run build`
- [ ] `php artisan test` passes
- [ ] No invented hours, address, zones, or admin passwords were seeded
- [ ] First admin created only via `php artisan savoury:create-admin`
- [ ] Acceptance rows in `docs/ACCEPTANCE_CHECKLIST.md` understood before any production claim

## Create the first admin (no seeded passwords)

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan savoury:create-admin --name="Owner" --email="owner@example.com"
# password is prompted securely unless --password= is supplied locally
```

Then open `/admin/login`.

## Related docs

- `docs/ACCEPTANCE_CHECKLIST.md` — launch gate
- `docs/DEPLOYMENT.md` — host prep
- `docs/RESPONSIVE_QA.md` — viewport checks
- `docs/API_CONTRACTS.md` — JSON contracts
