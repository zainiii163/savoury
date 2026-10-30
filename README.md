# Savoury Spot

Real restaurant ordering platform for **Savoury Spot**, Shorkot City — delivery & pickup, cash only, Laravel Blade + MySQL monolith.

**Tagline:** Special Aroma, Homemade Trust · **Phone:** 0316-6100080

## Stack

Laravel 12 · PHP 8.2+ · MySQL/MariaDB · Blade · Bootstrap 5 · Vite · PHPUnit

## Documentation

| Doc | Purpose |
|---|---|
| [MASTER_PROMPT.md](docs/MASTER_PROMPT.md) | Approved execution contract |
| [PROJECT_PLAN.md](docs/PROJECT_PLAN.md) | Roadmap and environment |
| [REQUIREMENTS.md](docs/REQUIREMENTS.md) | Functional requirements |
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | Technical architecture |
| [PROGRESS.md](docs/PROGRESS.md) | Phase status (living) |
| [SETUP.md](docs/SETUP.md) | Local setup notes |
| [ERD.md](docs/ERD.md) | Database ERD |
| [SEED_PLAN.md](docs/SEED_PLAN.md) | Seed constraints |

## Quick start

```bash
composer install
copy .env.example .env
php artisan key:generate
# configure DB_* for an isolated local database
php artisan migrate --seed
php artisan test
php artisan serve
```

If TCP 3306 is occupied on Windows (e.g. Docker/WSL), point `DB_PORT` at your XAMPP MariaDB instance (often `3307`). See `docs/SETUP.md`.

Provisional menu seed only: Pulao Kabab (375), Single Plate (565), Single Without Kabab (465) PKR — owner verification required before launch.

## Development phases

Work proceeds one phase at a time with approval gates. Current status: **`docs/PROGRESS.md`**.
