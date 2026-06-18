# NovaRider — AGENTS.md

Motorcycle repair shop management system.

## Stack

- **Backend:** `backend/` — Laravel 12 (PHP ^8.2), MySQL, Sanctum auth
- **Frontend:** `frontend/` — Vue 3 + Vite 8 + Pinia + Vue Router (history mode) + axios
- **Testing:** PHPUnit 11 (SQLite :memory: in tests), no frontend test framework configured
- **Linting (frontend):** `npm run lint` runs oxlint + ESLint sequentially (via `npm-run-all2`)

## Developer commands

### Backend (`backend/`)
```bash
composer setup                   # install + copy .env + key:generate + migrate + npm i + build
composer dev                     # concurrently: php artisan serve + queue:listen + pail + npm run dev
composer test                    # config:clear + artisan test (runs PHPUnit via Laravel)
php artisan serve                # dev server (default :8000)
```

### Frontend (`frontend/`)
```bash
npm run dev       # vite dev server
npm run build     # vite build
npm run lint      # oxlint --fix . + eslint --fix --cache
```

### DB management
- Schema reference: `CreacionDB.txt` (full MySQL DDL — all tables prefixed `T`, every table has audit columns `estadoA`/`usuarioA`/`fechahoraA`)
- Seed data: `DatosIniciales.txt` (creates 4 roles + admin user)
- **Admin login:** `admin` / `admin123` (role: Administrador)
- `.env` currently configured for MySQL `novarider` database (root/root)
- Tests use SQLite in-memory (see `phpunit.xml`)

## Architecture

- Backend routes: `routes/web.php` (applies Sanctum SPA auth for stateful domains)
- Frontend entry: `frontend/src/main.js` — creates Vue app with Pinia + Router
- Vue alias `@` → `frontend/src/` (configured in vite.config.js)
- Backend models under `App\Models`, controllers under `App\Http\Controllers`
- Only scaffold code exists so far (User model, base Controller, empty routes, empty Vue routes/stores)

## Conventions

- **Indentation:** PHP = 4 spaces, JS/Vue = 2 spaces
- **Line endings:** LF, UTF-8, trailing whitespace trimmed, final newline
- **Node engine:** `^22.18.0 \|\| >=24.12.0`
- **DB tables:** All prefixed with `T` (e.g., `TPersonas`, `TUsuarios`) — do NOT use plain names
- **Soft delete / audit:** Every table has `estadoA` (boolean), `usuarioA` (int FK), `fechahoraA` (datetime)
- **Audit trail:** `TAuditoriaGeneral` table for insert/update/delete tracking

## What is NOT set up yet

- No CI workflows
- No frontend tests (no Vitest, no Cypress, no Playwright)
- No Docker config
- No API routes defined (only web route returning welcome view)
- No Vue components beyond App.vue scaffold
- No custom backend models or controllers besides User.php and Controller.php
