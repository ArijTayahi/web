# Medilab Demo Credentials & Guide

Database has been seeded with demo users. All demo accounts use passwords set during seeding.

## Test Accounts

### Super Administrator
- **Username:** `superadmin`
- **Email:** `superadmin@medilab.local`
- **Password:** `SuperAdmin@2026`
- **Role:** Super Admin
- **Access:** Full admin panel + system management

### Administrator  
- **Username:** `admin`
- **Email:** `admin@medilab.local`
- **Password:** `Admin@2026`
- **Role:** Admin
- **Access:** Admin dashboard

---

## Physician (Doctor) Accounts

All physicians have certified status and license codes.

| Username | Email | Password | License Code |
|----------|-------|----------|--------------|
| dr.smith | dr.smith@medilab.local | DoctorSmith@2026 | LIC-001-SMITH |
| dr.johnson | dr.johnson@medilab.local | DoctorJohnson@2026 | LIC-002-JOHNSON |
| dr.williams | dr.williams@medilab.local | DoctorWilliams@2026 | LIC-003-WILLIAMS |
| dr.brown | dr.brown@medilab.local | DoctorBrown@2026 | LIC-004-BROWN |

**Default Password Pattern:** `Doctor{LastName}@2026`

---

## Patient Accounts

All patients are assigned to different regions for demo purposes.

| Username | Email | Password | Region |
|----------|-------|----------|--------|
| patient1 | patient1@medilab.local | Patient@2026 | North Region |
| patient2 | patient2@medilab.local | Patient@2026 | South Region |
| patient3 | patient3@medilab.local | Patient@2026 | East Region |
| patient4 | patient4@medilab.local | Patient@2026 | West Region |
| patient5 | patient5@medilab.local | Patient@2026 | Central Region |
| patient6 | patient6@medilab.local | Patient@2026 | North Region |
| patient7 | patient7@medilab.local | Patient@2026 | South Region |
| patient8 | patient8@medilab.local | Patient@2026 | East Region |

**Default Password:** `Patient@2026`

---

## How to Test the Application

### 1. Login as Patient
1. Go to http://127.0.0.1:8001/login
2. Username: `patient1`
3. Password: `Patient@2026`
4. You'll be redirected to patient dashboard

### 2. Login as Physician
1. Go to http://127.0.0.1:8001/login
2. Username: `dr.smith`
3. Password: `DoctorSmith@2026`
4. You'll be redirected to physician dashboard

### 3. Login as Admin
1. Go to http://127.0.0.1:8001/login
2. Username: `admin`
3. Password: `Admin@2026`
4. You'll have access to admin panel

### 4. Login as Super Admin
1. Go to http://127.0.0.1:8001/login
2. Username: `superadmin`
3. Password: `SuperAdmin@2026`
4. Full system access

---

## Demo Assets/Images

The application uses the following images from `assets/img/`:

### Doctor Images
- `doctors/doctors-1.jpg` - Dr. Smith
- `doctors/doctors-2.jpg` - Dr. Johnson
- `doctors/doctors-3.jpg` - Dr. Williams
- `doctors/doctors-4.jpg` - Dr. Brown

### Department/Category Images
- `departments-1.jpg` through `departments-5.jpg` - Medical departments

### Gallery Images
- `gallery/gallery-1.jpg` through `gallery/gallery-8.jpg` - Healthcare services showcase

### Homepage Images
- `hero-bg.jpg` - Hero section background
- `about.jpg` - About section image
- `testimonials/testimonials-1.jpg` through `testimonials-5.jpg` - Patient testimonials

---

## Key Features to Test

### As a Patient
- ✅ View physician profiles
- ✅ Find doctors by specialty
- ✅ Schedule appointments
- ✅ View appointment history
- ✅ Edit profile information
- ✅ Manage medical records

### As a Physician
- ✅ View patient list
- ✅ Manage appointments
- ✅ Upload certifications/documents
- ✅ Update profile
- ✅ View patient medical history

### As Admin
- ✅ User management (create/edit/delete users)
- ✅ View all users and roles
- ✅ System statistics
- ✅ Audit logs
- ✅ Export user data

---

## Database Schema

The demo data uses these core tables:
- `users` - All user accounts
- `roles` - User role definitions
- `user_roles` - Many-to-many relationship between users and roles
- `doctors` - Physician profiles linked to users
- `patients` - Patient profiles linked to users
- `doctor_documents` - Document uploads for physician verification

---

## Reset/Reload Demo Data

To reload demo data (wipes and recreates database):

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Server Status

**PHP Development Server:** http://127.0.0.1:8001

All CSS and JavaScript assets are loading correctly via AssetMapper with CDN fallbacks for vendor libraries.

---

## Contact & Support

For testing issues, check:
1. Browser console for JavaScript errors
2. PHP error logs in `var/log/`
3. Database connectivity in `.env` file

