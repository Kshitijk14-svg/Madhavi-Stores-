# Database Design Notes — Madhavi Stores

This file records **deliberate** schema decisions so they are not repeatedly
flagged as "anomalies" in future audits. The schema is otherwise normalised to
BCNF for the transactional tables.

## Accepted denormalisations (intentional — do NOT "fix")

### 1. `orders` and `order_items` carry a point-in-time snapshot
`orders` stores `first_name`, `last_name`, `email`, `address`, `city`,
`postal_code`; `order_items` stores `product_name` and `price`.

These duplicate data that also lives in `users` / `products`, but that is the
**correct** design for an order ledger: an order must reflect what was bought, at
what price, shipped to what address, **at the time of purchase** — even if the
product is later renamed/repriced or the customer later edits their profile.
Reconstructing them by join would corrupt order history.

### 2. `products.tags`, `products.gallery_images`, `products.details` are JSON
These are multi-valued attributes stored as JSON arrays (a 1NF deviation).

This is an accepted trade-off: they are **admin-managed presentation data**, read
as a whole, never queried relationally in hot paths, and small. A full pivot-table
normalisation (`tags` + `product_tag`, `product_images`, `product_details`) was
explicitly deferred — it would require rewriting controllers and both the desktop
and mobile Blade trees for marginal benefit. If tag-based filtering ever needs to
scale, revisit then (the shop tag filter currently uses `whereJsonContains`, which
cannot use an index).

### 3. `products.rating` / `products.review_count` are derived caches
These are denormalised aggregates of the `reviews` table. They are kept correct by
`App\Models\Review::recountProduct()`, fired on every review **save and delete**
(see `App\Models\Review::booted()`). They are intentionally **not** in
`Product::$fillable` so they can only change via that recompute path.

### 4. `orders.coupon_code` kept alongside `orders.coupon_id`
`coupon_id` (FK to `coupons`) is the proper relational link. `coupon_code` (string)
is retained for backward compatibility with historical orders and the per-user
usage count query. The stored `orders.discount` amount remains the source of truth
for the historical order total regardless of later coupon edits.

### 5. `users.is_admin` is a derived mirror of `users.role`
`role` (`customer` | `admin` | `superadmin`) is the **single source of truth**.
`is_admin` is kept in sync automatically by `App\Models\User::booted()` (saving
hook) for backward compatibility with older code/queries. Neither is mass-assignable.

## Engine
MySQL/MariaDB connections are pinned to **InnoDB** (`config/database.php`) because
the checkout flow relies on transactions + `lockForUpdate()`, which silently no-op
on MyISAM.
