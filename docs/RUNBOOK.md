# Production Runbook — Sacci Brand Hub

This runbook is for on-call engineers and developers responding to incidents on the Sacci Brand Hub production environment hosted on SiteGround shared hosting.

---

## Incident Response Steps

Every incident follows this sequence:

1. **Detect** — An alert fires, a user reports an issue, or monitoring surfaces an anomaly.
2. **Triage** — Determine severity. Is the site fully down? Partially degraded? Single user affected?
3. **Mitigate** — Apply the fastest fix that stops the bleeding: rollback, restart, config correction, or traffic diversion.
4. **Communicate** — Notify stakeholders (see Contacts section) with a brief status update: what is broken, what was done, current status.
5. **Post-mortem** — After resolution, write a short timeline of events, identify root cause, and add a follow-up task to prevent recurrence.

Severity levels:

| Severity | Definition | Target response |
|---|---|---|
| P1 | Site fully down / no users can log in | Immediate — drop everything |
| P2 | Core feature broken (uploads, email, admin panel) | Within 1 hour |
| P3 | Degraded experience / cosmetic issue | Next business day |

---

## Common Scenarios and Recovery Steps

### 1. Site is down / 500 errors

**Symptoms:**
- All pages return HTTP 500 or a blank white page
- Browser shows no content or a generic server error
- `curl -sI https://sacci.space/sacci_brand_hub/` returns `500` or no response

**Immediate steps:**
1. Check if the site was recently deployed — if yes, roll back immediately (see DEPLOY.md → Rollback Procedure)
2. SSH in and check the PHP/Apache error log:
   ```bash
   ssh sacci-sg "tail -n 100 ~/logs/error_log"
   ```
3. Check that `.env` exists and is readable:
   ```bash
   ssh sacci-sg "ls -la ~/public_html/sacci_brand_hub/.env"
   ```
4. Check that `vendor/` is present and autoloader exists:
   ```bash
   ssh sacci-sg "ls ~/public_html/sacci_brand_hub/vendor/autoload.php"
   ```

**Root cause investigation:**
- Parse error in PHP file → look for `PHP Parse error` in error log; check recently changed files
- Missing `vendor/` → run `composer install --no-dev --optimize-autoloader` on server
- Missing or corrupt `.env` → restore from backup or re-create from known values

**Fix:**
- Parse error: revert the offending file via git rollback (`git push siteground <good-commit>:refs/heads/main --force`) or edit directly over SSH
- Missing vendor: `ssh sacci-sg "cd ~/public_html/sacci_brand_hub && composer install --no-dev --optimize-autoloader"`
- Missing .env: `ssh sacci-sg "nano ~/public_html/sacci_brand_hub/.env"` and enter correct values

---

### 2. Database connection failure

**Symptoms:**
- Site loads but shows a database error or exception trace
- Error log contains `SQLSTATE[HY000] [2002] Connection refused` or similar PDO exception
- Login page loads but login always fails

**Immediate steps:**
1. Check error log for PDO exception details:
   ```bash
   ssh sacci-sg "grep -i 'PDO\|SQLSTATE\|database' ~/logs/error_log | tail -30"
   ```
2. Verify `.env` DB credentials are correct:
   ```bash
   ssh sacci-sg "grep DB_ ~/public_html/sacci_brand_hub/.env"
   ```
3. Test DB connectivity from the server:
   ```bash
   ssh sacci-sg "mysql -h \$DB_HOST -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE -e 'SELECT 1'"
   ```
4. Check SiteGround Site Tools → MySQL to confirm the database and user exist and the user has full privileges on the database

**Root cause investigation:**
- Wrong credentials in `.env` → update `.env` to match values in SiteGround Site Tools → MySQL
- DB server temporarily down → check SiteGround status page; wait and retry
- DB user privileges revoked → re-grant privileges via SiteGround Site Tools → MySQL → Manage Users

**Fix:**
- Update `.env` with correct `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`
- Restore DB from backup if data corruption is suspected (see Contacts section for backup location)

---

### 3. SMTP / email not sending

**Symptoms:**
- `GET /admin/test-mail` returns `{"status":"error","message":"..."}` with an SMTP error
- Users report not receiving password reset or notification emails
- Error log contains PHPMailer exceptions

**Immediate steps:**
1. Hit the test-mail endpoint as an admin:
   ```bash
   curl -sI https://sacci.space/sacci_brand_hub/admin/test-mail
   # Must be authenticated; use browser dev tools or a session cookie
   ```
2. Check the SMTP credentials in `.env`:
   ```bash
   ssh sacci-sg "grep MAIL_ ~/public_html/sacci_brand_hub/.env"
   ```
3. Confirm the SMTP port is not blocked by SiteGround firewall (port 587 is typical; port 465 for SSL)
4. Verify the SMTP provider account is active and not suspended

**Root cause investigation:**
- Wrong `MAIL_HOST`, `MAIL_USERNAME`, or `MAIL_PASSWORD` in `.env`
- SMTP provider rate limit or account suspension
- SiteGround firewall blocking outbound SMTP (common on shared hosting) — may require using SiteGround's own SMTP relay

**Fix:**
- Correct credentials in `.env` and retest
- If outbound SMTP is blocked, switch `MAIL_HOST` to `localhost` and use SiteGround's local mail server (check SiteGround support for local SMTP settings)
- As a temporary workaround, use a transactional email service that allows API-based sending (e.g., Mailgun, SendGrid) if raw SMTP is blocked

---

### 4. Admin locked out of portal

**Symptoms:**
- Super admin cannot log in — credentials rejected
- Admin account was deleted or role was removed via DB edit

**Immediate steps:**
1. SSH in and open the MySQL client:
   ```bash
   ssh sacci-sg
   mysql -h [FILL IN DB_HOST] -u [FILL IN DB_USERNAME] -p[FILL IN DB_PASSWORD] [FILL IN DB_DATABASE]
   ```
2. Find the admin user:
   ```sql
   SELECT id, email, created_at FROM users ORDER BY id LIMIT 10;
   ```
3. Reset the password to a known value (replace `<NEW_HASH>` with output of `php -r "echo password_hash('TemporaryPass123!', PASSWORD_DEFAULT);"` run locally):
   ```sql
   UPDATE users SET password_hash = '<NEW_HASH>' WHERE email = '[FILL IN ADMIN EMAIL]';
   ```
4. Confirm the user has the `super_admin` role:
   ```sql
   SELECT u.email, r.name
   FROM users u
   JOIN user_roles ur ON ur.user_id = u.id
   JOIN roles r ON r.id = ur.role_id
   WHERE u.email = '[FILL IN ADMIN EMAIL]';
   ```
5. If role is missing, re-assign it:
   ```sql
   INSERT INTO user_roles (user_id, role_id)
   SELECT u.id, r.id FROM users u, roles r
   WHERE u.email = '[FILL IN ADMIN EMAIL]' AND r.name = 'super_admin';
   ```
6. Log in with the temporary password and change it immediately in the UI

**Root cause investigation:**
- Accidental password change
- DB migration or seed data accidentally deleted the user or role
- Role assignment table (`user_roles`) was truncated

---

### 5. Uploads not working (storage/ issues)

**Symptoms:**
- File upload returns an error or silently fails
- Assets exist in DB but files are missing from disk
- Download endpoint returns 404 for files that should exist

**Immediate steps:**
1. Check that the `storage/` directory exists and is writable:
   ```bash
   ssh sacci-sg "ls -la ~/public_html/sacci_brand_hub/storage/"
   ssh sacci-sg "stat ~/public_html/sacci_brand_hub/storage/"
   ```
2. Check disk quota on the shared hosting account via SiteGround Site Tools → Statistics
3. Check file permissions:
   ```bash
   ssh sacci-sg "ls -la ~/public_html/sacci_brand_hub/storage/"
   ```

**Root cause investigation:**
- `storage/` directory missing after a deploy that accidentally wiped it → recreate and restore from backup
- Directory not writable by web server user → fix permissions
- Disk quota exceeded → delete old files or upgrade hosting plan

**Fix:**
- Create missing directory: `ssh sacci-sg "mkdir -p ~/public_html/sacci_brand_hub/storage && chmod 755 ~/public_html/sacci_brand_hub/storage"`
- Fix permissions: `ssh sacci-sg "chmod -R 755 ~/public_html/sacci_brand_hub/storage"`
- Restore files from backup (see Contacts section for backup location)
- If `storage/` is web-accessible (dashboard shows a security warning), move it above `public_html/` or add `.htaccess` with `Deny from all`

---

### 6. High load / slow responses

**Symptoms:**
- Pages take >5 seconds to load
- SiteGround Site Tools shows high CPU or memory usage
- Error log shows slow query warnings or timeout errors

**Immediate steps:**
1. Check SiteGround Site Tools → Statistics for current resource usage
2. Check for slow DB queries in the error log:
   ```bash
   ssh sacci-sg "grep -i 'slow\|timeout\|lock' ~/logs/error_log | tail -30"
   ```
3. Identify the most-hit routes by checking access log:
   ```bash
   ssh sacci-sg "tail -n 500 ~/logs/access_log | awk '{print \$7}' | sort | uniq -c | sort -rn | head -20"
   ```
4. Check if a bot or crawler is hammering the site:
   ```bash
   ssh sacci-sg "tail -n 500 ~/logs/access_log | awk '{print \$1}' | sort | uniq -c | sort -rn | head -10"
   ```

**Root cause investigation:**
- Missing DB indexes on frequently queried columns → add indexes via migration
- Large result sets being loaded without pagination → check model queries
- Bot traffic hitting login endpoint (no rate limiting on GET routes yet)
- SiteGround shared hosting CPU limits being hit by a legitimate traffic spike

**Fix:**
- Block an abusive IP via SiteGround Site Tools → Security → IP Blocker or via `.htaccess`:
  ```apache
  Deny from <ABUSIVE_IP>
  ```
- Add `LIMIT` clauses to expensive queries in models
- Contact SiteGround support to temporarily raise PHP memory or execution time limits if needed
- Consider adding opcode caching (OPcache) — check if enabled: `ssh sacci-sg "php -i | grep opcache.enable"`

---

## Monitoring Checklist

When something goes wrong, check these in order:

1. **Is the site responding?** `curl -sI https://sacci.space/sacci_brand_hub/` — expect `302` redirect to `/login`
2. **Recent deploys?** `git log --oneline -5` locally — if a deploy happened in the last hour, rollback first, investigate second
3. **Error log** — `ssh sacci-sg "tail -n 100 ~/logs/error_log"` — look for PHP errors, PDO exceptions, PHPMailer failures
4. **Access log** — `ssh sacci-sg "tail -n 100 ~/logs/access_log"` — look for bursts of 4xx/5xx responses or unusual traffic patterns
5. **Disk space** — SiteGround Site Tools → Statistics → Disk Usage
6. **DB connectivity** — `mysql -h ... -e 'SELECT 1'` from SSH
7. **`.env` present and correct** — `ssh sacci-sg "ls -la ~/public_html/sacci_brand_hub/.env"`
8. **`vendor/` present** — `ssh sacci-sg "ls ~/public_html/sacci_brand_hub/vendor/autoload.php"`
9. **`storage/` writable** — `ssh sacci-sg "ls -la ~/public_html/sacci_brand_hub/storage/"`
10. **SiteGround status page** — check [https://www.siteground.com/status](https://www.siteground.com/status) for platform-wide outages

---

## Contacts and Resources

| Resource | Details |
|---|---|
| Hosting panel (SiteGround Site Tools) | [FILL IN] — URL + login credentials location |
| SSH access | `ssh sacci-sg` — key configured at `~/.ssh/config` (see DEPLOY.md) |
| DB backup location | [FILL IN] — SiteGround automated backup location or manual backup path |
| Error log path on server | `~/logs/error_log` (verify path via SiteGround Site Tools → Statistics) |
| Access log path on server | `~/logs/access_log` |
| `.env` location on server | `~/public_html/sacci_brand_hub/.env` |
| Primary developer | [FILL IN] — Name, email, phone/Signal |
| Secondary developer / escalation | [FILL IN] — Name, email, phone/Signal |
| SiteGround support | Live chat via Site Tools, or [https://www.siteground.com/support](https://www.siteground.com/support) |
| Domain registrar | [FILL IN] — Registrar name + login URL |
| Git repository (source of truth) | [FILL IN] — GitHub repo URL |
