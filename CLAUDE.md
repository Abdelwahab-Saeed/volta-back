# Volta backend

Laravel 12 / PHP 8.4 API for the Volta store (frontend: the separate `volta-app` repo) plus a Blade admin dashboard.
Read `docs/offers-and-money.md` for the full history and reasoning behind money, offers and checkout.

## Commands
- Setup: `composer install && cp .env.example .env && php artisan key:generate`
- Tests: `php artisan test` (SQLite in memory, no MySQL needed). All tests must pass before pushing.
- Dev data: `php artisan migrate:fresh --seed` (includes offers of every type)

## Rules that are easy to break
- **Money is integer piasters** everywhere in the DB and PHP (`MoneyCast`). Pounds only at the edges:
  `Money::fromPounds()` on form/API input, `Money::toPounds()` in Resources, `Money::format()` in Blade.
  The API returns pounds. Never store or compute money as float.
- **Offers are bought directly, never through the cart**: `GET /api/offers/{id}/quote` → `POST /api/checkout/offer`
  (`OfferCheckoutController`). `/api/checkout` rejects `offer_id`. Cart-wide discounts are coupons.
  Only two offer types: `bundle` (per-product `offer_product.quantity`) and `buy_x_get_y`.
- **One pricing path**: `OfferPricing` prices offers (page, quote and checkout use it); `OrderPlacer` creates every
  order (stock locking, gifts, idempotency, notifications, Meta). Don't duplicate this logic in controllers.
- **The API sends offers display-ready** (`OfferResource`: localized `display` texts + prices, `purchase` info).
  Offer wording lives in `lang/{ar,en}/offers.php`; the frontend must not hardcode offer types or do offer math.
  A new offer type = backend only (pricing in `Offer`/`OfferPricing`, text in `lang/*/offers.php`, admin form).
- Checkout guards: `expected_total` (409 when prices changed) and `Idempotency-Key` (a retry returns the same order).
- Guest cart joins the account cart on login via `POST /api/cart/merge` (larger quantity wins, so repeats are safe).
- **The storefront only shows and sells visible products**: `Product::visible()` in queries, `isSellable()` on a loaded
  model (switched on, not deleted, in a switched-on category that is not deleted). Every public product, wishlist,
  comparison, cart and checkout path goes through them; cart responses use `Cart::loadAvailableItems()`.
- **Nothing is deleted for real**: every model uses `SoftDeletes`, except the cart, wishlist and comparison rows
  (a customer's current picks). A delete keeps the row's uploaded files. Relations an order reads (`Order::user()`,
  `Order::offer()`, `OrderItem::product()`) use `withTrashed()`. `coupons.code` and `users.email` are unique only among
  rows that are not deleted, checked by validation (`Rule::unique(...)->withoutTrashed()`), not by the database.
- **Meta tracking must never break a request**: all calls go through `MetaService::send()` (timeout + logged failures).
  Tests block real HTTP (`Http::preventStrayRequests()` in `tests/TestCase.php`); fake what you need.
- The admin dashboard is Arabic-only; API texts follow `Accept-Language` (`SetLocale` middleware).
- Admin UI: Tailwind Play CDN (no build step). The design system (brand colours, Cairo font, `.card`, `.btn-*`,
  `.input`, `.data-table`, `.badge-*`...) lives in `resources/views/admin/partials/head.blade.php`; icons are
  `<x-admin.icon name="..."/>`, plus `<x-admin.order-status>`, `<x-admin.money>`, `<x-admin.empty-state>`.
  Pages set `title`, optional `subtitle` / `actions` / `back`, and the layout draws the header. Destructive actions
  go through `confirmAction(formId, message)`. Nothing may live under `public/admin/` (it would shadow `/admin`).
- Migrations that change data must stop with a clear message on unexpected data instead of guessing,
  and must roll back cleanly. Old money columns are kept as `*_legacy` until a later cleanup migration.

## Deploying
Backend and `volta-app` changes to offers/cart/checkout must be deployed together. Before migrating production:
`php artisan down`, full `mysqldump`, then `php artisan migrate --force`.

## Git
Don't push to `main` directly; work on a branch and open a PR.
