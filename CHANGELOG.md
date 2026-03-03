# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

## [1.0.0] - 2026-03-03

### Added

#### Framework Core
- Custom PHP 8.2 MVC framework with no external framework dependency
- PSR-4 autoloading via `spl_autoload_register` covering `App\`, `Core\`, and `Config\` namespaces
- `Core\Router` — simple method+path dispatcher; strips subfolder prefix automatically
- `Core\View` — output-buffered PHP template renderer that wraps views in a master layout
- `Core\Database` — singleton PDO wrapper targeting MySQL 8, `utf8mb4` charset, `FETCH_ASSOC` mode, `ERRMODE_EXCEPTION`
- `Core\Auth` — static session-based authentication with RBAC permission lookup
- `Core\Csrf` — per-session CSRF token generation using `bin2hex(random_bytes(32))` with timing-safe `hash_equals()` validation
- `Core\RateLimiter` — database-backed rate limiter for per-IP, per-action request throttling
- `Core\AirtableClient` — HTTP client for reading records from Airtable via cURL with pagination support
- `Config\loadEnv()` and `Config\env()` helpers loading environment variables from `.env`
- `APP_BASE` constant auto-detected from `SCRIPT_NAME` for subfolder-install support
- `Config\appUrl()` and `Config\appBase()` helpers for subfolder-aware URL generation
- `bin/install.php` CLI migration runner

#### Controllers (11 total)
- `AuthController` — GET+POST `/login`, GET `/logout`
- `DashboardController` — GET `/app` (internal staff dashboard with ticket metrics)
- `ExecutiveDashboardController` — GET `/dashboard/executive` (aggregated view of meetings, actions, reports, documents)
- `TicketController` — GET `/tickets`, GET `/tickets?id=` (ticket list and detail)
- `AssetController` — GET `/assets`, GET `/assets/download` (asset library and controlled file download)
- `PortalController` — GET `/portal` (retail partner asset portal)
- `ContentController` — GET `/content` (CMS content block viewer, admin-only)
- `ActionController` — full CRUD for action items: GET/POST `/actions`, `/actions/new`, `/actions/edit`, `/actions/archive`, `/actions/update`; POST `/actions/sync-airtable` for Airtable import
- `BatchController` — full CRUD for production batches: GET/POST `/batches`, `/batches/new`, `/batches/edit`, `/batches/archive`, `/batches/update`
- `MeetingController` — full CRUD for meeting records: GET/POST `/meetings`, `/meetings/new`, `/meetings/edit`, `/meetings/archive`, `/meetings/update`
- `DocumentController` — full CRUD for internal documents: GET/POST `/documents`, `/documents/new`, `/documents/edit`, `/documents/archive`, `/documents/update`
- `ReportController` — full CRUD for business reports and report entries: GET/POST `/reports`, `/reports/new`, `/reports/edit`, `/reports/archive`, `/reports/update`; plus GET/POST `/reports/entries/edit`, `/reports/entries`, `/reports/entries/delete`, `/reports/entries/update`
- `StrainController` — full CRUD for cannabis strains: GET/POST `/strains`, `/strains/new`, `/strains/edit`, `/strains/archive`, `/strains/update`
- `DepartmentController` — GET `/departments` (department directory)
- `PeopleController` — GET `/people` (team directory)
- `SearchController` — GET `/search` (cross-module full-text search across meetings, actions, reports, documents)
- `BaseController` — shared base: `requireLogin()`, `render()`, `redirect()`, `csrfToken()`

#### Models (18 total)
- `BaseModel` — active-record base with `find()`, `findBy()`, `create()`, `update()` using PDO prepared statements
- `User` — user accounts with `findByEmail()`, `verifyPassword()` (uses `password_verify()`)
- `Role` — named roles with `findByUserId()`
- `Permission` — named permissions with `findByRoleId()`
- `Organization` — retail partner organizations
- `Ticket` — support/creative request tickets with status, priority, request\_type, and assignment tracking
- `TicketComment` — per-ticket comments
- `TicketActivity` — per-ticket activity log
- `Asset` — brand file asset metadata with visibility levels (`public`, `internal`, `org`)
- `ContentBlock` — slug-keyed CMS sections for editable brand content
- `Setting` — key-value application settings
- `AuditLog` — user action audit trail
- `Department` — internal department records with `findAllOrdered()`
- `ActionItem` — cross-module action items with `findAllWithRelations()`, `findRecentOpen()`, `searchByTerm()`, `syncFromAirtableRecord()`
- `Meeting` — meeting records with decisions and attendees; `findRecent()`, `searchByTerm()`
- `Document` — internal SOPs, policies, playbooks; `findRecentActive()`, `searchByTerm()`
- `Report` — structured recurring business reports with entries; `findRecentPublished()`, `searchByTerm()`
- `Batch` — cannabis production batch records with THC/CBD percentages; `findAllWithRelations()`, `findWithRelations()`
- `Strain` — cannabis strain catalog with `findAllWithCounts()`, `findWithCounts()`, `findAllOrdered()`

#### Database Schema (29+ tables across 16 migrations)
- `001_create_tables.php` — initial 13 tables: `users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `organizations`, `tickets`, `ticket_comments`, `ticket_activities`, `assets`, `content_blocks`, `settings`, `audit_logs`
- `002_seed_data.php` — seeds roles (super\_admin, admin, staff, retailer\_manager, retailer\_user), permissions (user.manage, content.manage, tickets.manage, assets.manage, organizations.manage), and sample data
- `003_add_departments_and_people_foundation.php` — `departments`, `user_department_assignments` tables; adds `job_title`, `profile_summary`, `is_active` columns to `users`
- `004_seed_departments_and_team_directory.php` — seeds department records and team directory data
- `005_add_meetings_and_decisions.php` — `meetings`, `meeting_attendees`, `meeting_decisions` tables
- `006_seed_sample_meetings.php` — seeds sample meeting records
- `007_add_action_items.php` — `action_items` table with cross-module source linking (meeting/report/document/ticket/manual)
- `008_seed_sample_action_items.php` — seeds sample action items
- `009_add_documents.php` — `documents` table for SOPs, policies, and reference material with versioning
- `010_seed_sample_documents.php` — seeds sample documents
- `011_add_reports.php` — `reports` and `report_entries` tables for structured business reporting
- `012_seed_sample_reports.php` — seeds sample reports
- `013_add_airtable_record_ids_to_action_items.php` — adds `airtable_record_id` column to `action_items` for idempotent Airtable sync
- `014_add_product_and_distribution_core.php` — `strains`, `batches`, `coas`, `products`, `sales_channels`, `product_channel_listings` tables (hub-and-spoke product model)
- `015_seed_sales_channels.php` — seeds sales channel records
- `016_rate_limit_log.php` — `rate_limit_log` table used by `Core\RateLimiter`

#### RBAC (Role-Based Access Control)
- Five built-in roles: `super_admin`, `admin`, `staff`, `retailer_manager`, `retailer_user`
- Five named permissions: `user.manage`, `content.manage`, `tickets.manage`, `assets.manage`, `organizations.manage`
- Many-to-many pivot tables `user_roles` and `role_permissions`
- Dynamic permission resolution at request time (no hard-coded role checks in controllers)

#### Asset Management
- Asset library with per-asset visibility control (`public`, `internal`, `org`)
- Controlled download endpoint — files served through `AssetController@download`, never directly from storage URL
- Files stored in `storage/` directory (outside or denied within web root via `.htaccess`)
- `storage/.htaccess` denies direct HTTP access to uploaded files

#### Web Installer (`install/index.php`)
- Browser-based setup wizard gated to `local` and `development` environments only (HTTP 403 in production)
- Writes `.env` file with DB credentials
- Executes initial migration to create all tables
- Creates the first `super_admin` user
- Environment gating enforced in `index.php` before autoloader runs

#### Airtable Integration
- `Core\AirtableClient` reads records from any Airtable table with automatic offset-based pagination
- `ActionController@syncFromAirtable` — POST `/actions/sync-airtable` imports action items from a configured Airtable table/view, using `airtable_record_id` for idempotency
- Configured via `AIRTABLE_TOKEN`, `AIRTABLE_BASE_ID`, `AIRTABLE_ACTIONS_TABLE`, `AIRTABLE_ACTIONS_VIEW` env vars

#### Views (30 PHP templates)
- Master layout (`layout/main.php`) with sticky navigation bar linking all 13 module areas
- Auth: `auth/login.php`
- App: `app/dashboard.php`, `app/dashboard/executive.php`
- Tickets: `app/tickets/index.php`, `app/tickets/show.php`
- Assets: `app/assets/index.php`, `app/assets/show.php`
- Content: `app/content/index.php`
- Actions: `app/actions/index.php`, `app/actions/create.php`
- Batches: `app/batches/index.php`, `app/batches/create.php`, `app/batches/show.php`
- Meetings: `app/meetings/index.php`, `app/meetings/create.php`, `app/meetings/show.php`
- Documents: `app/documents/index.php`, `app/documents/create.php`, `app/documents/show.php`
- Reports: `app/reports/index.php`, `app/reports/create.php`, `app/reports/show.php`
- Strains: `app/strains/index.php`, `app/strains/create.php`, `app/strains/show.php`
- People: `app/people/index.php`
- Departments: `app/departments/index.php`
- Search: `app/search/index.php`
- Portal: `portal/dashboard.php`

#### PHPUnit Test Suite
- `tests/Unit/Auth/AuthControllerTest.php`
- `tests/Unit/Core/RouterTest.php`
- `tests/Unit/Models/BaseModelTest.php`
- `tests/Unit/Security/CsrfTest.php` (12 test cases covering token generation, idempotency, and validation edge cases)
- `tests/bootstrap.php` — CLI-safe test bootstrap initializing `$_SESSION` as a plain array

#### External Dependencies
- `phpmailer/phpmailer ^6.9` — SMTP email (declared in `composer.json`; mail-sending routes not yet implemented)
- `phpunit/phpunit ^11` (dev) — unit test runner

#### Deployment & Configuration
- `.htaccess` front-controller rewrite to `index.php`
- `RewriteBase /sacci_brand_hub/` for subfolder deploy
- Apache hardening headers sent on every request: `Referrer-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Content-Security-Policy`
- `migrate.php` CLI migration entry point
- Git remotes: `origin` (GitHub) and `siteground` (SiteGround production)
- `.gitignore` excludes `.env`, `vendor/`, `storage/`, and `*.zip`

### Security

- **CSRF protection** — Per-session token generated with `bin2hex(random_bytes(32))`; validated on all POST requests using `hash_equals()` (timing-safe). Token stored in `$_SESSION['_csrf']`.
- **PDO prepared statements** — All database queries use `PDO::prepare()` and bound parameters. `PDO::ATTR_EMULATE_PREPARES` is set to `false` to enforce native prepared statements. No dynamic SQL string interpolation in any model or controller.
- **Password hashing** — Passwords hashed with `password_hash($password, PASSWORD_DEFAULT)` (bcrypt). Verified with `password_verify()`. No plaintext passwords stored.
- **Session regeneration on logout** — `Core\Auth::logout()` calls `session_regenerate_id(true)` to invalidate the old session ID.
- **Rate limiting on login** — `Core\RateLimiter` enforces a maximum of 5 login attempts per IP per 15-minute window using the `rate_limit_log` table. Attempts are counted before credential verification. Rate limit is cleared on successful login.
- **Install endpoint gating** — `/install/` returns HTTP 403 in any environment other than `local` or `development`. The check runs before the autoloader, making it impossible to bypass via routing.
- **Output escaping** — All user-controlled data rendered in views is escaped with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- **Controlled file download** — Asset files are served via `AssetController@download` only. The `storage/` path is never exposed as a public URL; an `.htaccess` in `storage/` denies all direct access.
- **Storage exposure detection** — On each request, `index.php` checks whether `storage/` is inside `DOCUMENT_ROOT` and logs a `CRITICAL SECURITY WARNING` if so, and surfaces a visible dashboard alert for admin users.
- **HTTP security headers** — Baseline headers set on every response: `Referrer-Policy: strict-origin-when-cross-origin`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, and a `Content-Security-Policy` restricting sources to `'self'` plus Google Fonts.
- **RBAC** — Permission checks performed dynamically by querying `role_permissions` at runtime. Controllers call `Core\Auth::hasPermission()` explicitly; no role checks are hard-coded.
- **`.env` excluded from version control** — `.env` is listed in `.gitignore`. Credentials are never committed.

[Unreleased]: https://github.com/house-of-sacci/sacci-site/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/house-of-sacci/sacci-site/releases/tag/v1.0.0
