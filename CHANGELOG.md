# Changelog

Generated: 2025-11-05

This file summarizes notable changes made in the workspace (branch: kandy). It was created from the repository state and edit history produced during an audit and feature work (AJAX cart, DB wrapper, transactional checkout, auth AJAX wiring).

## Summary — high level

- Implemented a minimal PDO `Database` wrapper and migrated several backend pages to use it.
- Added a JSON cart API (`cart_api.php`) with endpoints: add, bulk_update, remove, get. Supports session-based carts for guests and DB-backed carts for logged-in users.
- Implemented merge-on-login logic so a session cart is persisted to the DB cart on successful login.
- Reworked cart UI to be AJAX-driven: added `src/js/cart.js` to perform add/update/remove without full page reloads and update the navbar badge.
- Implemented transactional checkout (row locking) in `place_order.php` to avoid overselling.
- Added small frontend helpers and placeholder JS files to avoid 404s and provide UI integrations (`main.js`, `auth.js`, `validation-integration.js`, `reveal.js`, `reviews.js`).

## Files added

- src/backend/Database.php
  - Singleton PDO wrapper exposing `getInstance()`, `getConnection()`, `query()`, `fetchAll()`, `fetchOne()`, `execute()`, `beginTransaction()`, `commit()`, `rollBack()` and `lastInsertId()`.

- src/backend/cart_api.php
  - New JSON API for cart operations (add, bulk_update, remove, get). Handles stock checks, session or DB cart, and returns cart counts & errors.

- src/js/cart.js
  - AJAX cart client: intercepts cart forms, updates quantities in-place, removes rows, updates subtotal/total and navbar badge.

- src/js/main.js
  - Site-wide helper exposing `window.PromiseUI.refreshCartCount()` and `updateCartCount()` used by other scripts.

- src/js/auth.js
  - AJAX wiring for modal login/register forms; displays inline messages and refreshes cart badge on login.

- src/js/validation-integration.js, src/js/reveal.js, src/js/reviews.js
  - Small UI helper/placeholder files (prevent 404s and enable small UI behaviors).

- CHANGELOG.md (this file)

## Files modified

- src/backend/connections.php
  - Now requires `Database.php` and exposes `$pdo = Database::getInstance()->getConnection()` for backwards compatibility.

- src/backend/login_process.php
  - Refactored to use the `Database` wrapper and perform cart merge within a transaction. Returns JSON for AJAX login flows.

- src/backend/place_order.php
  - Refactored to use the `Database` wrapper. Uses `SELECT ... FOR UPDATE` to lock product rows during checkout, validates stock, inserts `orders` and `order_items`, decrements stock, commits/rolls back correctly.

- src/pages/cart.php
  - Added data attributes and markup to support client-side updates (data-product-id, data-unit-price, row-total spans, `cart-subtotal`, `cart-total` IDs). Uses Database wrapper for reads.

- src/pages/product-detail.php and src/pages/shop.php
  - Minor updates to integrate with the new cart API and JS (forms now post to `cart_api.php` when appropriate).

- src/components/modal.html
  - Modal markup was inspected and is wired by `auth.js` to submit login/register via AJAX and display inline messages (`#login-message`, `#register-message`).

- src/layouts/app.php and src/components/navbar.html
  - Layouts were adjusted to include the new JS files and maintain the modal/navbar includes. Navbar exposes `#nav-cart-count` used by `main.js`.

## Notes on verification performed

- PHP lint checks were run with the local XAMPP PHP executable where needed to find and fix syntax issues during refactors.
- Server-side behaviors (cart merge, transactional checkout) were implemented with PDO transactions and row locking.
- Frontend JS intercepts forms and updates the DOM; fallback behavior still does a reload (add-to-cart fallback and login still reload when required to update server-rendered navbar state).

## Pending / recommended items (not implemented yet)

- CSRF protection: currently omitted per prior project decision. Add token generation/validation on state-changing endpoints (`cart_api.php`, `reg_process.php`, `login_process.php`, `place_order.php`) before production.
- Migration SQL: a dedicated migration script for applying schema changes to existing databases is not present (recommended: `src/backend/migrations/20251105_add_stock_and_carts.sql`).
- Automated tests: no PHPUnit or endpoint tests were added. Recommend adding quick integration tests for `cart_api.php` and `place_order.php`.
- Full client-side navbar swap on login: currently the login handler reloads or redirects to allow the server to render the logged-in navbar. If desired, we can implement a client-side fragment swap to avoid reload.

## How to try the changes locally

1. Start XAMPP (Apache + MySQL).
2. Ensure database `wedding_shop` exists and `products` table includes `quantity_in_stock` (see `src/backend/schema.sql`).
3. Open app pages from `src/pages/*.php` in your browser (e.g., http://localhost/Promise/src/pages/shop.php depending on your server setup).
4. Observe cart behavior:
   - Add-to-cart (from product page) shows toast and updates navbar badge without full page reload.
   - Cart page (`cart.php`) Update/Remove operate via AJAX and update subtotals inline.
5. Login via the modal; the login AJAX will merge a session cart into the DB cart and refresh the badge, then reload or redirect to get logged-in UI.

## Contact / next steps

If you want I can:
- Add CSRF protection (high priority for production).
- Create a proper migration SQL and a short README section explaining the upgrade steps.
- Convert navbar updates to fully client-side to avoid reload after login.
- Add a small test harness for `cart_api.php`.

----

(End of changelog)
