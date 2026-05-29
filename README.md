# vivire

A minimalist daily journal web app. Write your end-of-day reflections — how you felt, what you thought, and your deeper reflections — and revisit the same date across years.

## Stack

| Layer    | Technology                         |
|----------|------------------------------------|
| Backend  | PHP 8.1+ (Composer)                |
| Frontend | Server-rendered HTML + `editor.js` |
| Auth     | Supabase Auth (server-side cookies)|
| Database | Supabase PostgreSQL (with RLS)     |
| Storage  | Supabase Storage (`journal-media`) |
| Deploy   | Vercel (`vercel-php` runtime)      |

---

## Setup

### 1. Create a Supabase project

Go to [supabase.com](https://supabase.com) and create a new project.

### 2. Run the database schema

In the Supabase dashboard → **SQL Editor**, run the contents of `supabase/schema.sql`.

### 3. Create the storage bucket

In Supabase → **Storage**, create a new bucket named `journal-media`.
Set it to **Public** so uploaded media can be served directly.

### 4. Install PHP dependencies

```bash
composer install
```

### 5. Configure environment variables

```bash
cp .env.example .env
```

Fill in your values:

```
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=eyJ...           # Settings → API → anon public
SUPABASE_SERVICE_ROLE_KEY=eyJ...   # Settings → API → service_role (keep secret!)
SUPABASE_JWT_SECRET=your-secret    # Settings → API → JWT Secret
```

---

## Local development

```bash
composer install
cp .env.example .env   # fill in Supabase credentials
composer dev
```

Opens at **http://localhost:8080**.

The built-in PHP server serves static assets from `public/` and routes all requests through `public/index.php` (FastRoute).

---

## Deploy to Vercel

```bash
vercel deploy
```

Add environment variables in the Vercel dashboard → **Settings → Environment Variables**:

| Key                         | Value                              |
|-----------------------------|------------------------------------|
| `SUPABASE_URL`              | `https://your-project.supabase.co` |
| `SUPABASE_ANON_KEY`         | your anon key                      |
| `SUPABASE_SERVICE_ROLE_KEY` | your service role key              |
| `SUPABASE_JWT_SECRET`       | your JWT secret                    |

---

## Project structure

```
vivire-web/
├── composer.json       # PHP dependencies + dev script
├── bootstrap.php       # Autoload + .env loader
├── config.php          # App constants from getenv()
├── public/
│   ├── index.php       # Front controller (routes)
│   ├── router.php      # Built-in server router
│   ├── css/app.css
│   └── js/editor.js
├── handlers/home.php   # / → journal or redirect
├── auth/               # login, register, logout
├── api/                # save, upload (JSON)
├── journal/            # SSR journal page
├── lib/                # Supabase, auth, entries, blocks
├── templates/          # HTML layout
└── supabase/schema.sql
```

---

## How it works

1. User signs up (name + email + password) via server-side forms → Supabase Auth.
2. Access + refresh tokens stored in HttpOnly cookies.
3. On every visit, the journal loads today's 3 sections (feelings / thoughts / reflections).
4. Below the main journal, the same calendar date is shown for +1/+2/+3 years:
   - Future → locked, greyed out.
   - Past → readable, not editable.
   - Today (same month+day) → fully editable.
5. Each section auto-saves after 1.5 s of inactivity (`editor.js` → `/api/save`).
6. Media uploads to Supabase Storage via `/api/upload`.
7. All entries are stored as a JSONB `blocks` array: `[{id, type, content, metadata}]`.
