# Savoury Spot — Seed Plan

## Principles

1. Seed **only** owner-safe catalogue data and RBAC definitions.
2. Never hard-code admin passwords or staff accounts.
3. Never invent hours, address, delivery zones, or fees.
4. Seeders must be **idempotent**.
5. Prices are stored as PKR minor units (paisa).

## Seeded in Phase 2

### Roles & permissions (`RolePermissionSeeder`)

| Role | Permissions |
|---|---|
| `admin` | manage-menu, manage-orders, collect-cash, manage-settings, view-reports, view-audit-logs |
| `staff` | manage-orders, collect-cash, view-reports |

No users are attached.

### Category

| Field | Value |
|---|---|
| name | Menu |
| slug | `menu` |
| is_active | true |

### Products (provisional — owner confirmation required)

| Name | Slug | `price_minor` | PKR | `is_featured` |
|---|---|---|---|---|
| Pulao Kabab | `pulao-kabab` | 37500 | 375 | false |
| Single Plate | `single-plate` | 56500 | 565 | false |
| Single Without Kabab | `single-without-kabab` | 46500 | 465 | false |

Implementation: `database/seeders/ProductSeeder.php`.

## Explicitly not seeded

- Users / passwords
- Variants and add-ons (none supplied)
- Delivery zones / fees / min order amounts
- Business hours
- Restaurant settings (address, pause flag, etc.)
- Carts, orders, payments
- Featured flags (left false until owner chooses)

## Commands

```bash
php artisan migrate:fresh --seed
```

Isolated local database only.
