# AGENTS.md — Sacci Brand Hub

Quick reference for AI coding agents. See CLAUDE.md for full documentation.

## Stack

- PHP 8.2+ / MySQL 8 / Apache
- No build step, no Node.js, no test suite
- Single dependency: PHPMailer ^6.9 (via Composer)

## Project Structure

```
sacci_brand_hub/           # Document root
├── index.php              # Front controller, all routes registered here
├── config/config.php      # loadEnv() and env() helpers
├── core/                  # Auth, Csrf, Database, Router, View
├── app/controllers/       # Extend BaseController
├── app/models/            # Extend BaseModel
├── app/views/             # Plain PHP templates
├── migrations/            # Sequential PHP scripts (001_, 002_, ...)
└── storage/               # File uploads (not web-accessible)
```

## Setup Commands

```bash
cd sacci_brand_hub
composer install
cp .env.example .env       # Edit DB credentials
php migrations/001_create_tables.php
php migrations/002_seed_data.php
php -S localhost:8000 -t sacci_brand_hub sacci_brand_hub/index.php
```

## Environment Variables

Required in `sacci_brand_hub/.env`:
- `APP_NAME`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

Optional (SMTP): `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM`

## Code Patterns

**Controllers**: Extend `BaseController`, call `$this->requireLogin()`, use `$this->render('view/path', $data)`

**Models**: Extend `BaseModel`, set `protected static string $table`, use `find()`, `findBy()`, `create()`, `update()`

**Views**: Plain PHP, escape with `htmlspecialchars()`, include CSRF: `<input type="hidden" name="_csrf" value="<?= $csrf ?>">`

**Routes**: Register in `index.php` via `$router->add('METHOD', '/path', [Controller::class, 'action'])`

## Security Rules

1. Always use PDO prepared statements
2. Always escape output with `htmlspecialchars()`
3. Validate CSRF on all POST requests
4. Call `$this->requireLogin()` on protected actions
5. Use `password_hash()` / `password_verify()`
6. Serve files via `AssetController@download`, not direct paths

## Adding Features

- **Route**: Add to `index.php` before `$router->dispatch()`
- **Controller**: Create in `app/controllers/`, extend `BaseController`
- **Model**: Create in `app/models/`, extend `BaseModel`, set `$table`
- **View**: Create in `app/views/`
- **Migration**: Add `migrations/00N_description.php`, run manually

## Git Workflow

- Main branch: `master`
- AI branches: `claude/<description>-<session-id>`
- No CI/CD — deploy via FTP/SSH
