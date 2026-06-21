# Madhavi Stores — Hostinger Shared Hosting Deployment Guide

**Domain:** madhavistores.in  
**Stack:** Laravel 12 · PHP 8.2+ · MySQL · Vite-compiled CSS/JS (Tailwind 3.4)

---

## PHASE 0 — Manual Credential Steps (Do These First, Before Touching the Server)

These cannot be automated. Do them before uploading anything.

### 0.1 Revoke the old Gmail App Password
1. Go to [myaccount.google.com](https://myaccount.google.com) → **Security** → **App Passwords**
2. Find the password you used for Madhavi Stores (the one ending in `kzv`)
3. Click the bin icon → **Remove**
4. Click **Create app password** → Select app: Mail → Device: Other → type "Madhavi Stores Server" → **Generate**
5. Copy the new 16-character password — you will need it in Phase 5

### 0.2 Revoke the old Instagram token
1. Go to [developers.facebook.com](https://developers.facebook.com) → your app → **Tools** → **Access Token Debugger**
2. Paste the old token (`EAAWwLvo...`) → click **Debug**
3. Click **Revoke Token** (or let it expire — long-lived tokens last 60 days; generate a new one for safety)
4. To generate a new long-lived token: **Graph API Explorer** → select your app → request `instagram_basic` permission → **Generate Access Token** → exchange for long-lived token via the API
5. Save the new token for Phase 5

### 0.3 Get real Razorpay live keys
1. Log in to [dashboard.razorpay.com](https://dashboard.razorpay.com)
2. Go to **Settings** → **API Keys** → **Generate Key** (switch to Live mode first — top-left toggle)
3. Download or copy the **Key ID** (starts with `rzp_live_`) and **Key Secret**
4. Save both for Phase 5

---

## PHASE 1 — Prepare Files on Your Computer

### Step 1.1: Optimize Composer for production
Open terminal/PowerShell in the project folder (`C:\xampp\htdocs\Madhavi-Stores-`):
```bash
composer install --no-dev --optimize-autoloader
```
This strips out debugbar, faker, and other dev-only packages. Reduces `vendor/` size significantly.

### Step 1.2: Verify the Vite build is current
```bash
npm run build
```
Confirm that `public/build/manifest.json` exists and contains entries for both `resources/css/app.css` and `resources/js/app.js`.

### Step 1.3: Create a deployment ZIP
Select all project files and compress to `madhavi-stores.zip`.

**Exclude these** (right-click exclude or delete from zip after creating):
| Exclude | Why |
|---------|-----|
| `node_modules/` | 300MB+, not needed on server |
| `.git/` | Version control history, not needed |
| `.env` | You will create a fresh one on the server |
| `storage/logs/*.log` | Local debug logs |

**Include everything else**, including:
- `vendor/` (Composer packages)
- `public/build/` (Vite-compiled CSS/JS — critical)
- `public/images/` (any existing uploaded images)
- `database/migrations/` (all migrations)

---

## PHASE 2 — Hostinger Account & Server Configuration

### Step 2.1: Log in to hPanel
Go to [hpanel.hostinger.com](https://hpanel.hostinger.com) and sign in.

### Step 2.2: Set PHP version to 8.2
**Websites** → **Manage** → **PHP Configuration** → **PHP Version** → select **8.2** (or 8.3)

Click **Save**.

### Step 2.3: Verify PHP extensions are enabled
Still in **PHP Configuration** → **Extensions**, confirm these are enabled (tick the checkbox if not):

| Extension | Purpose |
|-----------|---------|
| `pdo_mysql` | Database connection |
| `gd` | WebP image conversion |
| `mbstring` | String handling |
| `openssl` | Encrypted mail / HTTPS |
| `fileinfo` | File type detection |
| `bcmath` | Precise decimal math (prices) |
| `json` | API responses |
| `tokenizer` | Blade templating |
| `xml` | Sitemap generation |
| `curl` | External API calls (Razorpay) |

Click **Save** after enabling any that were off.

### Step 2.4: Enable SSH access
**Hosting** → **SSH Access** → **Enable**

Note down:
- **Host:** e.g., `srv123.hostinger.com`
- **Port:** `65002` (Hostinger's non-standard SSH port)
- **Username:** your Hostinger account username (e.g., `u123456789`)

You will use this to run Artisan commands remotely.

### Step 2.5: Create a MySQL database
**Databases** → **MySQL Databases** → **Create New Database**

Fill in:
- **Database name:** `madhavi` (Hostinger will prefix it: `u123456789_madhavi`)
- **Username:** `madhavi` (prefixed to `u123456789_madhavi`)
- **Password:** generate a strong password (save it!)

Click **Create**. Write down:
- Full DB name: `u123456789_madhavi`
- Full DB username: `u123456789_madhavi`
- DB password: _(the one you just set)_
- DB host: `127.0.0.1`

---

## PHASE 3 — Domain & SSL Configuration

### Step 3.1: Add the domain (if not already pointing to Hostinger)
**If madhavistores.in is registered at Hostinger:** it may already be connected.

**If registered elsewhere (GoDaddy, Namecheap, etc.):**
1. In hPanel → **Domains** → note Hostinger's nameservers (e.g., `ns1.dns-parking.com`, `ns2.dns-parking.com`)
2. Go to your domain registrar → DNS/Nameserver settings
3. Replace existing nameservers with Hostinger's two nameservers
4. Save — propagation takes 2–48 hours

**In hPanel → Domains → Add Domain:** enter `madhavistores.in` and click **Add**.

### Step 3.2: File layout — split upload method (no Document Root change needed)
Hostinger shared hosting doesn't always expose a Document Root setting. Instead, the project is split into two locations so `public_html/` is the web root and the Laravel source stays hidden:

```
home/u750617805/
└── domains/
    └── madhavistores.in/
        ├── madhavi-app/                    ← Laravel source (NOT web-accessible)
        │   ├── app/
        │   ├── bootstrap/
        │   ├── config/
        │   ├── database/
        │   ├── resources/
        │   ├── routes/
        │   ├── storage/
        │   ├── vendor/
        │   └── .env
        │
        └── public_html/                    ← web root (madhavistores.in serves this)
            ├── index.php                   ← modified to find madhavi-app/ automatically
            ├── .htaccess
            ├── build/                      ← Vite CSS/JS assets
            ├── images/                     ← product/banner images
            └── storage/                    ← symlink → madhavi-app/storage/app/public/
```

**Why this exact layout:** `public/index.php` resolves the app root as `__DIR__.'/../madhavi-app'` (i.e. `public_html/../madhavi-app`), and `bootstrap/app.php` sets the public path to `dirname(__DIR__, 2).'/public_html'` (i.e. `madhavistores.in/public_html`). Both paths only work when the two folders are siblings inside `domains/madhavistores.in/`. No manual edits needed — the code auto-detects the layout at runtime.

### Step 3.3: Enable SSL (HTTPS)
**Websites → Manage → SSL** → **Install Free SSL** (Let's Encrypt) → **Install**

Wait 5–10 minutes. Once installed:
- Enable **Force HTTPS** (redirects all HTTP traffic to HTTPS automatically)

---

## PHASE 4 — Upload Files to the Server

You upload in **two separate batches**: the Laravel app goes into `madhavi-app/`, the public files go into `public_html/`.

### Step 4.1: Create two ZIPs on your computer

**ZIP A — `madhavi-app.zip`**  
Zip the entire project folder, but EXCLUDE:
- `public/` folder entirely (goes separately)
- `node_modules/`
- `.git/`
- `.env`

**ZIP B — `public-files.zip`**  
Zip only the **contents** of your local `public/` folder:
- `index.php`
- `.htaccess`
- `build/` (Vite CSS/JS)
- `images/` (product/banner images, if any)
- `favicon.ico`, `robots.txt`, etc.

Do NOT zip the `public/` folder itself — zip what's inside it.

---

### Option A: File Manager (no extra software needed)

**Upload the app (ZIP A):**
1. **hPanel → File Manager** → navigate to the home directory (the folder that contains `public_html/` — usually one click up from `public_html/`)
2. Click **New Folder** → name it `madhavi-app` → Create
3. Enter the `madhavi-app/` folder
4. Click **Upload** → select `madhavi-app.zip` → upload
5. Right-click the zip → **Extract** — extract here (into `madhavi-app/`)
6. Delete the zip file after extracting

**Upload the public files (ZIP B):**
1. Navigate to `public_html/`
2. Click **Upload** → select `public-files.zip` → upload
3. Right-click the zip → **Extract** — extract here (into `public_html/`)
4. Delete the zip file after extracting

**Verify the structure:**
- `madhavi-app/vendor/autoload.php` must exist
- `public_html/index.php` must exist
- `public_html/build/manifest.json` must exist

### Option B: FTP via FileZilla (faster for large uploads)
1. **hPanel → FTP Accounts** → note the host, username, password, and port (21)
2. Install [FileZilla](https://filezilla-project.org/) → connect
3. In the right (server) panel, navigate to your home directory (parent of `public_html/`)
4. Create a `madhavi-app/` folder if it doesn't exist
5. **Drag** your local project folder (everything except `public/`, `node_modules/`, `.git/`, `.env`) into `madhavi-app/` on the server
6. **Drag** the contents of your local `public/` folder into `public_html/` on the server
7. Upload takes 10–30 minutes for large `vendor/` folders

### Option C: Git via SSH (cleanest method)
If your project is on GitHub (`.env` must be in `.gitignore`):
```bash
ssh -p 65002 u123456789@srv123.hostinger.com

# Create the app directory (MUST be a sibling of public_html inside domains/madhavistores.in/)
mkdir -p ~/domains/madhavistores.in/madhavi-app

# Clone into it
git clone https://github.com/YOUR_USERNAME/Madhavi-Stores-.git ~/domains/madhavistores.in/madhavi-app

# Install production Composer dependencies
cd ~/domains/madhavistores.in/madhavi-app
composer install --no-dev --optimize-autoloader

# Copy public files to the web root
cp -r ~/domains/madhavistores.in/madhavi-app/public/. ~/domains/madhavistores.in/public_html/
```

---

## PHASE 5 — Create the Production .env File

This file holds all your secrets. **Never commit it to Git.**

### Step 5.1: Open File Manager
**hPanel → File Manager** → navigate to `madhavi-app/` (NOT public_html)

### Step 5.2: Create .env
Click **New File** → name it `.env` → click **Create**

Right-click `.env` → **Edit** → paste the following and fill in every `← fill this in` value:

```env
APP_NAME="Madhavi Stores"
APP_ENV=production
APP_KEY=                                ← leave blank (generated in Phase 6 Step 1)
APP_DEBUG=false
APP_URL=https://madhavistores.in

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_madhavi         ← your full database name from Phase 2.5
DB_USERNAME=u123456789_madhavi         ← your full database username from Phase 2.5
DB_PASSWORD=YOUR_DB_PASSWORD           ← the password you set in Phase 2.5

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=kshitijmay14@gmail.com
MAIL_PASSWORD=YOUR_NEW_APP_PASSWORD    ← new Gmail App Password from Phase 0.1
MAIL_FROM_ADDRESS=kshitijmay14@gmail.com
MAIL_FROM_NAME="Madhavi Stores"

VITE_APP_NAME="${APP_NAME}"

INSTAGRAM_ACCESS_TOKEN=YOUR_NEW_TOKEN  ← new Instagram token from Phase 0.2
INSTAGRAM_BUSINESS_ACCOUNT_ID=

RAZORPAY_KEY_ID=rzp_live_XXXXXXXX      ← live Key ID from Phase 0.3 (starts with rzp_live_)
RAZORPAY_KEY_SECRET=YOUR_LIVE_SECRET   ← live Key Secret from Phase 0.3
```

Click **Save**.

---

## PHASE 6 — Laravel Initialization via SSH

Connect:
```bash
ssh -p 65002 u123456789@srv123.hostinger.com
cd ~/domains/madhavistores.in/madhavi-app
```

Run these commands **in order** — do not skip any:

```bash
# 1. Generate APP_KEY and write it into .env automatically
php artisan key:generate

# 2. Run all database migrations (creates all tables)
php artisan migrate --force

# 3. Create the public/storage symlink (needed for uploaded product images)
php artisan storage:link

# 4. Cache configuration for speed (reads .env once, stores it in bootstrap/cache/)
php artisan config:cache

# 5. Cache all route definitions
php artisan route:cache

# 6. Compile all Blade templates
php artisan view:cache
```

### Verify everything is working
```bash
# Should print: "The application environment is [production]"
php artisan env

# Should show all migrations as "Ran" — nothing should say "Pending"
php artisan migrate:status
```

If `migrate:status` shows errors, check that the DB credentials in `.env` are correct.

---

## PHASE 7 — File Permissions

Still in SSH (you should already be inside `madhavi-app/`):

```bash
# Storage and cache directories must be writable by the web server
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

The public files live in `public_html/` (not inside `madhavi-app/`), so image and storage symlink permissions use the full path:

```bash
# Product image upload directory
chmod -R 755 ~/domains/madhavistores.in/public_html/images/

# The public_html/storage symlink created by storage:link
chmod -R 755 ~/domains/madhavistores.in/public_html/storage/
```

If `public_html/images/` doesn't exist yet, create it:
```bash
mkdir -p ~/domains/madhavistores.in/public_html/images/products
mkdir -p ~/domains/madhavistores.in/public_html/images/categories
mkdir -p ~/domains/madhavistores.in/public_html/images/banners
chmod -R 755 ~/domains/madhavistores.in/public_html/images/
```

---

## PHASE 8 — Cron Job Setup (Emails + Queue + Cleanup)

The Laravel scheduler handles everything with a single cron entry:
- OTP cleanup (every 30 minutes)
- Session garbage collection (daily)
- Queue email processing — OTP emails and invoice emails (every minute)

### Add the cron in hPanel
**hPanel → Advanced → Cron Jobs → Add New Cron Job**

| Field | Value |
|-------|-------|
| **Command** | `cd /home/u750617805/domains/madhavistores.in/madhavi-app && php artisan schedule:run >> /dev/null 2>&1` |
| **Frequency** | Every minute (`* * * * *`) |

Replace `u123456789` with your actual Hostinger username.

Click **Create**.

> **How emails get sent:** `OtpMail` and `InvoiceMail` are queued jobs. When a user logs in or places an order, the job goes into the `jobs` database table. The scheduler runs `queue:work --once` every minute to process one job from the table. Emails arrive within ~1 minute — without blocking the HTTP response.

---

## PHASE 9 — Verify the Live Site

Open **https://madhavistores.in** in an incognito browser window.

### Checklist

**Visual & loading**
- [ ] Homepage loads without errors or warnings
- [ ] Check page source (`Ctrl+U`) — must NOT contain `cdn.tailwindcss.com`; must contain `/build/assets/app-` (Vite-compiled CSS)
- [ ] Product images load correctly
- [ ] Banner images load correctly
- [ ] Mobile layout looks correct on phone screen size

**Authentication**
- [ ] Register with your email → OTP email arrives within 1 minute
- [ ] Log in using OTP → redirects to home/account
- [ ] Session persists across pages

**Shopping flow**
- [ ] Browse shop → products visible
- [ ] Click product → product detail page loads with sizes
- [ ] Add to cart → cart badge updates
- [ ] Visit cart → items visible, quantities correct
- [ ] Proceed to checkout → Razorpay payment form appears (not a dummy/error)

**Admin**
- [ ] `/admin` loads (or your admin route)
- [ ] Upload a product with JPG image → `.webp` file created in `public/images/products/`
- [ ] Create a new category → appears on homepage immediately

**Security**
- [ ] HTTPS padlock is green (no mixed content warnings)
- [ ] `http://madhavistores.in` redirects to `https://madhavistores.in`
- [ ] `APP_DEBUG` is `false` — error pages should show generic "500" not stack traces

---

## PHASE 10 — Post-Launch Maintenance

### Monitor error logs (SSH)
```bash
ssh -p 65002 u123456789@srv123.hostinger.com
tail -n 50 ~/domains/madhavistores.in/madhavi-app/storage/logs/laravel.log
```

Check this after the first few real orders to catch any unexpected errors.

### Deploying code updates
After making changes locally and pushing to Git (or creating a new zip):

```bash
# 1. SSH in and go to the app directory
ssh -p 65002 u123456789@srv123.hostinger.com
cd ~/domains/madhavistores.in/madhavi-app

# 2. Pull latest code (if using Git)
git pull origin main

# 3. Run any new migrations
php artisan migrate --force

# 4. Clear old caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Re-cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If you changed CSS or JS locally:
```bash
# On your computer first:
npm run build

# Then upload the new public/build/ folder to public_html/build/ on the server
# (overwrite the existing one via FTP or File Manager — target: ~/domains/madhavistores.in/public_html/build/)
```

### Regular backups (do this monthly at minimum)
**hPanel → Backups → Create Backup**

This backs up both your files and your database. Download the backup to your computer and keep a copy.

### Rotating credentials (do this every 6 months)
- Gmail App Password: Google Account → Security → App Passwords → delete old → create new → update `.env`
- Razorpay keys: Razorpay Dashboard → Settings → API Keys → regenerate → update `.env`
- After updating `.env`: run `php artisan config:cache` to apply the change

---

## TROUBLESHOOTING

| Symptom | Most likely cause | Fix |
|---------|------------------|-----|
| Blank white page | Missing .env key or wrong DB credentials | Check `storage/logs/laravel.log`. Temporarily set `APP_DEBUG=true`, reload, read the error, set back to `false`. |
| 500 Internal Server Error | File permissions or missing APP_KEY | Run `chmod -R 755 storage/ bootstrap/cache/`. Run `php artisan key:generate` if APP_KEY is blank. |
| 404 on all pages except home | Document root not set to `/public` | Repeat Phase 3 Step 2. Confirm root points to `/public_html/public`. |
| CSS looks completely broken | `public/build/` not uploaded, or `manifest.json` missing | Re-run `npm run build` locally. Upload the new `public/build/` folder. |
| Images not loading (products/banners) | Storage symlink missing | Run `php artisan storage:link`. Check `chmod 755 public/storage/`. |
| OTP emails not arriving | Wrong MAIL_PASSWORD or Gmail blocking | Check spam folder. Verify App Password is new (from Phase 0.1). Check logs for `Failed to authenticate`. |
| "Invalid API Key" on Razorpay | Using test key (`rzp_test_`) in production | Replace with live keys (`rzp_live_`) from Phase 0.3. Run `php artisan config:cache`. |
| Queue jobs stuck (emails never send) | Cron not running | Check hPanel → Cron Jobs. Verify command path is correct (use your actual username). Check `php artisan schedule:list` via SSH. |
| `php artisan` command not found | Wrong PHP binary | Use full path: `/usr/local/php82/bin/php artisan` instead of `php artisan`. Or ask Hostinger support which PHP binary to use. |
| Session expires instantly | `SESSION_DRIVER=database` but sessions table missing | Set `SESSION_DRIVER=file` in `.env` (already set in the template above). Run `php artisan config:cache`. |
| "Class not found" errors | `composer install` not run, or run with dev deps | Run `composer install --no-dev --optimize-autoloader` via SSH. |
| Uploaded images not converting to WebP | GD extension not enabled | hPanel → PHP Configuration → Extensions → enable `gd` → Save. |

---

## QUICK REFERENCE — SSH Commands

```bash
# Connect
ssh -p 65002 u123456789@srv123.hostinger.com

# Go to project (always run artisan from here)
cd ~/domains/madhavistores.in/madhavi-app

# Most-used Artisan commands
php artisan env                          # check APP_ENV is "production"
php artisan migrate:status               # check migration status
php artisan migrate --force              # run pending migrations
php artisan config:cache                 # re-cache after .env changes
php artisan config:clear                 # clear config cache
php artisan cache:clear                  # clear application cache
php artisan queue:work --once            # manually process one queued job
php artisan storage:link                 # recreate public_html/storage symlink
tail -n 100 storage/logs/laravel.log     # view recent errors
```

---

## WHAT YOU CANNOT DO ON SHARED HOSTING

| Feature | Why not available | Workaround |
|---------|-----------------|------------|
| Persistent queue worker (`queue:work`) | Shared hosting kills long-running processes | Handled via scheduler cron (Phase 8) |
| Redis cache/sessions | Redis not available on shared plans | Using `file` driver for both (set in .env) |
| WebSockets (real-time) | Requires persistent socket server | Not needed for this store |
| Unlimited concurrent requests | Shared CPU/memory limits | Indexes + caching (already implemented) handle 10k users |
