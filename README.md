

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or compatible

### Required PHP Extensions
- `ext-ctype`
- `ext-iconv`
- `ext-PDO`
- `ext-pdo_mysql`

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/eyaarg/MedTime.git eya
cd eya
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the default environment file and update database credentials:

```bash
# The .env file is already configured with default values
# Update DATABASE_URL if needed in .env
```

Default database configuration:
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
```

### 4. Create Database

```bash
php bin/console doctrine:database:create
```

### 5. Run Migrations

Apply all database migrations:

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Load Demo Data (Optional)

Load demo users and fixtures:

```bash
php bin/console doctrine:fixtures:load --no-interaction
```


### Development Server

Start the built-in PHP server:

```bash
php -S 127.0.0.1:8001 -t public
```

Access the application at: `http://127.0.0.1:8001`


---

**Last Updated**: February 7, 2026
**Version**: 1.0.0
**Status**: Production Ready
