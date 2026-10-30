# Savoury Spot — API Contracts (Phase 9)

Blade form posts remain the primary multipage UX. JSON endpoints exist only for async refresh and optional cart/checkout preview clients.

**Money:** Server arithmetic uses `*_minor` (PKR paisa). Public/async payloads also expose `*_pkr` for display. Never trust client totals.

**Auth:** Session cookie + CSRF (`X-CSRF-TOKEN` or `X-XSRF-TOKEN`) for mutating web-session JSON. Catalogue product JSON is public and read-only.

---

## Public catalogue

### `GET /api/products`

Throttle: 60/min.

```json
{
  "data": [
    {
      "id": 1,
      "slug": "pulao-kabab",
      "name": "Pulao Kabab",
      "description": null,
      "price_pkr": 375,
      "image_url": null,
      "available": true,
      "category": { "id": 1, "slug": "menu", "name": "Menu" },
      "variants": [
        { "id": 1, "name": "Full", "price_pkr": 400, "available": true, "is_default": true }
      ],
      "addons": [
        { "id": 1, "slug": "raita", "name": "Raita", "price_pkr": 50 }
      ]
    }
  ]
}
```

### `GET /api/products/{slug}`

Same item shape under `data`. Inactive / unknown → **404**.

---

## Session cart (web session)

Mounted under web middleware so the guest session cart is shared with Blade `/cart`.

| Method | Path | Notes |
|---|---|---|
| `GET` | `/api/cart` | Optional `?delivery_zone_id=` |
| `POST` | `/api/cart/items` | Body: `product_id`, optional `product_variant_id`, `addon_ids[]`, `quantity` |
| `PATCH` | `/api/cart/items/{key}` | Body: `quantity` (`0` removes) |
| `DELETE` | `/api/cart/items/{key}` | |

Success envelope:

```json
{
  "message": "Added to cart.",
  "data": {
    "items": [
      {
        "key": "…",
        "product_id": 1,
        "product_name": "Pulao Kabab",
        "product_slug": "pulao-kabab",
        "variant_id": 1,
        "variant_name": "Full",
        "addon_ids": [1],
        "addons": [{ "id": 1, "name": "Raita", "price_minor": 5000, "price_pkr": 50 }],
        "quantity": 1,
        "unit_price_minor": 40000,
        "unit_price_pkr": 400,
        "addons_total_minor": 5000,
        "addons_total_pkr": 50,
        "line_total_minor": 45000,
        "line_total_pkr": 450,
        "available": true
      }
    ],
    "item_count": 1,
    "subtotal_minor": 45000,
    "subtotal_pkr": 450,
    "delivery_fee_minor": 0,
    "delivery_fee_pkr": 0,
    "total_minor": 45000,
    "total_pkr": 450,
    "delivery_zone_id": null,
    "delivery_zone_name": null,
    "currency": "PKR"
  }
}
```

Validation / unavailable items → **422** with Laravel error bag.

---

## Checkout totals preview

### `GET /api/checkout/preview`

Query:

- `fulfillment_type` = `pickup` \| `delivery` (default pickup)
- `delivery_zone_id` required when fulfilment is delivery

```json
{
  "data": {
    "fulfillment_type": "delivery",
    "ordering_open": true,
    "ordering_message": null,
    "cart": { "...same cart data shape..." }
  }
}
```

Used by checkout page JS when zone/fulfilment changes. Submit still recalculates server-side.

---

## Guest order status refresh

### `GET /orders/track/{reference}/status`

Requires a prior successful tracking lookup (or post-checkout grant) in the same session. Without grant → **403**.

```json
{
  "public_reference": "SS-…",
  "fulfillment_type": "pickup",
  "status": "preparing",
  "status_label": "Preparing",
  "payment_status": "unpaid",
  "payment_method": "cash_on_pickup",
  "placed_at": "2026-10-30T…+05:00",
  "completed_at": null,
  "timeline": [
    { "key": "pending", "label": "Pending", "state": "done" },
    { "key": "accepted", "label": "Accepted", "state": "done" },
    { "key": "preparing", "label": "Preparing", "state": "current" },
    { "key": "ready", "label": "Ready for Pickup", "state": "upcoming" },
    { "key": "collected", "label": "Collected", "state": "upcoming" }
  ],
  "is_terminal": false
}
```

Polled every 15s on the tracking show page.

---

## Admin order inbox refresh

### `GET /admin/api/orders/inbox`

Auth: session + `manage-orders`.

Query (optional): `status`, `fulfillment_type`, `payment_status`, `include_closed=1`.

Default excludes delivered/collected/cancelled/rejected unless `status` or `include_closed` is set.

```json
{
  "data": [
    {
      "id": 1,
      "public_reference": "SS-…",
      "customer_name": "…",
      "phone": "03166100080",
      "fulfillment_type": "pickup",
      "status": "pending",
      "status_label": "Pending",
      "payment_status": "unpaid",
      "total_minor": 37500,
      "total_pkr": 375,
      "placed_at": "…",
      "show_url": "https://…/admin/orders/1"
    }
  ],
  "meta": { "count": 1, "generated_at": "…" }
}
```

Polled every 20s on the admin orders index.

---

## Non-goals

- No public order IDOR (tracking requires token + session grant)
- No online payment webhooks
- No separate SPA API versioning until explicitly approved
