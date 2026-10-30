# Savoury Spot — Complete Professional Full-Stack Master Prompt

**Status:** Approved (2026-10-30)  
**This file is the controlling execution contract.** Earlier seven-day sprint notes remain reference only where they do not conflict.

## Business

- **Name:** Savoury Spot — Shorkot City, Punjab, Pakistan
- **Phone:** 0316-6100080
- **Tagline:** Special Aroma, Homemade Trust
- **Currency:** PKR
- **Fulfilment:** Home delivery + restaurant pickup
- **Payments:** Cash on delivery / cash on pickup only (no online gateways until requested)

Provisional seed products (owner verification required before launch):

| Product | Price |
|---|---|
| Pulao Kabab | PKR 375 |
| Single Plate | PKR 565 |
| Single Without Kabab | PKR 465 |

Do **not** invent delivery fees, opening hours, delivery zones, ingredients, or reviews.

## Mandatory stack

- Laravel + PHP + Eloquent
- Blade + Bootstrap 5 + custom CSS + vanilla JS
- MySQL
- Composer, NPM, Vite
- PHPUnit (Pest optional later), Playwright or equivalent for browser QA
- Git + GitHub

**Do not use** React/Next/Vue/Angular, a separate Node backend, Livewire, Alpine.js, Redis, or Docker unless explicitly approved.

## Brand tokens

| Token | Value |
|---|---|
| Primary | `#075B76` |
| White | `#FFFFFF` |
| Warm cream | `#FFF2E7` |
| Accent | `#D68B45` |

## Phased delivery (exact order)

1. Environment, Laravel setup, Git, documentation  
2. Database architecture, migrations, models, seeders  
3. Professional frontend layout, homepage, menu UI  
4. Dynamic product listing and product management  
5. Shopping cart and checkout  
6. Order processing, delivery/pickup, tracking  
7. Admin authentication and dashboard  
8. Admin menu/order management and reporting  
9. Frontend/backend integration and API verification  
10. Security audit, automated tests, responsive QA, deployment preparation  

### Per-phase ritual

1. Explain intended changes briefly  
2. Inspect relevant existing code  
3. Implement the phase  
4. Run relevant commands/tests  
5. Fix errors found  
6. Update `docs/PROGRESS.md`  
7. Summarize changed files  
8. Explain how to verify  
9. **Stop and wait for approval** before the next phase  

## Working rules

- Preserve useful existing work; do not delete/overwrite without approval  
- Never claim production readiness without verification  
- Never commit `.env`, credentials, customer data, or DB dumps  
- Do not push to remote unless authorized  
- JSON endpoints only where async UX needs them; document in `docs/API_CONTRACTS.md` when introduced  
