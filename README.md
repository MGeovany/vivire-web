# vivire

Minimalist daily journal.

Laravel + Livewire. Postgres on Supabase. Deploy on Vercel.

## Local

```bash
composer install
pnpm install
pnpm build

cp .env.example .env
php artisan key:generate
php artisan migrate

composer dev
```

App: `http://localhost:8081`

## Env

Set your Supabase Postgres **Transaction pooler** (IPv4):

- `DB_HOST=aws-1-us-west-2.pooler.supabase.com`
- `DB_PORT=6543`
- `DB_USERNAME=postgres.<project_ref>`
- `DB_PASSWORD=...`
- `DB_SSLMODE=require`
