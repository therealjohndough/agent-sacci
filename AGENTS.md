# AGENTS.md

Instructions for AI coding agents working on the Sacci Brand Hub project.

## Project Overview

A lightweight PHP 8.2 CMS for the House of Sacci brand portal, deployed to SiteGround hosting in a subfolder (`/sacci_brand_hub`).

## Directory Structure

```
sacci_brand_hub/          # Main application directory
├── app/                  # Controllers, models, views (PSR-4: App\)
├── core/                 # Framework core (PSR-4: Core\)
├── config/               # Configuration loader
├── migrations/           # Database migrations
├── install/              # Web installer
├── .env                  # Environment config (not in git)
├── vendor/               # Composer deps (not in git)
└── storage/              # User uploads (not in git)
```

## Requirements

- PHP >= 8.2
- Composer for dependencies

## Environment Variables

Stored in `sacci_brand_hub/.env` (never committed). Loaded via `Config\loadEnv()`. Access with `Config\env('KEY', $default)`.

Required variables (see installer):
- Database credentials
- Application secrets

## Build & Dependencies

```bash
cd sacci_brand_hub
composer install --no-dev --optimize-autoloader
```

## Routing & Subfolder Support

The app runs in a subfolder (`/sacci_brand_hub`). Key patterns:

- **APP_BASE constant**: Auto-detected from `SCRIPT_NAME`, available globally
- **Router::setBasePath()**: Strips subfolder prefix from URIs
- **BaseController::redirect()**: Use `$this->redirect('/path')` — it prepends `APP_BASE` automatically
- **Never hardcode** absolute paths like `/login`; use the redirect helper

Routes are defined in `sacci_brand_hub/index.php` without the subfolder prefix:
```php
$router->add('GET', '/login', [AuthController::class, 'login']);
```

## .htaccess

```apache
RewriteBase /sacci_brand_hub/
```
All requests route through `index.php`.

## Deployment

See `DEPLOY.md` for full SSH/Git deployment to SiteGround.

Quick deploy:
```bash
git push origin main          # GitHub (source of truth)
git push siteground main      # SiteGround production
```

Remote: `siteground` → `ssh://sacci-sg/home/u2520-3v1nc5i4btry/repos/sacci_brand_hub.git`

## Files Excluded from Git

Per `.gitignore`:
- `sacci_brand_hub/.env`
- `sacci_brand_hub/vendor/`
- `sacci_brand_hub/storage/`
- `*.zip`

These must exist on the server but are not version-controlled.

## Code Style

- PSR-4 autoloading
- Controllers extend `BaseController`
- Use `$this->redirect()` for redirects (subfolder-aware)
- Use `$this->render()` for views
