# Installation Guide - EYA Medical Platform

Complete step-by-step instructions for setting up the EYA project on your local machine.

## ⚙️ Prerequisites

### System Requirements
- **OS**: Windows, macOS, or Linux
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or compatible (MariaDB 10.11+)
- **Composer**: Latest version
- **Git**: Latest version

### Verify Prerequisites

```bash
# Check PHP version
php --version

# Check MySQL is running
mysql --version

# Check Composer is installed
composer --version

# Check Git is installed
git --version
```

## 📦 Installation Steps

### Step 1: Clone Repository

Clone the EYA project from GitHub:

```bash
git clone https://github.com/eyaarg/MedTime.git eya
cd eya
```

### Step 2: Install PHP Dependencies

Install all required PHP packages via Composer:

```bash
composer install
```

This will:
- Download 128+ packages
- Generate autoloader files
- Install Symfony bundles and dependencies
- Set up database connection tools

**Expected time**: 2-5 minutes
**Disk space**: ~500 MB

### Step 3: Configure Database Connection

Edit `.env` file and verify database configuration:

```bash
# Current default configuration:
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
```

If your MySQL setup differs, update the URL:

```
# Format: mysql://username:password@host:port/database_name?serverVersion=VERSION&charset=utf8mb4

# Examples:
# Local XAMPP (default):
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"

# With password:
DATABASE_URL="mysql://root:MyPassword@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"

# Remote database:
DATABASE_URL="mysql://dbuser:dbpass@192.168.1.100:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"

# MariaDB:
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

### Step 4: Ensure MySQL is Running

Start MySQL service if not already running:

#### Windows (XAMPP)
```bash
# Start MySQL service
C:\xampp\mysql\bin\mysqld.exe --port 3306
# Or use XAMPP Control Panel
```

#### macOS (Homebrew)
```bash
brew services start mysql
```

#### Linux
```bash
sudo systemctl start mysql
```

### Step 5: Create Database

```bash
php bin/console doctrine:database:create
```

**Expected output**:
```
Created database `mediplatform` for connection named default.
```

Or if database already exists:
```
Database `mediplatform` for connection named default already exists. Skipped.
```

### Step 6: Apply Database Migrations

Run all pending migrations to create tables:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

**Expected migrations**:
- Version20260205124620 - Create initial schema
- Version20260206120000 - Add RBAC and entity tables
- Version20260206123000 - Make legacy columns nullable
- Version20260207160000 - Make users.roles nullable

**Expected output**:
```
 [OK] 4 Migrations executed successfully
```

### Step 7: Load Demo Data (Optional but Recommended)

Load sample users and test data:

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

**Expected output**:
```
 [OK] Database purged successfully
 [OK] 15 objects loaded successfully
```

This creates:
- 1 super admin user
- 1 admin user
- 4 doctor accounts
- 8 patient accounts
- Associated profiles and documents

### Step 8: Verify Installation

Check that everything is working:

```bash
# Sync migration metadata
php bin/console doctrine:migrations:sync-metadata-storage

# Clear cache
php bin/console cache:clear

# Check database tables
php bin/console doctrine:query:sql "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema='mediplatform';"
```

Expected table count: 8 tables

### Step 9: Compile Assets (Optional)

Compile frontend assets if needed:

```bash
php bin/console asset-map:compile
```

This is already done, but regenerate if you modify CSS/JS files.

## ✅ Verification Checklist

After installation, verify everything works:

- [ ] Database created (mediplatform)
- [ ] All 4 migrations applied
- [ ] 8 tables present in database
- [ ] Demo users loaded (15 users)
- [ ] Cache cleared successfully
- [ ] No error messages in console

## 🏃 Running the Application

### Option 1: Built-in PHP Server (Recommended for Development)

```bash
php -S 127.0.0.1:8001 -t public
```

Access the application:
- **URL**: `http://127.0.0.1:8001`
- **Quit**: Press `Ctrl+C` to stop

### Option 2: XAMPP Apache Server

1. Ensure project is in: `C:\xampp\htdocs\eya`
2. Start Apache via XAMPP Control Panel
3. Access at: `http://localhost/eya`

### Option 3: Docker (Not Configured Yet)

Docker support coming soon.

## 🔑 Login with Demo Accounts

After loading fixtures, use these credentials:

### Super Admin Access
```
Username: superadmin
Password: SuperAdmin@2026
```

### Admin Access
```
Username: admin
Password: Admin@2026
```

### Doctor Access (any of these)
```
Username: dr.smith (or dr.johnson, dr.williams, dr.brown)
Password: Doctor@2026
```

### Patient Access (any of these)
```
Username: patient1 (through patient8)
Password: Patient@2026
```

## 📋 Directory Permissions

Ensure the following directories are writable:

```bash
# For Windows (run as Administrator):
icacls var /grant Everyone:F /T
icacls public\uploads /grant Everyone:F /T

# For macOS/Linux:
chmod -R 777 var/
chmod -R 777 public/uploads/
```

## 🐛 Troubleshooting

### "Database connection failed"

```bash
# Check MySQL is running
# Verify DATABASE_URL in .env
# Test connection:
php bin/console doctrine:query:sql "SELECT 1"
```

### "SQLSTATE[HY000]: General error: 1030 Got an error..."

```bash
# Fix migration metadata
php bin/console doctrine:migrations:sync-metadata-storage

# Clear cache
php bin/console cache:clear

# Retry migration
php bin/console doctrine:migrations:migrate
```

### "CSS/JS files return 404"

```bash
# Clear cache
php bin/console cache:clear

# Recompile assets
php bin/console asset-map:compile

# Validate CSS loading
python scripts/check_css.py http://127.0.0.1:8001/
```

### "Migration table not found"

```bash
# Sync migration schema
php bin/console doctrine:migrations:sync-metadata-storage

# Check status
php bin/console doctrine:migrations:list
```

### "Port 8001 already in use"

Use a different port:
```bash
php -S 127.0.0.1:8002 -t public
```

## 📚 Next Steps

1. **Read the README**: [README.md](README.md)
2. **Review Demo Guide**: [DEMO_GUIDE.md](DEMO_GUIDE.md)
3. **Explore Controllers**: Check `src/Controller/` for application logic
4. **Review Entities**: Check `src/Entity/` for database structure
5. **Check Templates**: Review `templates/` for frontend code

## 🔧 Common Tasks

### Reset Database to Fresh State

```bash
# Drop all tables
php bin/console doctrine:database:drop --force

# Recreate database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Load fixtures
php bin/console doctrine:fixtures:load --no-interaction
```

### View Database Tables

```bash
# List all tables
php bin/console doctrine:query:sql "SHOW TABLES;"

# Count users
php bin/console doctrine:query:sql "SELECT COUNT(*) as user_count FROM users;"

# List all users with roles
php bin/console doctrine:query:sql "SELECT id, username, email FROM users LIMIT 10;"
```

### Check Migrations Status

```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:list
```

### Generate New Migration

```bash
php bin/console doctrine:migrations:generate
```

## 📱 Browser Compatibility

Tested and working on:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 💡 Tips

1. **Always clear cache after updates**: `php bin/console cache:clear`
2. **Use fixtures for testing**: `doctrine:fixtures:load`
3. **Check logs**: `tail -f var/log/dev.log`
4. **Debug database**: Use `doctrine:query:sql` command
5. **Development mode**: `.env` already set to dev mode

## ✨ You're Ready!

Your EYA installation is complete. Start the development server and begin exploring the application.

```bash
php -S 127.0.0.1:8001 -t public
# Open http://127.0.0.1:8001 in your browser
```

**Happy coding!** 🚀

---

**Last Updated**: February 7, 2026
**Compatibility**: Symfony 6.4, PHP 8.2+, MySQL 8.0+
