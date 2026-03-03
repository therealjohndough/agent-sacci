# AGENTS.md

Instructions for AI coding agents working on the Sacci Brand Hub project.

## Project Overview

A lightweight PHP 8.2 CMS for the House of Sacci brand portal, deployed to SiteGround hosting in a subfolder (`/sacci_brand_hub`).

## Directory Structure

```text
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

### Branch Policy

- AI-generated branches (`claude/`, `gpt/` prefixes) must be merged or deleted within 7 days
- No branch should remain open longer than 14 days without an active PR
- Stale branches should be deleted with:

  ```bash
  git branch -d branch-name
  git push origin --delete branch-name
  ```

### Release Tagging SOP

1. Update `CHANGELOG.md`: move entries from `[Unreleased]` into a new `[x.y.z] - YYYY-MM-DD` section
2. Commit the changelog update:

   ```bash
   git add CHANGELOG.md
   git commit -m "Release vX.Y.Z"
   ```

3. Tag the release:

   ```bash
   git tag vX.Y.Z
   ```

4. Push the commit and tag:

   ```bash
   git push origin main
   git push origin vX.Y.Z
   ```

5. Create a GitHub release: go to the repository on GitHub, click **Releases → Draft a new release**, select the tag, paste the changelog section as the release notes, and publish.

### Secret Rotation SOP

#### Rotating `DB_PASSWORD`

1. Generate a new password and update it in the database server (SiteGround control panel or MySQL CLI)
2. Open `sacci_brand_hub/.env` on the server and update `DB_PASSWORD=<new-value>`
3. Test the database connection:

   ```bash
   php -r "require 'sacci_brand_hub/config/config.php'; require 'sacci_brand_hub/core/Database.php'; Config\loadEnv('sacci_brand_hub/.env'); Core\Database::init(); echo 'OK';"
   ```

4. Reload the application and verify login and data load correctly
5. Never commit `.env` — credentials live only on the server

#### Rotating SMTP credentials (`MAIL_USERNAME` / `MAIL_PASSWORD`)

1. Update credentials in your SMTP provider's admin panel
2. Open `sacci_brand_hub/.env` on the server and update `MAIL_USERNAME` and `MAIL_PASSWORD`
3. Use `/admin/test-mail` (if implemented) or create a test ticket and confirm the notification email is received
4. Never commit `.env` — credentials live only on the server

### Dependency Update SOP

Run monthly to keep dependencies current and audited:

1. Check for outdated packages:

   ```bash
   cd sacci_brand_hub
   composer outdated
   ```

2. Update all packages within declared version constraints:

   ```bash
   composer update
   ```

3. Run the test suite to verify nothing broke:

   ```bash
   vendor/bin/phpunit
   ```

4. Review the diff in `composer.lock` to confirm only expected packages changed:

   ```bash
   git diff composer.lock
   ```

5. Commit only `composer.lock` (not `vendor/`):

   ```bash
   git add composer.lock
   git commit -m "chore: update composer.lock (monthly dependency update)"
   ```
