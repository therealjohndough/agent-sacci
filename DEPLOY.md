# Sacci Brand Hub — SSH & Git Deployment to SiteGround

## Connection Details

> **Note:** Real connection values (hostname, username, port, paths) are stored in the
> private project runbook. Replace every `<PLACEHOLDER>` below before running any command.

| What | Placeholder |
|---|---|
| SSH hostname | `<YOUR_SSH_HOSTNAME>` |
| SSH username | `<YOUR_SSH_USERNAME>` |
| SSH port | `<YOUR_SSH_PORT>` |
| Server deploy path | `/home/<YOUR_SSH_USERNAME>/public_html/sacci_brand_hub` |
| Bare repo path | `/home/<YOUR_SSH_USERNAME>/repos/sacci_brand_hub.git` |

---

## Step 1 — Add the Public Key to SiteGround

Go to **Site Tools → Devs → SSH Keys Manager → Add New SSH Key** and paste
your public key. Generate a dedicated deploy keypair if you don't have one:

```bash
ssh-keygen -t ed25519 -C "sacci-deploy" -f ~/.ssh/sacci_siteground_ed25519
# Then add ~/.ssh/sacci_siteground_ed25519.pub in SiteGround's SSH Keys Manager
```

Add an entry to `~/.ssh/config` so subsequent commands can use the `sacci-sg` alias:

```
Host sacci-sg
  HostName     <YOUR_SSH_HOSTNAME>
  User         <YOUR_SSH_USERNAME>
  Port         <YOUR_SSH_PORT>
  IdentityFile ~/.ssh/sacci_siteground_ed25519
```

---

## Step 2 — Test the SSH Connection

Once the public key is saved in SiteGround, run:

```bash
ssh sacci-sg
# Expected: a shell prompt on the SiteGround server
# (<YOUR_SSH_USERNAME>@sg-server-hostname:~$)
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
  ssh://sacci-sg/home/<YOUR_SSH_USERNAME>/repos/sacci_brand_hub.git
```

Verify:
```bash
git remote -v
# siteground  ssh://sacci-sg/home/<YOUR_SSH_USERNAME>/repos/sacci_brand_hub.git (fetch)
# siteground  ssh://sacci-sg/home/<YOUR_SSH_USERNAME>/repos/sacci_brand_hub.git (push)
```

---

## Step 6 — First Deploy

```bash
git push siteground main
```

The `post-receive` hook checks out all tracked files into
`/home/<YOUR_SSH_USERNAME>/public_html/sacci_brand_hub` automatically.

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
curl -sI https://<YOUR_DOMAIN>/sacci_brand_hub/
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
| `Connection refused` on port 22 | SiteGround uses a non-default port — ensure the `Port` in `~/.ssh/config` under `Host sacci-sg` matches what was configured in Step 1 |
| Hook runs but files don't change | Confirm the branch name in the hook matches what you push (`main`) |
| White page after deploy | SSH in and check `.env` exists; check SiteGround error logs |
| `vendor/` missing | `ssh sacci-sg "cd ~/public_html/sacci_brand_hub && composer install --no-dev"` |
| `git push` hangs | SiteGround firewall may block outbound SSH; try from a different network |
