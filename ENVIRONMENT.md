# Environment Configuration Guide

Reference documentation for all environment variables and configuration options used in the EYA project.

## 📄 Environment Files

The project uses standard Symfony environment file hierarchy:

1. `.env` - Default values (committed to repository)
2. `.env.local` - Local overrides (NOT committed, machine-specific)
3. `.env.dev` - Development environment defaults
4. `.env.test` - Testing environment defaults
5. `.env.prod` - Production environment defaults

**Priority Order**: Later files override earlier ones.

## 🔧 Core Configuration

### APP_ENV

**Purpose**: Defines the application environment
**Type**: String
**Possible Values**: `dev`, `test`, `prod`
**Default**: `dev`
**Location**: `.env`

```dotenv
APP_ENV=dev
```

**When to Change**:
- `dev` - Local development (verbose errors, debug toolbar)
- `test` - Running tests (isolated environment)
- `prod` - Production server (optimized, minimal logs)

### APP_SECRET

**Purpose**: Secret key for security features (CSRF tokens, encryption)
**Type**: String
**Default**: `1b770c4e5d617f0db992c08bd4bdcf26`
**Location**: `.env`
**Security**: ⚠️ IMPORTANT: Change in production!

```dotenv
APP_SECRET=1b770c4e5d617f0db992c08bd4bdcf26
```

**How to Generate New Secret**:
```bash
# Generate a random secret
php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"

# Or use Symfony
php bin/console generate-secret
```

## 🗄️ Database Configuration

### DATABASE_URL

**Purpose**: MySQL database connection string
**Type**: String (DSN format)
**Default**: `mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4`
**Location**: `.env`

#### Format Explanation

```
mysql://username:password@host:port/database_name?serverVersion=VERSION&charset=utf8mb4
```

#### Parameters

| Parameter | Description | Example |
|-----------|-------------|---------|
| `mysql://` | Database driver | (MySQL, use `postgresql://` for PostgreSQL) |
| `username` | Database user | `root` |
| `password` | Database password | (empty for default XAMPP) |
| `host` | Database server | `127.0.0.1` or `localhost` |
| `port` | Database port | `3306` (MySQL default) |
| `database_name` | Database to use | `mediplatform` |
| `serverVersion` | MySQL version | `8.0` or `5.7` |
| `charset` | Character encoding | `utf8mb4` (recommended) |

#### Configuration Examples

**XAMPP (Default - No Password)**:
```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
```

**XAMPP (With Password)**:
```dotenv
DATABASE_URL="mysql://root:MyPassword@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
```

**MariaDB (XAMPP)**:
```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

**Remote Server**:
```dotenv
DATABASE_URL="mysql://dbuser:dbpass@192.168.1.100:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
```

**PostgreSQL**:
```dotenv
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/mediplatform?serverVersion=16&charset=utf8"
```

**SQLite (Development)**:
```dotenv
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
```

### Doctrine Configuration

Doctrine is further configured in `config/packages/doctrine.yaml`:

```yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'
    
  orm:
    auto_generate_proxy_classes: true
    naming_strategy: doctrine.orm.naming_strategy.underscore
    metadata_cache_driver:
      type: pool
      pool: doctrine.system_cache_pool
```

## 📧 Mailer Configuration

### MAILER_DSN

**Purpose**: Email delivery configuration
**Type**: String (DSN format)
**Default**: `null://null`
**Location**: `.env`

**Format**:
```
protocol://user:password@host:port?query=value
```

#### Common Configurations

**Disabled (Development)**:
```dotenv
MAILER_DSN=null://null
```

**SMTP Server**:
```dotenv
MAILER_DSN=smtp://user:password@smtp.gmail.com:587?encryption=tls&auth_mode=login
```

**Gmail**:
```dotenv
MAILER_DSN=smtp://your-email@gmail.com:your-app-password@smtp.gmail.com:587?encryption=tls
```

**Local Sendmail**:
```dotenv
MAILER_DSN=sendmail://default
```

**AWS SES**:
```dotenv
MAILER_DSN=ses://ACCESS_KEY:SECRET_KEY@default?region=us-east-1
```

## 📬 Messenger (Message Queue)

### MESSENGER_TRANSPORT_DSN

**Purpose**: Message broker configuration for async tasks
**Type**: String
**Default**: `doctrine://default?auto_setup=0`
**Location**: `.env`

#### Configuration Examples

**Doctrine (Database-based)**:
```dotenv
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

**Redis**:
```dotenv
MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```

**RabbitMQ**:
```dotenv
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
```

**In-Memory (Development/Testing)**:
```dotenv
MESSENGER_TRANSPORT_DSN=in-memory://
```

## 🔐 Security Configuration

Security is configured in `config/packages/security.yaml`. Key environment-dependent settings:

### Password Encoding

Default: Uses `auto` strategy (argon2id)

```yaml
security:
  password_hashers:
    Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
```

### Firewall Configuration

Main firewall uses session-based authentication:

```yaml
security:
  firewalls:
    dev:
      pattern: ^/(_(profiler|wdt)|css|images|js)/
      security: false
    main:
      stateless: false
      lazy: true
      entry_point: form_login
      form_login:
        login_path: app_login
        check_path: app_login
```

## 🎨 Asset Configuration

### Asset Paths

Configured in `config/packages/asset.yaml`:

```yaml
framework:
  assets:
    json_manifest_path: '%kernel.project_dir%/public/assets/manifest.json'
```

**Asset Mapping** handled by AssetMapper:
- Source: `assets/` directory
- Output: `public/assets/` (hashed filenames)
- Manifest: `public/assets/manifest.json`

### CDN Assets

For external libraries, configuration in templates uses CDN URLs:
- Bootstrap: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/...`
- FontAwesome: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/...`
- Other vendors loaded similarly

See `templates/base.html.twig` for CDN configuration.

## 🔍 Debugging Configuration

### Debug Mode

Controlled by `APP_DEBUG` environment variable (derived from `APP_ENV`):
- `dev` = Debug enabled (verbose error pages, debug toolbar)
- `prod` = Debug disabled (minimal output, security-focused)

### Logging

Configured in `config/packages/monolog.yaml`:

**Development**:
```yaml
monolog:
  handlers:
    main:
      type: stream
      path: '%kernel.project_dir%/var/log/%kernel.environment%.log'
      level: debug
```

**Production**:
```yaml
monolog:
  handlers:
    main:
      type: fingers_crossed
      action_level: error
      handler: nested
    nested:
      type: stream
      path: '%kernel.project_dir%/var/log/%kernel.environment%.log'
      level: error
```

**Log Locations**:
- Development: `var/log/dev.log`
- Production: `var/log/prod.log`
- Test: `var/log/test.log`

## 📦 Symfony-Specific Variables

### TRUSTED_PROXIES

If behind a proxy or load balancer:

```dotenv
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
TRUSTED_HOSTS=localhost,example.com
```

### KERNEL_CLASS

Default application kernel (usually doesn't need changing):

```dotenv
KERNEL_CLASS=App\Kernel
```

## 🏗️ Doctrine-Specific Variables

### DATABASE_URL_REPLICA

For read replicas (optional):

```dotenv
DATABASE_URL_REPLICA="mysql://reader:password@replica.example.com:3306/mediplatform"
```

### DOCTRINE_MIGRATIONS_VERSION_TABLE_NAME

Custom migrations table name (optional):

```dotenv
DOCTRINE_MIGRATIONS_VERSION_TABLE_NAME=doctrine_migrations_version
```

## 📝 Complete .env Example

```dotenv
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=1b770c4e5d617f0db992c08bd4bdcf26
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://root:@127.0.0.1:3306/mediplatform?serverVersion=8.0&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=null://null
###< symfony/mailer ###
```

## 🔒 Production Considerations

### Environment-Specific Configuration

**Production .env** (`/etc/eya/.env.prod.local`):

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=your-secure-random-secret-here

DATABASE_URL="mysql://prod_user:SecurePassword@prod-db.example.com:3306/mediplatform_prod?serverVersion=8.0&charset=utf8mb4"

MAILER_DSN=smtp://noreply@example.com:password@smtp.example.com:587?encryption=tls

MESSENGER_TRANSPORT_DSN=redis://redis.example.com:6379/messages

TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12
TRUSTED_HOSTS=example.com,www.example.com
```

### Security Best Practices

1. ✅ **Change APP_SECRET** in production
2. ✅ **Use environment variables** for sensitive data
3. ✅ **Never commit** `.env.local` or `.env.*.local`
4. ✅ **Use strong database passwords** in production
5. ✅ **Enable HTTPS** in production
6. ✅ **Set APP_ENV=prod** to disable debug mode
7. ✅ **Configure proper logging** with error handlers
8. ✅ **Use strong mail credentials** for MAILER_DSN

## 🔄 Environment Variables in Code

Access environment variables in PHP code:

```php
// Using getenv()
$appEnv = getenv('APP_ENV');

// Using $_ENV
$databaseUrl = $_ENV['DATABASE_URL'];

// Using Symfony's environment variable processor
// In service configuration or dependency injection
$mailerDsn = $this->getParameter('mailer_dsn');
```

## 📚 Related Documentation

- [symfony.com - Configuration](https://symfony.com/doc/current/configuration.html)
- [symfony.com - Environment Variables](https://symfony.com/doc/current/configuration/env_var_processors.html)
- [README.md](README.md) - Project overview
- [INSTALLATION.md](INSTALLATION.md) - Setup guide

---

**Last Updated**: February 7, 2026
**Version**: 1.0.0
