# Feeyangu School Management System

A **multi-tenant SaaS** school management platform for Kenyan schools (CBC, 8-4-4, Cambridge).

## Tech Stack
- **Backend:** Laravel 11, PHP 8.2, PostgreSQL, Redis, Sanctum, Spatie Permission
- **Frontend:** Inertia.js, React 18, TypeScript, Tailwind CSS

## Quick Start
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan db:seed
npm run build && php artisan serve
```

## Default Credentials
| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@feeyangu.com | password |
| School Admin | admin@demoschool.ac.ke | password |