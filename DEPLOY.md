# Sacci Brand Hub — SSH & Git Deployment to SiteGround

## Connection Details

| What | Value |
|---|---|
| SSH hostname | `ssh.sacci.space` |
| SSH username | `u2520-3v1nc5i4btry` |
| SSH port | `18765` |
| Server deploy path | `/home/u2520-3v1nc5i4btry/public_html/sacci_brand_hub` |
| Bare repo path | `/home/u2520-3v1nc5i4btry/repos/sacci_brand_hub.git` |

---

## Step 1 — Add the Public Key to SiteGround

Go to **Site Tools → Devs → SSH Keys Manager → Add New SSH Key** and paste
the following public key exactly as shown:

```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDarkjztQ9jv6K/eRoctbO2U/YVXIUd8KQIURO7Q3L2K8aDtfn85UWDlU0p15kjx6dQBKSYaEBA1Nwy28MgFffBpDzaqEjcAnOH/q6W1jWNOlx5XyWMgjBRUFA0SkI2lQpX4+B+d3RYZmDDfrtjKiIOoa9cOM21W0XwV+cUrU7YnmKpB8IZcjiJ9V8SnD9AWGwCutaWKFmZo9tSrY00CdQQas4uwZhatDn7VFsJ35KFxc1aaY1n+l7wxz0luOs9uBwnzb1d6+39U366FQqeNuRfa04O54YPuNmKTacVF5znbP57AQmxeC82zNurSt+C9H3q6eL2g0dh6WKQwJlEEhi6fmo5xhZnb7r4SyoLaBRgPvUP15WvOrgki8HlJRO3nUssTKTVPC+n4oY+dC9zhhTPmXwhdOUnhKTT8WpOnnD9CJT9oZ0xbyWRzyVFSnbFL2iklAJ0gFTwRWkvSHU2kJhk8MicRRji++ISF6eiT8JY7bndoXRyqDh/T0C5VaQ0/eZ7C/JiI5QQmvceQvkjcr7OArQfTrdMavdX/u6k/7+FI5ZUsqtlgpOuM8j/7BU7djw2gssVYaZbJzWrr13FWZWmCtJcXI6rP9tMVxB5P7oxdFOIPXJnmQnLXdr8RDMiPaJ4p4oTa7EM59atUIgtEzPOesNuWGwX/D/FxU210bKRXw== sacci-deploy
```

The private key is stored at `/home/user/.ssh/sacci_siteground_rsa` in this
agent environment and is already configured in `/home/user/.ssh/config`.

---

## Step 2 — Test the SSH Connection

Once the public key is saved in SiteGround, run:

```bash
ssh sacci-sg
# Expected: a shell prompt on the SiteGround server
# (u2520-3v1nc5i4btry@sg-server-hostname:~$)
```

---

## Step 3 — Create the Bare Git Repo on the Server

SSH in and run these commands once:

```bash
ssh sacci-sg << 'SETUP'
set -e

# Bare repo lives outside public_html
mkdir -p ~/repos/sacci_brand_hub.git
cd ~/repos/sacci_brand_hub.git
git init --bare

# Confirm deploy target exists (already uploaded via File Manager)
ls ~/public_html/sacci_brand_hub/index.php

echo "=== Bare repo created ==="
SETUP
```

---

## Step 4 — Create the `post-receive` Hook on the Server

```bash
ssh sacci-sg << 'HOOK_SETUP'
cat > ~/repos/sacci_brand_hub.git/hooks/post-receive << 'HOOK'
#!/usr/bin/env bash
set -e
DEPLOY_DIR="$HOME/public_html/sacci_brand_hub"
GIT_WORK_TREE="$DEPLOY_DIR" git checkout -f main
echo ""
echo "=== Deployed to $DEPLOY_DIR ==="
HOOK
chmod +x ~/repos/sacci_brand_hub.git/hooks/post-receive
echo "=== post-receive hook installed ==="
HOOK_SETUP
```

---

## Step 5 — Add the Server as a Git Remote

Run this once in the local repo:

```bash
git remote add siteground \
  ssh://sacci-sg/home/u2520-3v1nc5i4btry/repos/sacci_brand_hub.git
```

Verify:
```bash
git remote -v
# siteground  ssh://sacci-sg/home/u2520-3v1nc5i4btry/repos/sacci_brand_hub.git (fetch)
# siteground  ssh://sacci-sg/home/u2520-3v1nc5i4btry/repos/sacci_brand_hub.git (push)
```

---

## Step 6 — First Deploy

```bash
git push siteground main
```

The `post-receive` hook checks out all tracked files into
`/home/u2520-3v1nc5i4btry/public_html/sacci_brand_hub` automatically.

---

## Day-to-Day Deploy Workflow

```bash
# 1. Make + commit changes
git add <files>
git commit -m "describe change"

# 2. Push to GitHub (source of truth)
git push origin main

# 3. Deploy to SiteGround
git push siteground main
```

---

## Files That Live ONLY on the Server (not in git)

| File / Dir | Why |
|---|---|
| `.env` | DB credentials + secrets — created by installer; **store this outside `public_html` (for example `~/sacci_brand_hub.env`) so it is never web-accessible** |
| `vendor/` | Composer dependencies — install on server separately |
| `storage/` | User-uploaded files |

After the **first deploy** via git, SSH in and verify these are intact:

```bash
# Check that the env file exists outside the web root and that vendor/ is present in the app directory
ssh sacci-sg "ls -la ~/sacci_brand_hub.env ~/public_html/sacci_brand_hub/vendor/"
```

If `vendor/` is missing:
```bash
ssh sacci-sg "cd ~/public_html/sacci_brand_hub && composer install --no-dev --optimize-autoloader"
```

---

## Verify the Deployment

```bash
curl -sI https://sacci.space/sacci_brand_hub/
# Expected: HTTP/2 302  Location: .../sacci_brand_hub/login
```

---

## Rollback

```bash
# Find the commit to revert to
git log --oneline -10

# Force-push that commit as the tip of main on SiteGround
git push siteground <commit-sha>:refs/heads/main --force
```

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `Permission denied (publickey)` | Re-check the key was saved in Site Tools → SSH Keys Manager |
| `Connection refused` on port 22 | Use port **18765** — already set in `~/.ssh/config` |
| Hook runs but files don't change | Confirm the branch name in the hook matches what you push (`main`) |
| White page after deploy | SSH in and check `.env` exists; check SiteGround error logs |
| `vendor/` missing | `ssh sacci-sg "cd ~/public_html/sacci_brand_hub && composer install --no-dev"` |
| `git push` hangs | SiteGround firewall may block outbound SSH; try from a different network |
