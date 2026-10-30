# Savoury Spot — Acceptance Checklist (Phase 10)

**Rule:** Do not claim production readiness until every required row below has evidence. Owner-pending business data must remain empty or owner-supplied — never invented.

## Automated evidence (this repo)

| ID | Check | Evidence |
|---|---|---|
| T1 | Feature tests: auth, cart, checkout, fees, transitions, cash, tracking, APIs, security headers | `php artisan test` — see `docs/PROGRESS.md` |
| T3 | Isolated test DB only | `phpunit.xml` → SQLite `:memory:` |
| T4 | Vite production build | `npm run build` succeeds; assets under `public/build` (gitignored) |
| S1–S5 | Security baselines | CSRF (Laravel), hashed passwords, RBAC middleware, throttles, `.env` gitignored, audit on cash collect, security headers middleware |

## Manual browser evidence (required before launch)

Complete at viewports **360 / 375 / 768 / 1024 / 1440** px. Record date + tester initials in the launch notes.

### Customer journey (T2)

- [ ] Home shows brand colours/tagline/phone; Order now → menu
- [ ] Menu filter/search; product detail add-to-cart (variant/add-on if present)
- [ ] Cart update/remove; checkout pickup cash-on-pickup succeeds
- [ ] Confirmation shows reference + one-time tracking token
- [ ] Track order with token; status timeline updates after admin transition
- [ ] Delivery option disabled until a zone exists; with owner zone, COD fee matches admin setting
- [ ] Ordering pause blocks checkout with clear message

### Admin journey (T2)

- [ ] `/admin/login` rejects bad password; accepts `savoury:create-admin` user
- [ ] Dashboard metrics match real orders (not placeholders)
- [ ] Order inbox: accept → prepare → ready → collected; print ticket
- [ ] Mark cash collected; payment shows paid; audit trail present
- [ ] Menu CRUD + image upload; featured appears on home
- [ ] Settings: pause toggle; hours save; zone fee/min order save
- [ ] Reports period filters show expected sales/cash/product rows
- [ ] Staff role can open orders/reports; cannot open settings/menu if not permitted

### Responsive QA

See `docs/RESPONSIVE_QA.md`. All critical paths usable without horizontal scroll traps on 360px.

## Owner blockers (not software bugs)

- [ ] Confirm provisional menu prices/copy/images
- [ ] Supply delivery zones/fees/min order (or keep delivery off)
- [ ] Supply opening hours / pause policy
- [ ] Supply physical address / map for Contact
- [ ] Create first production admin via artisan (no seeded passwords)
- [ ] Decide GitHub branch protection

## Sign-off

| Role | Name | Date | Result |
|---|---|---|---|
| Builder | | | Automated suite + build |
| Owner | | | Business data + manual journeys |
| Deployer | | | `docs/DEPLOYMENT.md` steps |
