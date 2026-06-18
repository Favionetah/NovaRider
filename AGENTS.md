# NovaRider — AGENTS.md

Motorcycle repair shop management system.

## Stack

- **Backend:** `backend/` — Laravel 12 (PHP ^8.2), MySQL, Sanctum auth
- **Frontend:** `frontend/` — Vue 3 + Vite 8 + Pinia + Vue Router (history mode) + axios
- **Animations:** GSAP 3.12 (CDN via `index.html`)
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

## Design System

### Color Palette
- `#042D29` — Primary (headers, buttons, navbar, backgrounds)
- `#929079` — Secondary (subtle text, borders, disabled states)
- `#741102` — Accent (hover, errors, active states, decorative elements)
- `#FFFFFF` — White (cards, inputs, modal backgrounds)
- `#F5F4F0` — Page background (light warm tone)

### Typography
- Font: `Inter` from Google Fonts (loaded in `index.html`)
- Headings: 600–700 weight, `#042D29`
- Body: 400–500 weight, `#1F2937`
- Subtle text: `#929079`

### Animations
- Library: **GSAP 3.12** loaded from CDN in `index.html`
- Entrance animations: `gsap.timeline()` with `from()` / `fromTo()` in `onMounted`
- Micro-interactions: `gsap.to()` for hover effects (scale, y position)
- CSS transitions for input focus, color changes (better perf for simple cases)

### Component Patterns
- **Cards:** White background, 16px border-radius, soft shadow, 4px accent border-top gradient (`#042D29` → `#741102`)
- **Buttons:** 10px radius, 14px padding, primary bg `#042D29`, hover `#052E2A`, active `#741102`. GSAP hover: `scale(1.02)` + `y(-2)`
- **Inputs:** 1.5px border `#D1D5DB`, 10px radius, 12px padding, 40px left padding when icon present, focus border `#042D29` + glow
- **Modals:** Overlay rgba(0,0,0,0.4), card 14px radius, max-width 600px, slide/animate with GSAP or CSS
- **Error messages:** Left border `#741102`, bg `#FFF5F5`, 8px radius, slide-down animation
- **Icons:** SVG inline (no icon library), consistent 20px for inputs, 16px for errors

### Layout
- All pages: `max-width 1000px`, centered, `padding: 32px`
- No external UI libraries — pure Vue + CSS + GSAP

## What is NOT set up yet

- No CI workflows
- No frontend tests (no Vitest, no Cypress, no Playwright)
- No Docker config
