# EYA - Medical Platform

A comprehensive Symfony-based medical management system with patient and physician collaboration features.

## 🎯 Project Overview

EYA is a modern web application built with Symfony 6.4 designed to streamline medical practice management. The platform supports multiple user roles (super admin, admin, doctors, patients) with specialized features for each role.

### Key Features

- **User Management**: Role-based access control (RBAC) with 5 predefined roles
- **Doctor Profiles**: Complete physician information with license management
- **Patient Profiles**: Patient medical records and document management
- **Document Management**: Secure storage of medical documents
- **Responsive Design**: Bootstrap 5.3 based UI with modern components
- **Asset Management**: Optimized asset compilation with AssetMapper
- **Database Migration**: Doctrine ORM with automated migrations

## 🛠️ Technology Stack

### Backend
- **Framework**: Symfony 6.4.*
- **Language**: PHP 8.2+
- **ORM**: Doctrine ORM 3.6
- **Database**: MySQL 8.0
- **Migrations**: Doctrine Migrations 3.9

### Frontend
- **CSS Framework**: Bootstrap 5.3.3
- **Icons**: FontAwesome 6.5.2
- **Animations**: AOS (Animate On Scroll) 2.3.4
- **Lightbox**: Glightbox 3.3.0
- **Carousel**: Swiper 11.1.4
- **Asset Bundling**: Symfony AssetMapper

### Development Tools
- **Package Manager**: Composer
- **Version Control**: Git
- **Testing**: PHPUnit
- **Fixtures**: Doctrine Fixtures Bundle

## 📋 System Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or compatible
- **Composer**: Latest version
- **Node.js**: Optional (for asset compilation)

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

This creates:
- 1 super admin user
- 1 admin user
- 4 doctor users
- 8 patient users
- Related doctor and patient records

### 7. Clear Cache

```bash
php bin/console cache:clear
```

## 🏃 Running the Application

### Development Server

Start the built-in PHP server:

```bash
php -S 127.0.0.1:8001 -t public
```

Access the application at: `http://127.0.0.1:8001`

### Apache/XAMPP

If using XAMPP:
1. Place project in `C:\xampp\htdocs\eya`
2. Enable mod_rewrite in Apache
3. Access via: `http://localhost/eya`

## 👤 Demo Credentials

After loading fixtures, use these credentials:

### Super Admin
- **Email/Username**: superadmin
- **Password**: SuperAdmin@2026

### Admin
- **Email/Username**: admin
- **Password**: Admin@2026

### Doctors
- **Usernames**: dr.smith, dr.johnson, dr.williams, dr.brown
- **Password**: Doctor@2026

### Patients
- **Usernames**: patient1, patient2, ..., patient8
- **Password**: Patient@2026

**Note**: Passwords are case-sensitive. Change passwords after first login in production.

## 📁 Project Structure

```
eya/
├── bin/
│   └── console                 # Symfony console application
├── config/
│   ├── routes.yaml            # Route definitions
│   └── packages/              # Bundle configurations
├── migrations/                # Database migrations
├── public/
│   ├── index.php              # Application entry point
│   ├── assets/                # Compiled assets (CSS, JS, images)
│   └── uploads/               # User uploads (documents, images)
├── src/
│   ├── Controller/            # Application controllers
│   ├── Entity/                # Doctrine entities
│   ├── Repository/            # Data repositories
│   ├── DataFixtures/          # Test data fixtures
│   ├── Security/              # Security handlers
│   └── Kernel.php             # Symfony kernel
├── templates/                 # Twig templates
├── assets/                    # Source assets (CSS, JS)
└── composer.json              # Project dependencies
```

## 🗄️ Database Schema

### Main Tables
- **users**: User accounts with roles
- **roles**: Role definitions (RBAC)
- **user_roles**: User-role associations
- **doctors**: Doctor profiles
- **patients**: Patient profiles
- **doctor_documents**: Document storage
- **regions**: Geographic regions
- **messenger_messages**: Message queue

See migrations/ directory for schema details.

## 🔐 Security Features

- Password hashing with Symfony security
- Role-based access control (RBAC)
- CSRF protection on forms
- SQL injection prevention via Doctrine ORM
- Secure file uploads in public/uploads/

## 📚 Key Documentation

- [INSTALLATION.md](INSTALLATION.md) - Detailed setup guide
- [DEMO_GUIDE.md](DEMO_GUIDE.md) - Demo testing instructions
- [ASSET_LOADING_DIAGNOSIS.md](ASSET_LOADING_DIAGNOSIS.md) - Asset troubleshooting
- [ENVIRONMENT.md](ENVIRONMENT.md) - Environment variables reference
- [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md) - Setup verification checklist

## 🧪 Testing

### Run Tests

```bash
php bin/phpunit
```

### Validate Assets

```bash
python scripts/check_css.py http://127.0.0.1:8001/
```

## 📦 Deployment

### To MedTime Repository

Push to the MedTime repository:

```bash
git push medtime main
```

See [PUSH_INSTRUCTIONS.txt](PUSH_INSTRUCTIONS.txt) for detailed deployment steps.

## 🐛 Troubleshooting

### Database Connection Failed
- Ensure MySQL is running
- Check DATABASE_URL in .env
- Verify credentials: root (no password) on localhost:3306

### CSS/JS Not Loading
- Run: `php bin/console cache:clear`
- Check public/assets/ directory exists
- Validate with: `python scripts/check_css.py http://127.0.0.1:8001/`

### Migration Errors
- Sync migration metadata: `php bin/console doctrine:migrations:sync-metadata-storage`
- Check migration status: `php bin/console doctrine:migrations:status`
- See [ASSET_LOADING_DIAGNOSIS.md](ASSET_LOADING_DIAGNOSIS.md) for detailed troubleshooting

### Console Commands Hang
- Ensure PHP version is 8.2+
- Try: `php bin/console -h`
- Check var/log/ for error messages

## 📝 Development Workflow

1. Create feature branch: `git checkout -b feature/your-feature`
2. Make changes and test locally
3. Commit changes: `git commit -m "Add feature description"`
4. Push to branch: `git push origin feature/your-feature`
5. Create pull request on GitHub

## 📄 License

Proprietary - All rights reserved

## 👥 Contributors

Developed and maintained by the EYA team.

## ❓ Support

For issues, questions, or suggestions:
1. Check [TROUBLESHOOTING.md](ASSET_LOADING_DIAGNOSIS.md) first
2. Review [DEMO_GUIDE.md](DEMO_GUIDE.md) for common questions
3. Check GitHub issues
4. Contact the development team

---

**Last Updated**: February 7, 2026
**Version**: 1.0.0
**Status**: Production Ready
