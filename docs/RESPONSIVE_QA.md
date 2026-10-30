# Savoury Spot — Responsive QA (Phase 10)

Brand tokens: primary `#075B76`, cream `#FFF2E7`, accent `#D68B45`, white `#FFFFFF`.

## Viewports

| Width | Device intent | Pass criteria |
|---|---|---|
| 360 | Small Android | Nav toggles; hero CTA visible; forms usable; no clipped primary buttons |
| 375 | iPhone-class | Same as 360; cart/checkout summary stacks cleanly |
| 768 | Tablet | Menu grid readable; admin tables scroll horizontally if needed without breaking page |
| 1024 | Small laptop | Header links fit; dashboard metric cards in grid |
| 1440 | Desktop | Full layout; no oversized empty gaps in first viewport |

## Pages to spot-check

Storefront: Home, Menu, Product, Cart, Checkout, Confirmation, Track, About, Contact, legal pages.

Admin: Login, Dashboard, Orders list/detail/ticket print, Products, Settings, Zones, Reports.

## Known intentional behaviours

- Admin dense tables may scroll horizontally on narrow screens (prefer usable over crushing columns).
- Print ticket uses a minimal standalone layout (`window.print()`).
- Delivery fields hide until fulfilment = delivery (JS + server validation).

## Evidence log

| Date | Tester | Viewport | Path | Result | Notes |
|---|---|---|---|---|---|
| | | 360 | / → checkout | | |
| | | 375 | /admin/orders | | |
| | | 768 | /menu | | |
| | | 1024 | /admin | | |
| | | 1440 | / | | |
