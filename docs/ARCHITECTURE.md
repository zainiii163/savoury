# Savoury Spot — Architecture

## Application shape

**Modular Laravel monolith** (Blade + Bootstrap), not a SPA and not a separate API app.

```text
Browser (Blade + Bootstrap 5 + vanilla JS)
        │
        ▼
HTTP routes (web primarily; JSON only for cart/status UX)
        │
        ├── Controllers  → Form Requests → Policies
        ├── Services     → DB transactions / pricing / status rules
        └── Eloquent models
                │
                ▼
             MySQL
```

## Layers

| Layer | Responsibility |
|---|---|
| Controllers | HTTP in/out only |
| Form Requests | Validation |
| Policies / middleware | Authorization |
| Services | Checkout, pricing, status transitions, cash collection |
| Models | Persistence + relations |
| Blade components/layouts | UI composition |
| Resources (optional) | JSON shapes for async endpoints |

## Money

**Decision (preserved from existing implementation):** store money as **integer PKR minor units** (`*_minor`, paisa). One PKR = 100 minor units.

- Avoids floating-point and decimal rounding bugs  
- Public/Blade view models expose `*_pkr` for display  
- Configured in `config/savoury.php` (`currency`, `money_scale`)

Order lines store **price snapshots** so later catalogue edits do not rewrite history.

## Time

- Persist timestamps in **UTC**  
- Display in **Asia/Karachi**

## Auth & roles

- Staff/admin users in `users` with `is_active`  
- RBAC tables: `roles`, `permissions`, pivots  
- Guest customers place orders without accounts; tracking uses `tracking_token_hash` (hash only)

## Cart

- Session-based guest cart for MVP (server-authoritative totals)  
- Optional DB cart tables already outlined for future logged-in continuity

## Order & payment separation

- `orders.status` = fulfilment workflow  
- `payments` / `payment_status` = cash collection  
- Cash can remain unpaid after delivery/collection until staff records it

## Frontend integration

- Prefer Blade form posts for multipage flows  
- JSON endpoints only for: cart mutations, checkout totals preview, order status refresh, admin order inbox refresh  
- Contracts: `docs/API_CONTRACTS.md`

## Tooling constraints

Approved: Laravel, Blade, Bootstrap 5, custom CSS, vanilla JS, MySQL, Composer, NPM, Vite, PHPUnit, Playwright-class browser tests.

Requires approval before adding: Livewire, Alpine, Redis, Docker, React/Vue/etc.

## Existing codebase note

Migrations, catalogue models, provisional seeder, and a read-only public product JSON surface already exist from prior work. They are **preserved** and will be reviewed/completed under Phase 2–4 rather than deleted.
