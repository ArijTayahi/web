# Symfony Asset Loading Diagnosis & Fix Guide

## Problem Summary
Symfony app works locally but assets (CSS/JS/images) render as raw HTML on another PC or fail to load (404/blank).

---

## Root Causes

### 1. **Wrong Web Root**
- Server serving from project root instead of `public/`
- Assets are in `public/assets/` but web server doesn't know this is the root

### 2. **Missing Router Script**
- `php -S` without router.php can't route requests properly
- Static files fail, Symfony routes fail

### 3. **Asset Compilation Issues**
- Assets not compiled/installed on the remote PC
- Using Symfony AssetMapper but controllers.json missing
- No npm build step run

### 4. **URL Rewriting Problems**
- mod_rewrite not enabled (Apache)
- AllowOverride All not set
- .htaccess not present or misconfigured

### 5. **Cache & Environment**
- Stale cache from different environment
- .env file incorrect on remote PC
- APP_ENV=prod without assets compiled

### 6. **Base Href Issues**
- Hardcoded base paths in templates
- Asset paths don't match actual serving path
- app() function generating wrong URLs

---

## VERIFICATION CHECKLIST

### Step 1: Check Server Configuration
```bash
# Verify current working directory
pwd
# Should be: C:\xampp\htdocs\eya

# Check what files are in public/
ls -la public/

# Should contain: index.php + assets/css/main.css, assets/js/main.js, etc.
```

### Step 2: Test Direct Asset URLs
**BEFORE configuring anything, test if assets actually exist and are accessible:**

1. Open browser to: `http://127.0.0.1:8001/assets/css/main.css`
   - ✅ **200 OK** = Assets are accessible and server root is correct
   - ❌ **404 Not Found** = Wrong web root OR assets not installed

2. Open browser to: `http://127.0.0.1:8001/index.php`
   - ✅ **200 OK with HTML** = Application loads
   - ❌ **Blank/Error** = Application issue

3. Open browser to: `http://127.0.0.1:8001/` (homepage)
   - ✅ **200 OK with styled HTML** = Everything works
   - ❌ **Raw HTML** = Assets not loading (proceed to fixes)

### Step 3: Check Assets Exist Locally
```bash
# On the problem PC, verify assets were installed
Test-Path "C:\xampp\htdocs\eya\public\assets\css\main.css"
# Should return: True

# List public directory contents
ls C:\xampp\htdocs\eya\public\
# Should show: index.php + assets/ folder
```

---

## FIXES BY SERVER TYPE

### FIX #1: Using PHP Built-in Server (php -S)

#### ✅ CORRECT SETUP
```bash
# Navigate to PROJECT ROOT (not public/)
cd C:\xampp\htdocs\eya

# Start server with:
# 1. -t public (document root = public/)
# 2. router.php (routes static files + Symfony routes)
php -S 127.0.0.1:8001 -t public router.php
```

#### ❌ WRONG SETUP (causes asset failures)
```bash
# WRONG #1: Missing -t public (serves from project root)
php -S 127.0.0.1:8001
# Assets look for /assets/ but actual path is /public/assets/

# WRONG #2: Missing router.php (can't route non-existent files)
php -S 127.0.0.1:8001 -t public
# Static files fail, CSS/JS 404s

# WRONG #3: Running from wrong directory
cd C:\xampp\htdocs\eya\public
php -S 127.0.0.1:8001
# Can't find router.php or config files
```

#### Verify it's working:
```bash
# Check direct asset URL
curl http://127.0.0.1:8001/assets/css/main.css
# Should return CSS content, not 404
```

---

### FIX #2: Using Apache/XAMPP

#### Step 1: Set DocumentRoot
```apache
# File: C:\xampp\apache\conf\httpd.conf
# Line ~252, change from:
DocumentRoot "C:/xampp/htdocs"

# To:
DocumentRoot "C:/xampp/htdocs/eya/public"

# Also update the Directory directive below it:
<Directory "C:/xampp/htdocs/eya/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

#### Step 2: Enable mod_rewrite & AllowOverride
```apache
# Ensure this line exists and is NOT commented (line ~163):
LoadModule rewrite_module modules/mod_rewrite.so

# Ensure AllowOverride All is set (in Directory section above):
AllowOverride All
```

#### Step 3: Create .htaccess
File: `C:\xampp\htdocs\eya\public\.htaccess`
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

#### Step 4: Restart Apache
```bash
# Using Apache directly
C:\xampp\apache\bin\httpd.exe -k restart

# Or use XAMPP control panel and click "Restart" on Apache module
```

#### Verify it's working:
```bash
# Test direct asset URL
curl http://127.0.0.1/assets/css/main.css
# Should return CSS content, not 404

# Test homepage
curl http://127.0.0.1/
# Should return styled HTML
```

---

### FIX #3: Asset Installation & Compilation

#### Check which asset system is used:
```bash
# If this file exists → using Symfony AssetMapper (modern)
Test-Path "C:\xampp\htdocs\eya\assets\controllers.json"

# If this folder exists → using public/assets/ (traditional)
Test-Path "C:\xampp\htdocs\eya\public\assets"
```

#### For Symfony AssetMapper (new projects):
```bash
# On the REMOTE PC, run:
cd C:\xampp\htdocs\eya
php bin/console importmap:install
php bin/console asset-map:compile

# Verify output folder was created:
ls public/assets/
# Should have compiled asset files
```

#### For Traditional Assets (older projects):
```bash
# On the REMOTE PC, run:
cd C:\xampp\htdocs\eya
php bin/console assets:install public --symlink
# or without symlink for Windows:
php bin/console assets:install public
```

---

### FIX #4: Clear Cache & Environment

```bash
cd C:\xampp\htdocs\eya

# Clear application cache
php bin/console cache:clear

# Check .env file on remote PC
cat .env
# Verify APP_ENV=dev (not prod)
# Verify DATABASE_URL matches remote setup

# If in prod, compile assets first:
php bin/console assets:install public
php bin/console cache:clear --env=prod
```

---

### FIX #5: Fix Base Href & Asset URLs

#### Check templates:
File: `templates/base.html.twig`

```twig
{# WRONG #1: Hardcoded paths #}
<link rel="stylesheet" href="/assets/css/main.css">
{# Breaks if app not at root or uses different domain #}

{# WRONG #2: Absolute URLs without scheme #}
<link rel="stylesheet" href="//example.com/assets/css/main.css">
{# Works locally but breaks on different hosts #}

{# ✅ CORRECT: Use Symfony asset() function #}
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

{# ✅ CORRECT: Use absolute_url() for full URLs #}
<link rel="stylesheet" href="{{ absolute_url(asset('assets/css/main.css')) }}">
```

#### Check for base href:
```html
{# WRONG: Hardcoded base #}
<base href="/eya/public/">

{# ✅ CORRECT: Use Symfony #}
<base href="{{ app.request.basePath }}/">
```

---

### FIX #6: Firewall & Remote Access

#### If accessing from ANOTHER MACHINE on network:

```bash
# Bind to all interfaces instead of localhost
php -S 0.0.0.0:8001 -t public router.php

# Or for Apache, allow remote access
# File: C:\xampp\apache\conf\httpd.conf
# Change: Require all granted (in Directory section)
# If using Allow/Deny:
Allow from all
```

#### Enable Windows Firewall:
```bash
# Allow PHP/Apache through Windows Firewall
netsh advfirewall firewall add rule name="Apache/PHP" dir=in action=allow program="C:\xampp\apache\bin\httpd.exe" enable=yes

netsh advfirewall firewall add rule name="PHP" dir=in action=allow program="C:\xampp\php\php.exe" enable=yes

# Or allow port 8001 and 80:
netsh advfirewall firewall add rule name="Port 8001" dir=in action=allow protocol=tcp localport=8001
netsh advfirewall firewall add rule name="Port 80" dir=in action=allow protocol=tcp localport=80
```

#### Test from remote PC:
```bash
# From another computer on the network
curl http://192.168.x.x:8001/assets/css/main.css
# or
curl http://192.168.x.x/assets/css/main.css
```

---

## COMPLETE ACTION CHECKLIST

### ✅ BEFORE STARTING
- [ ] Verify project is in `C:\xampp\htdocs\eya`
- [ ] Composer installed: `composer install` already run
- [ ] Database created: `php bin/console doctrine:database:create`
- [ ] Migrations run: `php bin/console doctrine:migrations:migrate`

### ✅ SERVER CONFIGURATION (Choose ONE)

#### Option A: PHP Built-in Server
- [ ] Navigate to: `cd C:\xampp\htdocs\eya`
- [ ] Start with: `php -S 127.0.0.1:8001 -t public router.php`
- [ ] Test: `curl http://127.0.0.1:8001/assets/css/main.css` → should return CSS
- [ ] Test: `curl http://127.0.0.1:8001/` → should return styled HTML

#### Option B: Apache/XAMPP
- [ ] Edit `C:\xampp\apache\conf\httpd.conf`
- [ ] Change DocumentRoot to: `C:/xampp/htdocs/eya/public`
- [ ] Verify mod_rewrite enabled: `LoadModule rewrite_module modules/mod_rewrite.so`
- [ ] Set `AllowOverride All` in Directory section
- [ ] Create `.htaccess` in `C:\xampp\htdocs\eya\public\`
- [ ] Restart Apache
- [ ] Test: `curl http://127.0.0.1/assets/css/main.css` → should return CSS

### ✅ ASSETS

- [ ] Check if assets exist: `Test-Path "C:\xampp\htdocs\eya\public\assets\css\main.css"`
- [ ] If NOT exist, run: `php bin/console assets:install public`
- [ ] If using AssetMapper: `php bin/console asset-map:compile`
- [ ] Verify: `ls C:\xampp\htdocs\eya\public\assets\`

### ✅ CACHE & ENVIRONMENT

- [ ] Clear cache: `php bin/console cache:clear`
- [ ] Check `.env`: `APP_ENV=dev` (NOT prod for testing)
- [ ] Check `.env`: `DATABASE_URL` matches setup
- [ ] If prod: run `php bin/console cache:clear --env=prod`

### ✅ TEMPLATES

- [ ] Check `templates/base.html.twig` for hardcoded paths
- [ ] Replace `/assets/` with `{{ asset('assets/') }}`
- [ ] Replace hardcoded `<base href>` with Symfony helpers
- [ ] Clear browser cache (Ctrl+Shift+Delete)

### ✅ FIREWALL (If Remote Access)

- [ ] Bind to `0.0.0.0:8001` instead of `127.0.0.1:8001`
- [ ] Allow firewall: port 8001 or 80
- [ ] Test from remote machine: `curl http://[SERVER_IP]:8001/`

### ✅ FINAL VERIFICATION

- [ ] Direct asset URL works: `http://[IP]:8001/assets/css/main.css` → CSS file displayed
- [ ] Homepage loads: `http://[IP]:8001/` → HTML with styles applied
- [ ] Check browser DevTools → Network tab → no 404s for CSS/JS
- [ ] Check browser console → no errors

---

## TROUBLESHOOTING QUICK REFERENCE

| Symptom | Cause | Fix |
|---------|-------|-----|
| `http://127.0.0.1:8001/` loads but CSS unstyled | Assets 404 | Check web root is `public/` |
| `http://127.0.0.1:8001/assets/css/main.css` returns 404 | Assets not installed or wrong root | Run `assets:install` or fix DocumentRoot |
| Page returns raw HTML without styles | Router missing or .htaccess broken | Use `router.php` or enable mod_rewrite |
| Works on one PC, not on another | Different config/cache | Clear cache + check .env |
| Works locally, fails on remote | Server not bound to 0.0.0.0 | Change `127.0.0.1` to `0.0.0.0` |
| Apache 404 on all pages | DocumentRoot wrong | Set to `.../eya/public` |
| Assets 404 on Apache | mod_rewrite disabled | Enable LoadModule and AllowOverride |
| Browser shows styles but fonts/images missing | Partial asset loading | Check Asset Mapper compilation |

---

## COMMAND REFERENCE

```bash
# Quick setup (php -S)
cd C:\xampp\htdocs\eya
php bin/console cache:clear
php bin/console assets:install public
php -S 127.0.0.1:8001 -t public router.php

# Quick setup (Apache)
php bin/console cache:clear
php bin/console assets:install public
# Edit httpd.conf + restart Apache

# Verify assets exist
Test-Path "C:\xampp\htdocs\eya\public\assets\css\main.css"

# Verify server running
curl http://127.0.0.1:8001/
curl http://127.0.0.1:8001/assets/css/main.css

# Remote access
php -S 0.0.0.0:8001 -t public router.php
# Then access from other PC: http://[MACHINE_IP]:8001/
```

---

## KEY TAKEAWAYS

1. **Web root MUST be `public/`** - Not the project root
2. **Use router.php with php -S** - For proper routing
3. **Asset URLs must use Symfony helpers** - `{{ asset() }}` function
4. **Clear cache after changes** - `php bin/console cache:clear`
5. **Test with direct URLs** - `/assets/css/main.css` first before full page
6. **Enable mod_rewrite** - Required for pretty URLs on Apache
7. **Allow remote access** - Bind to `0.0.0.0` and check firewall
8. **Match env locally** - Same APP_ENV and DATABASE_URL as remote

