# Sacci Brand Hub Development Overview

## Repo Structure

The project is organized into a lightweight MVC structure compatible with PHP 8.2 and MySQL 8 on shared hosting.  Top‑level directories separate configuration, core services, controllers, models, views, migrations and an install wizard.  The `storage/` directory is outside the web root and protected with an `.htaccess` file.

```
sacci_brand_hub/
├── composer.json          # Composer definition (autoload & PHPMailer dependency)
├── .env.example           # Example environment variables
├── .htaccess              # Simple front‑controller rewrite rules
├── index.php              # Front controller and router
├── config/
│   └── config.php         # Env loader and helper
├── core/
│   ├── Auth.php           # Authentication & RBAC helper
│   ├── Csrf.php           # CSRF token handling
│   ├── Database.php       # PDO wrapper
│   ├── Router.php         # Simple router
│   └── View.php           # View renderer
├── app/
│   ├── controllers/       # Controllers for each endpoint
│   │   ├── AssetController.php
│   │   ├── AuthController.php
│   │   ├── BaseController.php
│   │   ├── ContentController.php
│   │   ├── DashboardController.php
│   │   ├── PortalController.php
│   │   └── TicketController.php
│   ├── models/            # Active‑record style models
│   │   ├── Asset.php
│   │   ├── AuditLog.php
│   │   ├── BaseModel.php
│   │   ├── ContentBlock.php
│   │   ├── Organization.php
│   │   ├── Permission.php
│   │   ├── Role.php
│   │   ├── Setting.php
│   │   ├── Ticket.php
│   │   ├── TicketActivity.php
│   │   ├── TicketComment.php
│   │   └── User.php
│   └── views/             # PHP templates
│       ├── layout/main.php
│       ├── auth/login.php
│       ├── app/dashboard.php
│       ├── app/tickets/index.php
│       ├── app/tickets/show.php
│       ├── app/assets/index.php
│       ├── app/assets/show.php
│       ├── app/content/index.php
│       └── portal/dashboard.php
├── migrations/
│   ├── 001_create_tables.php  # Creates all tables
│   └── 002_seed_data.php      # Seeds roles, permissions, sample users, assets, tickets & content
├── install/
│   └── index.php             # Web‑based installer
└── storage/
    └── .htaccess             # Deny direct access to uploaded files
```

## Database Schema

The CMS uses a normalized relational schema built around users, roles, permissions and tickets.  Below is a summary of the key tables and columns.

| Table               | Key Columns & Purpose                                   |
|---------------------|---------------------------------------------------------|
| `users`             | `id`, `name`, `email` (unique), `password_hash`, `organization_id`, timestamps |
| `roles`             | `id`, `name` (unique), `description`                    |
| `permissions`       | `id`, `name` (unique), `description`                    |
| `user_roles`        | `user_id`, `role_id` (many‑to‑many pivot)               |
| `role_permissions`  | `role_id`, `permission_id` (many‑to‑many pivot)          |
| `organizations`     | `id`, `name`, contact details; groups retailer users     |
| `tickets`           | `id`, `title`, `description`, `request_type`, `priority`, `due_date`, `requester_id`, `organization_id`, `assigned_to`, `status`, timestamps |
| `ticket_comments`   | `id`, `ticket_id`, `user_id`, `body`, `created_at`      |
| `ticket_activities` | `id`, `ticket_id`, `description`, `created_at`          |
| `assets`            | `id`, `name`, `description`, `category`, `product`, `weight_platform`, `file_type`, `filepath`, `visibility` (`public`/`internal`/`org`), `org_id`, `uploaded_by`, timestamps |
| `content_blocks`    | `id`, `slug` (unique), `title`, `content`, timestamps   |
| `settings`          | `id`, `key` (unique), `value`                           |
| `audit_logs`        | `id`, `user_id`, `action`, `created_at`                 |

## Route Map

Routes are mapped in `index.php` using the `Router` class.  GET and POST methods are registered separately.  Query parameters (e.g., `?id=1`) are handled within the controller.

| Method | Path             | Controller & Action                       | Description (concise) |
|--------|------------------|-------------------------------------------|-----------------------|
| GET    | `/login`         | `AuthController@login`                    | Show login form       |
| POST   | `/login`         | `AuthController@login`                    | Process login         |
| GET    | `/logout`        | `AuthController@logout`                   | End session           |
| GET    | `/app`           | `DashboardController@index`               | Internal dashboard    |
| GET    | `/tickets`       | `TicketController@index`                  | List/overview tickets |
| GET    | `/assets`        | `AssetController@index`                   | List assets           |
| GET    | `/assets/download` | `AssetController@download`             | Safe file download    |
| GET    | `/portal`        | `PortalController@index`                  | Retail partner portal |
| GET    | `/content`       | `ContentController@index`                 | List content blocks   |

## Permission Matrix (Roles → Allowed Actions)

The system uses RBAC with explicit permissions stored in `permissions` and assigned to roles via `role_permissions`.  Below is a high‑level view of which roles should be granted which capabilities.  “✓” indicates that the role should be granted the permission.

| Role                | user.manage | content.manage | tickets.manage | assets.manage | organizations.manage |
|---------------------|------------:|---------------:|---------------:|-------------:|---------------------:|
| Super Admin         | ✓           | ✓              | ✓              | ✓            | ✓                    |
| Admin (Marketing)   |             | ✓              | ✓              | ✓            | ✓                    |
| Staff (Marketing)   |             |                | ✓              | ✓            |                      |
| Retailer Manager    |             |                |                |              | (self)               |
| Retailer User       |             |                |                |              |                      |

Additional implied capabilities:

- **Portal access**: retailer roles (manager/user) can view assets with visibility `public` or belonging to their organization and submit requests via the request form.
- **Tickets**: staff can create, update and comment on tickets; admin and super admin can triage, assign and close tickets.
- **Content editing**: restricted to admin and super admin roles; content blocks correspond to the static sections of the brand system.

## Migration Mapping (Static HTML → CMS)

The static brand system page is decomposed into editable `content_blocks`.  Each section of the original HTML becomes a record identified by a `slug`.  The “content” field stores rich text or HTML for flexible rendering.

| Static Section       | CMS Slug               | Content Description |
|----------------------|------------------------|--------------------|
| Design Tokens        | `design-tokens`        | Colors, typography and spacing tokens for UI theming |
| Brand System         | `brand-system`         | Brand identity, voice, typography rules and do/don’t guidelines |
| Asset Structure      | `asset-structure`      | Folder hierarchy, rule boxes and COA requirement note |
| Naming Rules         | `naming-rules`         | File naming formula and example table |
| Sell Sheets          | `sell-sheets`          | Budtender sell sheets for Airmail, Trop Cherry, Hash and Vape |
| Agent Instructions   | `agent-instructions`   | AI system prompt, operational rules and next steps |
| Retail Partner Templates | `retail-partner-templates` | Welcome brief, training checklist and drop announcement templates |

During installation the seeding script inserts a basic design‑tokens block.  The remaining content should be migrated by manually pasting the corresponding HTML from the static page into the `content` column for each slug.

## Risks & Mitigations

| Risk                    | Mitigation strategy |
|-------------------------|---------------------|
| **SQL injection**       | All database operations use prepared statements via PDO; no dynamic query concatenation. |
| **CSRF attacks**        | CSRF tokens are generated per session and validated on all forms. |
| **Weak authentication** | Passwords hashed with `password_hash()`; session ID regenerated on login; rate limiting should be added for brute‑force protection. |
| **Role misconfiguration** | Permissions are stored in a table rather than hard‑coded; an admin UI should let you review role assignments; auditing of permission changes is logged. |
| **File upload abuse**   | Validate MIME types and file extensions on upload; store files outside the web root in `storage/`; serve via a controlled download endpoint that checks permissions. |
| **Shared hosting constraints** | The app avoids heavy frameworks and Node builds; uses vanilla PHP and minimal dependencies; long‑running tasks should be offloaded (e.g., via cron) to stay within memory/CPU limits. |
| **Migration errors**    | Provide clear mapping (above) and seed scripts; run migrations via CLI before serving the app; backup existing data before upgrades. |
| **Email deliverability** | PHPMailer uses SMTP credentials from `.env`; ensure TLS ports are open on SiteGround; provide fallback to `mail()` if needed. |

## SiteGround Deployment Steps

1. **Prepare environment**: Ensure your SiteGround plan supports PHP 8.2+, MySQL 8 and has `pdo_mysql` enabled.  Create a new MySQL database and user via SiteGround’s control panel.
2. **Upload files**: Copy the contents of `sacci_brand_hub/` to your site’s document root (e.g., `public_html/brandhub`).  For security, place the `storage/` directory outside of the public directory if SiteGround allows custom directory structures.
3. **Set permissions**: Ensure the web server can write to the `storage/` directory and the root directory to create the `.env` file during installation.  You can typically set directories to `755` and files to `644`.
4. **Run the installer**: Navigate to `https://yourdomain.com/brandhub/install/`.  Enter your database credentials and the email/password for the first Super Admin user.  The installer writes the `.env` file, runs the migrations and seeds initial data.  After success, delete the `install/` folder as instructed.
5. **Install dependencies**: From a terminal (SSH or local), run `composer install` in the project root to fetch PHPMailer.  If you cannot run Composer on SiteGround, you may vendor PHPMailer locally and upload the `vendor/` folder.
6. **Configure mail**: Edit `.env` to add your SMTP host, port and credentials.  You can test by creating a ticket and ensuring notification emails send.
7. **Set up cron (optional)**: Configure a cron job to run maintenance scripts, such as pruning old sessions or sending SLA reminders.
8. **Go live**: Point DNS or configure a subdomain to the project directory.  Use HTTPS and ensure the `.htaccess` file is active to route all requests through `index.php`.

## Minimal UI Specification

The front‑end adheres to the House of Sacci dark theme with green and gold accents.  Components are built with plain HTML and minimal inline CSS so they can be themed via the design tokens.

* **Navigation bar** – sticky at the top; links for Dashboard, Tickets, Assets and Portal; active link highlighted in gold.
* **Cards** – used to group tickets, assets and content; dark charcoal background with subtle border; gold border accent on hover.
* **Tables** – simple, two‑column listings for settings and checklists; avoid long paragraphs in cells.
* **Forms** – basic inputs styled with dark backgrounds and gold submit buttons; all forms include a hidden CSRF field.
* **Modal/dialog (future)** – should be centered with dark backdrop and follow the same token palette; use for confirmations and ticket updates.

UI components deliberately avoid heavy JavaScript frameworks; behaviour like tab switching or Kanban drag‑and‑drop can be introduced progressively.

---

This document covers the repository structure, schema, routes, permissions, migration mapping, risks, deployment instructions and UI guidelines for the Sacci Brand Hub CMS.  The accompanying ZIP file contains the full source tree and starter code ready for customization and deployment.