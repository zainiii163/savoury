# Savoury Spot — Project Plan

## Goal

Ship a real, integrated ordering platform for Savoury Spot (Shorkot City): customer site + secure admin, cash delivery/pickup, MySQL-backed menu and orders.

## Environment snapshot (Phase 1 inspection)

| Tool | Status |
|---|---|
| PHP | 8.2.12 (XAMPP) — OK for Laravel 12 |
| Composer | 2.11.1 via local `composer.phar` — OK |
| Node.js / NPM | v22.20.0 / 10.9.3 — OK (deps not installed yet) |
| MySQL/MariaDB | MariaDB 10.4 on **port 3307** (Docker/WSL occupies 3306) — OK |
| Git | Repo on `main`, remote `origin` → `github.com/zainiii163/savoury.git` |
| Laravel | **12.70.2** already present under `savoury/` |
| `.env` / `APP_KEY` | Present locally (gitignored) |
| `vendor/` | Installed |
| `node_modules/` | Not installed yet (needed from Phase 3 Vite/CSS onward) |

## Roadmap

| Phase | Deliverable | Exit criteria |
|---|---|---|
| **1** | Environment, Laravel, Git, docs | Toolchain verified; docs complete; `artisan` + tests + `/up` health OK |
| **2** | DB architecture, migrations, models, seeders | ✅ Schema + models + seeders verified (2026-10-30) |

| **3** | Layout, homepage, menu UI | ✅ Bootstrap brand UI + MySQL menu (2026-10-30) |
| **4** | Dynamic products + admin menu management | ✅ Secure CRUD + menu search (2026-10-30) |

| **5** | Cart + checkout | ✅ Server-priced cart + checkout (2026-10-30) |
| **6** | Orders, delivery/pickup rules, tracking | ✅ Token tracking + transitions (2026-10-30) |
| **7** | Admin auth + dashboard | ✅ Login + live metrics (2026-10-30) |
| **8** | Admin orders/menu/settings/reporting | ✅ Inbox, cash, settings, reports (2026-10-30) |
| **9** | Integration + JSON contracts | ✅ `API_CONTRACTS.md` + async UX (2026-10-30) |
| **10** | Security, tests, responsive QA, deploy prep | ✅ Docs + suite/build evidence; **no prod claim** (2026-10-30) |

## Preservation policy

Useful existing work (migrations, models, seeders, product read API, `config/savoury.php`) is retained. Phase 2+ will reconcile it with this plan rather than rewrite from scratch.

## Branching

- `main` — stable  
- `feature/*` — phase work  
- Push only when authorized  

## Risk register

| Risk | Mitigation |
|---|---|
| Port 3306 conflict on this workstation | Use MariaDB `3307` in local `.env`; document in SETUP |
| Owner data gaps (fees/hours/address) | Leave tables empty; block invented content |
| Scope creep (gateways, Livewire, Docker) | Require explicit approval |
| Premature “complete” claims | Phase gates + evidence in PROGRESS |

## Immediate next phase after approval

**Phase 2** — formalize/complete database architecture, models, and seeders against `docs/REQUIREMENTS.md` and `docs/ERD.md` without inventing business data.
