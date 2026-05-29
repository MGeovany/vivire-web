# vivire

Diario minimalista. Migrado a **Laravel 10 + Livewire** usando **Postgres (Supabase)**.

## Setup local

1. `composer install`
1. `cp .env.example .env` y configura `APP_KEY` (`php artisan key:generate`) y `DB_*` (Postgres de Supabase)
1. `composer dev`

Abre `http://localhost:8080`.

## Deploy (Vercel)

- Runtime: `vercel-php`
- Entry: `public/index.php`
- Variables obligatorias: `APP_KEY`, `DB_*` (y las que uses para correo, etc.)

`vercel.json` ya incluye overrides para que Laravel escriba caches/views en `/tmp`.

## Legacy

El backend anterior (PHP + Supabase Auth/Storage) quedó en `legacy/` para referencia mientras se completa la migración.
