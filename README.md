# Lutong Bahay: Barangay Recipe Exchange

A small recipe-sharing site for our barangay's home cooks, themed around **Filipino home cooking**.
Members post their family recipes, browse and search what neighbors are cooking, save favorites,
and swap tips in the comments.

Built with plain PHP (sessions, OOP, PDO) and HTML/CSS/JavaScript. No frameworks or libraries;
Composer is used only for PSR-4 autoloading.

## Setup

Requirements: PHP 8.1+ (with `pdo_mysql` and `mbstring`), MySQL 8 or MariaDB 10.6+.
The generated Composer autoloader is committed in `vendor/`, so Composer isn't needed to run the
site (run `composer dump-autoload` only if you add classes outside `src/`).

1. **Create the database.** In phpMyAdmin, create a database named `lutong_bahay`
   (collation `utf8mb4_unicode_ci`), open it, go to **Import**, and choose
   `database/lutong_bahay.sql`. This creates the six tables plus categories and demo data.
2. **Database credentials.** `config/database.php` defaults to `root` with no password on
   `127.0.0.1:3306` (Herd / XAMPP defaults). If yours differ, copy
   `config/database.local.example.php` to `config/database.local.php` and edit it
   (that file is git-ignored).
3. **Run it.** The web root is `public/`.
   - **Herd:** put the project in your Herd folder and open `http://<folder-name>.test`
     (Herd serves `public/` automatically).
   - **PHP built-in server:** `php -S localhost:8000 -t public`, then open http://localhost:8000

### Demo accounts

All use the password **`Lutong2026`**.

| Name        | Email                    |
|-------------|--------------------------|
| Aling Nena  | nena@lutongbahay.test    |
| Mang Tonyo  | tonyo@lutongbahay.test   |
| Bea Santos  | bea@lutongbahay.test     |

## Features

- **Accounts:** register, log in, log out. Every page except login and register requires a
  session; guests are redirected to the login page and sent back to the page they wanted afterward.
- **Share a recipe:** title, description, category, servings, ingredients, and steps. The
  ingredients list grows with **+ Add ingredient** (or by pressing Enter in the last row), and rows
  can be removed.
- **Browse:** newest first, keyword search (matches title, description, *and ingredient names*;
  every word must match), category filter chips with counts, pagination, and a "My recipes" view.
- **Recipe page:** ingredients, numbered steps, and comments ("Kwentuhan").
- **Comments:** post, edit (inline), and delete your own.
- **Favorites:** save/unsave with the heart button on cards and recipe pages using `fetch()`,
  so the page never reloads. **Favorites** lists everything you saved; unsaving there removes
  the card in place.
- **Edit/delete your own posts only.** Edited recipes show an "Edited by the author on …" badge,
  and edited comments show "· edited" (with the time on hover). Saving a form with no changes does
  not mark it edited.

### Extra feature: servings scaler

Each recipe stores how many people it serves. On the recipe page, the **Cooking for [−] 4 [+]**
control recalculates every ingredient amount for the number of people you're feeding, and shows
kitchen-friendly fractions (e.g. `1½ cups`, `¾ tsp`). Members can type amounts as `2`, `1.5`,
`1/2`, or `1 1/2`. They're stored as decimals so they can be multiplied.

## Database design

Exactly the six required tables (`database/lutong_bahay.sql` is a phpMyAdmin Quick export):

```
users 1───N recipes N───1 categories
  │           │
  │           ├──1:N── ingredients      (one row per ingredient, ordered by position)
  │           ├──1:N── comments  N───1 users
  │           └──M:N── favorites N───1 users   (pivot table)
```

| Table         | Keys and constraints that keep the data clean |
|---------------|-------------------------------------------|
| `users`       | PK `id`; `email` is **UNIQUE**; only `password_hash` is stored (never the plain password) |
| `categories`  | PK `id`; `name` is **UNIQUE**; seeded with Ulam, Sabaw, Gulay, Almusal, Merienda, Kakanin, Panghimagas |
| `recipes`     | FK `user_id` → users (**CASCADE**); FK `category_id` → categories (**RESTRICT**, so a category in use can't be deleted); `updated_at` is NULL until the author edits; index on `created_at` for "latest first" |
| `ingredients` | FK `recipe_id` → recipes (**CASCADE**); **UNIQUE** (`recipe_id`, `position`) keeps the order unambiguous; `quantity` is a nullable DECIMAL ("salt, to taste" has none) |
| `comments`    | FKs to recipes and users (**CASCADE**); `updated_at` marks edits; index on (`recipe_id`, `created_at`) |
| `favorites`   | Composite **PRIMARY KEY** (`user_id`, `recipe_id`), so a member can't save the same recipe twice; FKs **CASCADE** |

All tables are InnoDB with `utf8mb4` so names like *Ñ* and any emoji are stored correctly.
Deleting a recipe automatically removes its ingredients, comments, and favorites.

## Code structure

```
bootstrap.php              loaded by every page: autoload, DB, session, security headers
config/database.php        the one database connection file (PDO)
database/lutong_bahay.sql  phpMyAdmin export (schema + seed data)
public/                    web root: one small script per page or action
  assets/css/app.css       theme and responsive layout (mobile-first)
  assets/js/app.js         validation, ingredient rows, favorites (fetch), servings scaler
src/                       classes (namespace App\, PSR-4)
  Core/SessionManager.php  the only class that touches $_SESSION: login/logout, flash, CSRF
  Core/View.php            renders views/ inside the layout
  Repositories/*.php       all SQL lives here: User, Category, Recipe, Comment, Favorite
  Validation/Rules.php     every validation limit in one place
  Validation/Validator.php server-side validation and input cleanup
  Support/Quantity.php     parses "1 1/2" and formats 1.5 as "1½"
  helpers.php              e(), redirect(), abort(), …
views/                     templates: they only display data (no SQL)
```

**Why these classes:** each has one job. Page scripts stay short: check the session, validate,
call a repository, render a view. Repositories receive the PDO connection through their
constructor (composition), so no SQL ends up in pages or templates.

## Validation rules (browser and server)

The limits live in `App\Validation\Rules`. The forms print them into HTML attributes
(`required`, `minlength`, `maxlength`, `min`, `max`, `pattern`), `app.js` shows friendly messages
before submitting, and `Validator` enforces the same rules again in PHP.

| Field | Rule |
|---|---|
| Name | 2–60 characters; letters, spaces, `.` `'` `-` |
| Email | valid format, max 190, must not already be registered |
| Password | 8–72 characters, at least one letter and one number, confirmation must match |
| Title | 3–120 characters |
| Description | 10–500 characters |
| Category | must be one of the existing categories |
| Servings | whole number 1–50 |
| Ingredients | 1–40 rows; each needs a name (≤100); amount optional but must look like `2`, `1.5`, `1/2`, `1 1/2`; a unit needs an amount |
| Steps | 10–5000 characters, max 30 steps (one per line; typed numbering like "1." is removed) |
| Comment | 2–1000 characters |

## Security (non-negotiables)

- **Passwords:** `password_hash()` / `password_verify()` (bcrypt), rehashed automatically if PHP's
  default algorithm changes.
- **SQL injection:** every query that uses input is a prepared statement with bound parameters,
  and emulated prepares are turned off. Search terms escape `%` and `_`.
- **XSS:** everything a user typed is printed through `e()` (`htmlspecialchars`). A
  Content-Security-Policy also blocks inline scripts.
- **Sessions:** HttpOnly + SameSite=Lax cookie, strict mode, new session ID on login, full
  cleanup on logout. A session whose account no longer exists is logged out.
- **CSRF:** every form that changes data, including logout and the favorite toggle, carries a
  per-session token.
- **Ownership:** edit and delete check the owner in PHP *and* in the SQL `WHERE user_id = ?`.
- **Responsive:** mobile-first CSS with a collapsible menu on phones, tested at 390px and 1280px
  wide.
