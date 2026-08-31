# Sugar Pappi — App Storefront APIs

Base URL: `https://sugarpappi.com/api`

Auth: Sanctum Bearer token on all routes marked **Auth**.

```
Authorization: Bearer {token}
Content-Type: application/json
```

Login / register / OTP / Stripe webview endpoints already exist. This file covers the **website flow** APIs: Home, Menu, Sugar Papi Special, Drive-In, Dessert Wholesale, pickup slots, 10-minute window, wholesale 6-hour lock, receipts, and edit size/toppings.

---

## 1. Channels (most important)

Every screen, add-to-cart, and place-order call must send a **channel**. Same product from a different screen is a different order type.

| Channel | App screen | Menu API | After place |
|---|---|---|---|
| `regular` | Home / Menu | `/storefront/home-menu` | 10-min add/remove/edit + new receipt |
| `special` | Pappi Special | `/storefront/special-menu` | Same 10-min window |
| `drive_in` | Drive-In | `/storefront/drive-in-menu` | 20% off at checkout + 10-min window |
| `wholesale` | Dessert Wholesale | `/storefront/wholesale-menu` | **No 10-min timer.** Edit until 6 hours before delivery (7:00 PM) |

Rules:

- Special items show **only** on Special. Never on Home, Menu, Drive-In, or Wholesale.
- Wholesale desserts **do** show on Home/Menu, but adding them there = `regular` order.
- Adding the same SKU from Wholesale (after saving a date) = `wholesale` order.
- Drive-In discount is applied at **checkout**, not on list prices.
- Do not mix channels. A Menu add must not attach to a wholesale order.

**Fulfillment** (how they receive it) is separate from channel:

- `pickup` / `delivery` — regular, special, drive-in
- `wholesale` — Mon/Thu/Sat window 7:00 PM – 10:00 PM (no 15-min pickup slot)

---

## 2. Two time systems (not the same)

### A) Pickup slots — before the order is placed

Used by **regular / special / drive-in** store pickup.

`GET /storefront/pickup-slots?date=YYYY-MM-DD`  
Alias: `GET /time-intervals?date=YYYY-MM-DD`

Admin window, shown in **15-minute** steps (7:00 PM, 7:15 PM, …). Customer must pick a time before payment.

Home delivery: address on the product popup, not this slot.

Wholesale: **does not** use these slots. Customer picks a wholesale delivery date instead.

### B) After-place window

- Regular / Special / Drive-In: `add_to_order_minutes` (usually 10). Any change cancels the old receipt, issues a new one, and **restarts** the timer.
- Wholesale: no 10-min timer. Customer can add / remove / change size & toppings until **6 hours before** the selected delivery (lock around 1:00 PM if delivery is 7:00 PM). After that: `You can no longer update this order.`

---

## 3. App screens → APIs

### Bootstrap (public)

| Method | Path | Use |
|---|---|---|
| GET | `/storefront/config` | Channels, 20% drive-in, timer minutes, wholesale dates/window, shop open/closed, WhatsApp |
| GET | `/storefront/navigation` | Header + side menu (same order as website) |
| GET | `/business-status` | Open/closed + next opening (for schedule) |
| GET | `/stores` | Branches |

### Catalogs (public)

| Method | Path | Channel |
|---|---|---|
| GET | `/storefront/home-menu` | `regular` — no Special; wholesale desserts included as regular |
| GET | `/storefront/menu` | Same as home-menu |
| GET | `/storefront/special-menu` | `special` only |
| GET | `/storefront/wholesale-menu` | `wholesale` + available dates |
| GET | `/storefront/drive-in-menu` | `drive_in` — food menu, 20% label |
| GET | `/storefront/product/{id}` | Variants, toppings, complementary |
| GET | `/storefront/private-bookings` | WhatsApp / events page |

Existing app endpoints (Auth), now channel-aware:

- `GET /home-products?menu_id=all` — Special excluded by default
- `GET /home-products?menu_id=all&channel=special` (or `wholesale` / `drive_in`)
- `GET /menu-items` — each row has `is_special`, `is_wholesale`, `visible_on_*`
- `GET /menu-items?channel=special`

Prefer the `/storefront/*` catalogs for a new app.

### Wholesale date (Auth)

1. `GET /storefront/wholesale-menu` or `GET /wholesale-dates`
2. `POST /storefront/set-wholesale-date`

```json
{ "wholesale_delivery_date": "2026-09-03" }
```

Days: Monday, Thursday, Saturday. Window 7:00 PM – 10:00 PM. Cutoff: **7:00 PM that same day**. After cutoff that date is gone.

Then add items with `channel=wholesale`. Then checkout.

### Pickup time (Auth)

```
POST /storefront/save-pickup-time
{ "date": "2026-08-31", "time": "07:15 PM" }
```

### Store closed (Auth)

```
POST /storefront/schedule
```

Saves next opening as `scheduled_at`. Send `is_scheduled` / `scheduled_at` on place-order (also stored in cart context).

### Cart (Auth)

Keep using existing cart routes. **Always send `channel`.**

```
POST /product-add-to-cart
```

```json
{
  "product_id": 1,
  "product_name": "Sprite",
  "branch_id": 1,
  "quantity": 1,
  "variant_id": 3,
  "price": 6,
  "original_price": 6,
  "order_type": "pickup",
  "channel": "regular",
  "wholesale_delivery_date": null,
  "adding_to_order_id": null,
  "toppings": "[]"
}
```

Wholesale without a valid date → `422` `WHOLESALE_DATE`.

Other cart routes:

- `GET /get-user-cart-items` — `summary.channel`, `drive_in_discount`, wholesale date, pickup flags
- `POST /cart-update-quantity/{id}`
- `POST /delete-cart-item/{id}`
- `POST /continue-to-payments`
- `POST /storefront/checkout-preview` — selected items + Drive-In 20%
- `GET /storefront/cart-context` / `POST /storefront/cart-context`

Checkout selected items only:

```json
{ "cart_item_ids": [11, 12] }
```

### Place order (Auth)

```
POST /place-order
```

```json
{
  "channel": "drive_in",
  "fulfillment": "pickup",
  "order_type": "pickup",
  "pickup_time": "07:15 PM",
  "branch_id": 1,
  "delivery_address": "",
  "tip": 0,
  "points_to_redeem": 0,
  "cart_item_ids": [11],
  "wholesale_delivery_date": null,
  "is_scheduled": false,
  "scheduled_at": null
}
```

Wholesale:

```json
{
  "channel": "wholesale",
  "fulfillment": "wholesale",
  "order_type": "wholesale",
  "menu_type": "wholesale",
  "wholesale_delivery_date": "2026-09-03",
  "branch_id": 1
}
```

Response includes Stripe checkout URL (same payment-first flow as before).

### My Orders / timer / receipt (Auth)

| Method | Path | Use |
|---|---|---|
| GET | `/orders` | Full list with `channel`, `can_modify`, `state`, items |
| GET | `/orders/{id}` | Detail |
| GET | `/my-orders-status?status=Pending` | Legacy list + extra `orders[]`, `channel_label`, `receipt_print_url` |
| GET | `/order-state?order_id=` | Timer / wholesale lock text, `remaining_seconds`, `message` |
| GET | `/orders/{id}/receipt` | JSON snapshot (print this in-app) |
| GET | `/orders/{id}/receipt?html=1` | 80mm HTML (WebView; send Bearer) |

Receipt rules:

- Labels: Takeaway / Home Delivery / Wholesale Delivery
- Wholesale shows delivery date + 7:00 PM – 10:00 PM
- Drive-In shows 20% discount
- Only the **active** receipt. After an edit, previous receipt is cancelled

### Change a placed order (Auth)

Allowed only while `can_modify` is true.

**Add more items**

1. `POST /orders/{id}/start-add-items` → returns `browse_endpoint` (wholesale always wholesale menu)
2. Add to cart with `channel` + `adding_to_order_id`
3. `POST /orders/add-from-cart`

Or send items directly: `POST /orders/add-items` `{ "order_id", "items": [...], "channel" }`

Leave wholesale add-flow if user opens Home/Menu: `POST /orders/{id}/cancel-add-items`

**Remove**

```
POST /orders/remove-items
{ "order_id": 12, "item_ids": [45] }
```

**Change size / toppings / qty** (10-min or wholesale 6-hour window)

1. `GET /orders/{orderId}/items/{itemId}/options` — variants + topping groups, current selection
2. `POST /orders/update-item`

```json
{
  "order_id": 12,
  "item_id": 45,
  "variant_id": 3,
  "quantity": 1,
  "update_toppings": true,
  "toppings_by_category": { "8": [21, 22] }
}
```

Any successful change: old receipt cancelled, new receipt, timer restarts (non-wholesale).

---

## 4. Flow cheatsheet

| Customer wants | Screen | Must do | After place |
|---|---|---|---|
| Normal dessert | Home / Menu | Pickup or delivery + pickup time | 10-min window + print receipt |
| Bulk wholesale | Dessert Wholesale | Save Mon/Thu/Sat date, then add | No 10-min. Edit until 6h before delivery. Receipt shows wholesale date |
| Special items | Pappi Special | Order like a normal item | 10-min + receipt |
| 20% off full food menu | Drive-In | Checkout applies 20% | 10-min + receipt shows discount |

---

## 5. Example: `GET /storefront/config`

Returns `channels[]`, `drive_in_discount_percent`, `add_to_order_minutes`, `pickup_interval_minutes`, `wholesale.dates`, `wholesale.window`, `wholesale.modify_hours_before_delivery` (6), `business`, `whatsapp`.

Use this on app launch.

---

## 6. Example: order state

`GET /order-state?order_id=12`

```json
{
  "status": true,
  "data": {
    "can_add_items": true,
    "remaining_seconds": 480,
    "remaining_time": "08:00",
    "add_minutes": 10,
    "is_wholesale": false,
    "channel": "regular",
    "channel_label": "Regular Order",
    "message": "You have 10 minutes to add or remove items. Any change cancels the old receipt...",
    "receipt_version": 2,
    "receipt_json_url": "https://sugarpappi.com/api/orders/12/receipt",
    "receipt_print_url": "https://sugarpappi.com/api/orders/12/receipt?html=1"
  }
}
```

Wholesale `message` uses the 6-hour lock and `lock_at_label`.

---

## 7. App coding rules

1. One **channel** per screen. Keep it in cart context.
2. Wholesale: date first, then add, then pay. No 15-min pickup slot.
3. Same dessert from Home = regular; from Wholesale page = wholesale.
4. Special never on Home/Menu/Drive-In/Wholesale lists.
5. Drive-In 20% only at checkout.
6. After any change, print only the new active receipt.
7. Edit size/toppings via `/orders/{id}/items/{itemId}/options` then `update-item`. Do not assume the product relation on the order row is enough.
8. Existing `/home-products` and `/menu-items` still work; new screens should use `/storefront/*`.

---

## 8. Auth reminder (existing)

| Method | Path |
|---|---|
| POST | `/register-user` |
| POST | `/register-verify-otp` |
| POST | `/login-user` |
| POST | `/resend-otp` |
| POST | `/user-forget-password` |
| POST | `/logout` |
| GET | `/user-get-profile` |

Gallery, FAQ, rewards, referrals: existing routes under Auth (`/gallery`, `/faq`, `/get-user-reward-amount`, `/referral/...`).
