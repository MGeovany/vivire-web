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

## Deploy (Vercel)

1. Push to GitHub.
1. Import the repo in Vercel.
1. Add the same env vars in Vercel.
1. Run migrations from your machine/CI: `php artisan migrate --force`.

## Legacy

Previous PHP app is kept in `legacy/`.
