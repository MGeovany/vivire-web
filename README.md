# vivire

A minimalist daily journal web app. Write your end-of-day reflections — how you felt, what you thought, and your deeper reflections — and revisit the same date across years.

## Stack

| Layer    | Technology                              |
|----------|-----------------------------------------|
| Frontend | Static HTML + Vanilla JS + CSS          |
| Auth     | Supabase Auth (JS client)               |
| Database | Supabase PostgreSQL (with RLS)          |
| Storage  | Supabase Storage (`journal-media`)      |
| API      | PHP serverless functions (`api/*.php`)  |
| Deploy   | Vercel (`vercel-php@0.7.2` runtime)     |

---

## Setup

### 1. Create a Supabase project

Go to [supabase.com](https://supabase.com) and create a new project.

### 2. Run the database schema

In the Supabase dashboard → **SQL Editor**, run the contents of `supabase/schema.sql`.

### 3. Create the storage bucket

In Supabase → **Storage**, create a new bucket named `journal-media`.
Set it to **Public** so uploaded media can be served directly.

### 4. Configure environment variables

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

### 5. Inject Supabase credentials into index.html

For local development, open `index.html` and replace the placeholder strings:

```html
window.SUPABASE_URL      = 'REPLACE_WITH_SUPABASE_URL';
window.SUPABASE_ANON_KEY = 'REPLACE_WITH_SUPABASE_ANON_KEY';
```

with your actual `SUPABASE_URL` and `SUPABASE_ANON_KEY` values (the anon key is safe to expose in the browser).

### 6. Deploy to Vercel

```bash
npm i -g vercel   # if not installed
vercel deploy
```

### 7. Add environment variables in Vercel

In the Vercel dashboard → your project → **Settings → Environment Variables**, add:

| Key                       | Value                            |
|---------------------------|----------------------------------|
| `SUPABASE_URL`            | `https://your-project.supabase.co` |
| `SUPABASE_ANON_KEY`       | your anon key                    |
| `SUPABASE_SERVICE_ROLE_KEY` | your service role key          |
| `SUPABASE_JWT_SECRET`     | your JWT secret                  |

The PHP functions read these at runtime via `getenv()`.

---

## Project structure

```
vivire-web/
├── index.html          # Single-page app shell + auth UI
├── vercel.json         # Vercel config (PHP runtime, rewrites)
├── .env.example        # Environment variable template
├── css/
│   └── app.css         # Full Notion-inspired stylesheet
├── js/
│   └── app.js          # Vanilla JS app (~500 lines)
├── api/
│   ├── _helper.php     # JWT verification, Supabase REST helpers, CORS
│   └── entries.php     # GET + POST (upsert) for journal entries
└── supabase/
    └── schema.sql      # PostgreSQL schema with RLS policies
```

---

## Local development

Since `api/*.php` requires the Vercel PHP runtime, the easiest local option is:

```bash
vercel dev
```

This runs the Vercel dev server locally, emulating the PHP serverless functions.
Make sure your `.env` file is present — `vercel dev` loads it automatically.

---

## How it works

1. User signs up (name + email + password) via Supabase Auth.
2. A Postgres trigger auto-creates a `profiles` row.
3. On every visit, the journal loads today's 3 sections (feelings / thoughts / reflections).
4. Below the main journal, the same calendar date is shown for +1/+2/+3 years:
   - Future → locked, greyed out.
   - Past → readable, not editable.
   - Today (same month+day) → fully editable.
5. Each section auto-saves after 1.5 s of inactivity.
6. Media (images, audio, video, documents) uploads to Supabase Storage and appears inline.
7. All entries are stored as a JSONB `blocks` array: `[{id, type, content, metadata}]`.
