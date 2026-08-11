# CLAUDE.md — Waterlift Solar Expo Data Collection System

This file gives Claude Code the persistent context it needs when working on this repository. Read this before making changes.

## Project Purpose

- A **single-purpose** web system for collecting visitor/inquiry data at physical expos Waterlift Solar attends.
- Flow: Admin creates an expo → system generates a QR code → visitor scans QR → fills a short mobile form → submission is stored and linked to that expo → admin views/exports data per expo.
- This is **not** a CRM, marketing platform, or general lead-management system. See "Out of Scope" below — do not add these features even if asked casually in a future session; confirm with the user first if a request seems to cross this line.

## Tech Stack (do not substitute without asking)

- PHP (procedural or lightweight OOP), PDO with prepared statements only
- MySQL (InnoDB, utf8mb4)
- HTML5, Bootstrap 5, vanilla JS + Fetch/AJAX
- QR generation: a PHP library such as `chillerlan/php-qrcode` or `endroid/qr-code` (via Composer)
- Export: native CSV + PhpSpreadsheet (or similar) for Excel-compatible export
- DataTables (client-side) for the admin submissions table
- No frameworks (no Laravel/Symfony) unless explicitly requested — keep footprint small

## Project Structure

```
waterlift-expo-system/
├── config/              ← OUTSIDE web root (db credentials, app config)
├── includes/            ← OUTSIDE web root (Auth, Csrf, Validator, QrGenerator, Duplicate, helpers)
├── database/schema.sql
├── public/               ← WEB ROOT
│   ├── assets/{css,js,img}
│   ├── qr/               (generated QR PNG/SVG per expo)
│   ├── expo/              (public form: index.php + success.php)
│   └── admin/             (login, dashboard, expos/, submissions/)
└── vendor/                (Composer packages)
```

- Only `public/` is web-accessible. `config/` and `includes/` must never be reachable by direct URL.
- Every file under `public/admin/` must call the auth guard as its first action.

## Database Rules

- Core tables: `admin_users`, `expos`, `expo_submissions`, `interests`, `submission_interests`.
- `interests` is a **lookup table**, not an enum and not a comma-separated string. Adding a new interest option later must be a data change, never a code change.
- `submission_interests` is the many-to-many junction table between submissions and interests.
- `expo_submissions.is_possible_duplicate` is a stored flag set at insert time when `phone + expo_id` already exists for that expo. Duplicates are **flagged, never blocked** — the admin decides what to do with them.
- Full schema lives in `database/schema.sql` — treat it as the source of truth; if a change is needed, update the schema file and note the migration, don't silently diverge in code.
- Foreign keys and indexes (especially on `expo_id`, `phone`, `submitted_at`, `slug`) must be preserved in any schema changes.

## Core Business Rules

- One expo = one slug = one QR code = one isolated set of submissions. The same codebase serves every expo; adding a new expo is a new database row, never a code change.
- Public visitors never create accounts or log in.
- An inactive expo shows a friendly closed message, not an error or blank form.
- Server-side validation is authoritative — client-side validation is a UX convenience only, never trust the hidden `expo_id` field or client checks alone.
- Required form fields: full name, phone, project location, at least one interest, follow-up method. Optional: email, message.
- Follow-up method is one of: Phone Call, WhatsApp, Email (radio, single choice).
- Interests are multi-select checkboxes; "Other" reveals a free-text field.

## Security Requirements (non-negotiable)

- PDO prepared statements everywhere — no string-concatenated SQL.
- `password_hash()` / `password_verify()` for admin auth; never store plain-text passwords.
- CSRF token on every form (public and admin), verified server-side on every POST.
- Escape all output with `htmlspecialchars()` — no raw echo of user input.
- Secure sessions: `cookie_httponly`, `cookie_secure`, `use_strict_mode`, `session_regenerate_id(true)` on login.
- Basic rate limiting / honeypot on the public form to deter spam — no CAPTCHA required.
- No database credentials or secrets committed to the repo; load from environment/config outside web root.
- `display_errors=0` in production; log errors server-side instead.

## Branding / Theme

- The Waterlift Solar logo will be added to the local development environment (expect it under `public/assets/img/` — check there before assuming it's missing).
- Derive the color palette and visual theme from the logo once it's present: primary/accent colors, and typography that reads as a professional solar & water solutions company — clean, trustworthy, not over-designed.
- Public form must look credible on first glance since visitors are strangers scanning a QR code mid-conversation at a booth — large touch targets, minimal scrolling, fast load, no unnecessary animation.
- If the logo hasn't been added yet when a design task comes up, ask rather than guessing a palette.

## Out of Scope — do not add without explicit new instructions

- General CRM functionality, sales pipeline, customer accounts
- Google Ads / Meta Ads integration
- WhatsApp API, email marketing, marketing automation
- General website lead-capture forms unrelated to expos

## Development Workflow

- Build module by module, not all at once: (1) DB + admin auth, (2) Expo CRUD + QR generation, (3) Public form + submission handling, (4) Admin dashboard + stats, (5) Submissions table + detail view, (6) Export (CSV/Excel), (7) Duplicate flagging polish.
- For each module: provide complete working code and state exactly which file(s) it goes in.
- Keep concerns separated: DB connection, auth, public form, form processing, admin pages, QR generation, export — each in its own file under `includes/` or the relevant `public/` subfolder, not bundled together.
- When in doubt about a schema or scope change, flag it rather than silently expanding the system.
