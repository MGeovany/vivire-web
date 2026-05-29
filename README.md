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
2. Import the repo in Vercel (Framework: **Other** — not Vite).
3. **No pongas `composer install` en el build command.** Vercel no tiene PHP ahí; `vercel-php` ejecuta Composer automáticamente. El build de assets ya está en `vercel.json`:

   ```
   installCommand: pnpm install
   buildCommand: pnpm build
   ```

   Si lo configuraste a mano en el dashboard, bórralo o déjalo vacío para que use `vercel.json`.
4. Add env vars in Vercel (see `.env.example`).
5. Run migrations from your machine/CI: `php artisan migrate --force` (direct Postgres, port 5432).

## Legacy

Previous PHP app is kept in `legacy/`.
