# Frontend Documentation

Sacci Brand Hub — frontend layer reference.

This document describes exactly what exists in the codebase as of the initial release. Nothing is inferred or aspirational.

## Build Process

None. There is no build step, no Node.js, no npm, no package.json, no webpack, no Vite, no Tailwind CLI, and no CSS preprocessor of any kind. All frontend files are plain, static files served directly by Apache. Editing a CSS or PHP template file takes effect immediately on the next page load.

## File Inventory

### CSS

One stylesheet exists in the project:

| File | Purpose |
|---|---|
| `sacci_brand_hub/static/brand-hub.css` | Primary and only stylesheet for the entire application |

There are no SCSS, SASS, or LESS files anywhere in the repository.

### JavaScript

None. There are no `.js` files anywhere in the project. No `<script>` tags appear in any view file. No JavaScript is loaded locally or from a CDN.

### Images and Other Static Assets

None committed to the repository. The `sacci_brand_hub/static/` directory contains only `brand-hub.css`. Uploaded brand asset files are stored at runtime in `sacci_brand_hub/storage/` which is excluded from git.

## External Resources Loaded at Runtime

The master layout (`sacci_brand_hub/app/views/layout/main.php`) loads two external resources on every page:

| Resource | Source | Purpose |
|---|---|---|
| Google Fonts — Montserrat | `https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap` | Primary typeface for all UI text |
| `brand-hub.css` | Self-hosted at `/static/brand-hub.css` | All application styles |

No other CDN resources, icon fonts, or third-party UI libraries are referenced anywhere in the views.

## CSS Architecture

`brand-hub.css` is a single flat file with no imports, no nesting, and no preprocessor syntax. It uses CSS custom properties (variables) defined on `:root` for the design token system.

### Design Tokens (CSS Custom Properties)

```css
--color-primary:      #31935f;   /* Sacci green */
--color-accent:       #d4a837;   /* Sacci gold */
--color-bg:           #0d0d0d;   /* Very dark background */
--color-surface:      #f5f0e8;   /* Light text / surface */
--color-card:         #1a1a1a;   /* Dark card background */
--color-muted:        #888880;   /* Muted gray */
--color-danger:       #e74c3c;   /* Error red */
--color-danger-border:#c0392b;   /* Error border */
--border-subtle:      1px solid rgba(212, 168, 55, 0.15);
```

### Component Classes Defined in `brand-hub.css`

| Class | Description |
|---|---|
| `.navbar` | Sticky top navigation bar with gold bottom border |
| `.navbar a` | Navigation links in muted gray; `.active` sets gold color |
| `.container` | Page content wrapper, max-width 1100px, centered |
| `.card` | Dark charcoal content card with subtle gold border |
| `.page-title` | Gold `<h1>` page heading |
| `.section-title` | Green `<h2>` section heading |
| `.card-title` | Gold card heading |
| `.app-link` | Gold anchor link, no underline |
| `.logout-link` | Gold logout anchor (overrides `.navbar a` color) |
| `.form-label` | Block-display form label |
| `.form-input` | Full-width dark input with gold border tint |
| `.form-textarea` | Variant of `.form-input` with `min-height: 120px` and `resize: vertical` |
| `.button-primary` | Gold submit button with dark text |
| `.button-link` | Borderless button styled as a danger-colored text link |
| `.inline-form` | Compact form wrapper with bottom margin |
| `.error-card` | Card variant with red left border and red text |
| `.notice-card` | Card variant with green left border |
| `.meta-text` | Small muted gray text for metadata |
| `.simple-list` | Unstyled `<ul>` with modest left padding |
| `.metric-grid` | CSS Grid auto-fit layout for dashboard metric cards |
| `.metric-card` | Metric card (removes bottom margin for grid context) |
| `.metric-label` | Small uppercase muted label above a metric value |
| `.metric-value` | Large (32px, bold) gold metric number |
| `.section-grid` | CSS Grid auto-fit layout for side-by-side content sections |
| `.section-card` | Section card (removes bottom margin for grid context) |
| `.compact-title` | Removes top margin from a heading inside a card |

### Inline Styles

One instance of an inline style exists in the codebase:

- `sacci_brand_hub/app/views/app/dashboard.php` line 5 — the storage-exposure security warning card uses `style="border-color:#c0392b;background:#2d0a0a;color:#e74c3c;margin-bottom:1rem;"` because it is a one-off alert that does not map to an existing utility class.

## View Templates

All views are plain PHP templates. The rendering pipeline is:

1. `Core\View::render($view, $data)` calls `extract($data)` then buffers the view template
2. The buffered output is passed as `$content` to `app/views/layout/main.php`
3. The layout emits the full HTML document

No Blade, Twig, Smarty, or other template engine is used.

### Template Files

| Template path (relative to `sacci_brand_hub/`) | Route(s) served |
|---|---|
| `app/views/layout/main.php` | All authenticated pages (master layout) |
| `app/views/auth/login.php` | `GET /login` |
| `app/views/app/dashboard.php` | `GET /app` |
| `app/views/app/dashboard/executive.php` | `GET /dashboard/executive` |
| `app/views/app/tickets/index.php` | `GET /tickets` (list) |
| `app/views/app/tickets/show.php` | `GET /tickets?id=` (detail) |
| `app/views/app/assets/index.php` | `GET /assets` |
| `app/views/app/assets/show.php` | `GET /assets?id=` |
| `app/views/app/content/index.php` | `GET /content` |
| `app/views/app/actions/index.php` | `GET /actions` |
| `app/views/app/actions/create.php` | `GET /actions/new`, `GET /actions/edit` |
| `app/views/app/batches/index.php` | `GET /batches` (list) |
| `app/views/app/batches/create.php` | `GET /batches/new`, `GET /batches/edit` |
| `app/views/app/batches/show.php` | `GET /batches?id=` |
| `app/views/app/meetings/index.php` | `GET /meetings` (list) |
| `app/views/app/meetings/create.php` | `GET /meetings/new`, `GET /meetings/edit` |
| `app/views/app/meetings/show.php` | `GET /meetings?id=` |
| `app/views/app/documents/index.php` | `GET /documents` (list) |
| `app/views/app/documents/create.php` | `GET /documents/new`, `GET /documents/edit` |
| `app/views/app/documents/show.php` | `GET /documents?id=` |
| `app/views/app/reports/index.php` | `GET /reports` (list) |
| `app/views/app/reports/create.php` | `GET /reports/new`, `GET /reports/edit` |
| `app/views/app/reports/show.php` | `GET /reports?id=` |
| `app/views/app/strains/index.php` | `GET /strains` (list) |
| `app/views/app/strains/create.php` | `GET /strains/new`, `GET /strains/edit` |
| `app/views/app/strains/show.php` | `GET /strains?id=` |
| `app/views/app/people/index.php` | `GET /people` |
| `app/views/app/departments/index.php` | `GET /departments` |
| `app/views/app/search/index.php` | `GET /search` |
| `app/views/portal/dashboard.php` | `GET /portal` |

### Output Escaping Convention

All user-controlled values rendered in templates are escaped with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. This is a manual convention — there is no auto-escaping engine enforcing it.

### CSRF Token in Forms

Every form that submits a POST request includes:

```php
<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
```

The `$csrf` variable is provided by `BaseController::csrfToken()`, which calls `Core\Csrf::token()`.

## Content Security Policy

The following CSP is set in `index.php` on every request:

```text
default-src 'self';
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com;
img-src 'self' data:;
form-action 'self';
base-uri 'self';
frame-ancestors 'self'
```

`'unsafe-inline'` is permitted for styles because the dashboard security-warning card uses an inline `style` attribute. `script-src` is absent from the policy, which defaults to `default-src 'self'`, blocking all inline scripts and external script sources.

## Known Gaps and Risks

**No JavaScript at all.** There is no client-side interactivity. Form submissions do full page reloads. There is no confirmation dialog before destructive actions (archive, delete). The archive buttons submit POST forms directly without any "are you sure?" prompt.

**Google Fonts loaded from an external CDN.** Every page load makes a DNS lookup and TCP connection to `fonts.googleapis.com` and `fonts.gstatic.com`. This has privacy implications (Google can log visitor IPs) and is a single point of failure for the font. If Google Fonts is unavailable, the UI falls back to `sans-serif`. Self-hosting the Montserrat font files would eliminate both concerns.

**One inline style.** The storage-warning card in `app/views/app/dashboard.php` uses a hardcoded `style` attribute. This is why `'unsafe-inline'` is required in the CSP `style-src` directive. Moving this to a dedicated CSS class (e.g., `.alert-critical`) would allow `'unsafe-inline'` to be removed from the policy, strengthening the CSP.

**No responsive breakpoints.** The stylesheet uses `max-width: 1100px` for the container and CSS Grid `auto-fit` for metric and section grids, which provides some flexibility on narrow viewports. However, the navbar is a horizontal flex row with no mobile hamburger menu or wrapping, so it will overflow on small screens.

**No CSS minification.** The single `brand-hub.css` file is served as-is. There is no build step to minify or fingerprint it. Browsers will cache it by URL, but there is no cache-busting mechanism (e.g., a content hash in the filename), so old versions may be served after updates until the cache expires.

**No JS framework — intentional.** The deployment target is SiteGround shared hosting. The absence of a Node.js build step is a deliberate constraint, not an oversight. Adding any npm-based toolchain would require a local build step before every deploy.

## Recommendations

The following are observations based on what exists, not a roadmap:

1. **Self-host Montserrat.** Download the font files and serve them from `static/fonts/`. Update `brand-hub.css` to use `@font-face`. Remove the Google Fonts `<link>` from `main.php` and tighten the CSP `font-src` to `'self'`.

2. **Add a `.alert-critical` CSS class.** Move the inline styles from the dashboard security warning into `brand-hub.css`. This removes the only inline style in the codebase, allowing `'unsafe-inline'` to be dropped from the `style-src` CSP directive.

3. **Add a `?v=` cache-busting parameter to the stylesheet link.** A simple approach is to append the file's modification timestamp: `appUrl('/static/brand-hub.css?v=' . filemtime(...))`. This ensures updated styles are picked up immediately after deploy without waiting for cache expiry.

4. **Add minimal confirmation behavior for destructive actions.** A `<button onclick="return confirm('Archive this item?')">` is the simplest approach that requires no JS framework and no build step. It would prevent accidental data loss on archive/delete operations.

5. **Add a navbar overflow strategy for narrow viewports.** At minimum, `flex-wrap: wrap` on `.navbar` or a CSS-only disclosure pattern would prevent horizontal overflow on mobile screens.
