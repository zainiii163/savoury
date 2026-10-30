# Savoury Spot — Requirements

Derived from the approved master prompt. Pending owner decisions are marked **OWNER**.

## Business rules

| ID | Requirement |
|---|---|
| B1 | Single-branch restaurant in Shorkot City |
| B2 | Delivery and pickup only |
| B3 | Cash on delivery / cash on pickup only |
| B4 | No online payment gateway until explicitly requested |
| B5 | Provisional menu: Pulao Kabab 375, Single Plate 565, Single Without Kabab 465 PKR |
| B6 | Prices editable; **OWNER** verification before production launch |
| B7 | Do not invent fees, hours, zones, ingredients, or reviews |

## Customer experience

| ID | Requirement |
|---|---|
| C1 | Pages: Home, Menu, Category menu, Product details, Cart, Checkout, Confirmation, Tracking, About, Contact, Privacy, Terms, Delivery/Cancellation policies |
| C2 | Homepage: nav, hero, Order Now CTA, featured dishes, categories, intro, delivery/pickup info, contact/location, footer |
| C3 | Brand colours: `#075B76`, `#FFFFFF`, `#FFF2E7`, `#D68B45` |
| C4 | Responsive at 360 / 375 / 768 / 1024 / 1440 px |
| C5 | Menu data loads from MySQL |

## Ordering

| ID | Requirement |
|---|---|
| O1 | Session cart for guests: add/remove/qty, subtotal, delivery fee, total |
| O2 | Server recalculates all money; never trust client totals |
| O3 | Checkout fields: name, phone, fulfilment, address (if delivery), zone (if applicable), notes, payment method |
| O4 | Payment method must match fulfilment type |
| O5 | Order created in a DB transaction with item price snapshots, unpaid payment status, unique reference, duplicate protection |
| O6 | Secure tracking via unguessable token (not enumerable public IDs) |
| O7 | Statuses: Pending, Accepted, Preparing, Ready for Pickup, Out for Delivery, Delivered, Collected, Cancelled, Rejected — valid transitions only per fulfilment |

## Admin

| ID | Requirement |
|---|---|
| A1 | Secure admin login; server-enforced authorization |
| A2 | Dashboard metrics from real DB records |
| A3 | Order management including cash collection and print ticket |
| A4 | Menu CRUD with images, variants, add-ons, availability, featured |
| A5 | Settings: restaurant info, hours, zones, fees, min order, order pause |
| A6 | Reporting: daily/weekly/monthly sales, product performance, delivery vs pickup, cash reconciliation |

## Security

| ID | Requirement |
|---|---|
| S1 | Password hashing, CSRF, validation, escaped Blade, safe uploads |
| S2 | Role-based policies/middleware; rate-limited login; session regeneration |
| S3 | Prevent price tampering, unauthorized order updates, IDOR, duplicate checkout |
| S4 | Audit logs for sensitive actions; restricted admin registration |
| S5 | Secrets only in `.env` (never committed) |

## Testing & quality

| ID | Requirement |
|---|---|
| T1 | Feature tests for auth, cart, checkout, fees, transitions, cash, tracking |
| T2 | Browser E2E: customer journey + admin journey (delivery and pickup) |
| T3 | Isolated test DB only |
| T4 | Vite production build succeeds before release claim |

## Pending decisions (OWNER)

1. Delivery zones, fees, and minimum order amounts  
2. Opening hours and temporary pause policy  
3. Physical address / map pin for Contact  
4. Confirmation of provisional menu prices and final product copy/images  
5. First admin account creation process (invite vs local artisan command)  
6. GitHub branch protection / whether `develop` is required beyond `main` + `feature/*`  
