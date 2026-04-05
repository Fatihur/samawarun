# Samawa Run - Running Community Platform

## Project Snapshot

Samawa Run is a running event management platform for the Sumbawa, Indonesia running community. Built with **Laravel 11 + Livewire + Tailwind CSS**, it handles event registration, BIB generation, certificates, race timing, and admin dashboard.

**Architecture:** MVC with Livewire reactive components
**Language:** Indonesian (Bahasa Indonesia) - all user-facing text is Indonesian
**Database:** SQLite (development), supports MySQL/PostgreSQL

## Quick Start Commands

```bash
# Install dependencies
composer install
npm install

# Database setup
php artisan migrate
php artisan db:seed

# Development
php artisan serve
npm run dev

# Build for production
npm run build

# Run tests
php artisan test
```

## Universal Conventions

- **PHP 8.2+ features**: Use typed properties, match expressions, readonly classes
- **Code style**: PSR-12 compliant (4 spaces, no trailing whitespace)
- **Routes**: Use named routes with `route('name')` helper
- **Views**: Use `@extends('layouts.xxx')` and `@section('content')`
- **Icons**: Use `<x-heroicon-s-xxx />` components from blade-ui-kit
- **Never commit**: `.env`, `storage/fonts/*`, uploaded images

## Security & Secrets

- **Never commit** API keys (OpenAI), database credentials, or mail passwords
- Admin routes protected by `auth` + `admin` middleware (checks `is_admin` flag)
- Payment URLs use secure tokens with expiration
- Participant data contains PII - always sanitize exports

## Documentation

- **PRD**: [PRD.md](./PRD.md) - Product Requirements Document (Indonesian)
- **Roadmap**: [DEVELOPMENT_ROADMAP.md](./DEVELOPMENT_ROADMAP.md) - Feature roadmap

## JIT Index

### Application Code
- **Models**: `app/Models/` → [see app/AGENTS.md](app/AGENTS.md)
- **Controllers**: `app/Http/Controllers/` → [see app/AGENTS.md](app/AGENTS.md)
- **Services**: `app/Services/` → [see app/AGENTS.md](app/AGENTS.md)

### Frontend
- **Views**: `resources/views/` → [see resources/views/AGENTS.md](resources/views/AGENTS.md)
- **Assets**: `resources/css/`, `resources/js/`
- **Tailwind config**: `tailwind.config.js`

### Database
- **Migrations**: `database/migrations/` → [see database/AGENTS.md](database/AGENTS.md)
- **Seeders**: `database/seeders/`
- **Factories**: `database/factories/`

### Configuration
- **App config**: `config/` → [see config/AGENTS.md](config/AGENTS.md)
- **Environment**: `.env` (copy from `.env.example`)

### Routes
- **Web routes**: `routes/web.php` → [see routes/AGENTS.md](routes/AGENTS.md)
- **Console**: `routes/console.php`

## Quick Find Commands

```bash
# Find a model
rg -n "class.*ModelName" app/Models/

# Find a controller
rg -n "class.*Controller" app/Http/Controllers/

# Find a view
find resources/views -name "*.blade.php" | grep viewname

# Find a migration
rg -n "Schema::create.*table_name" database/migrations/

# Search all routes
rg -n "Route::(get|post|put|delete)" routes/
```

## Definition of Done

Before creating a PR:
- [ ] `php artisan test` passes
- [ ] No PHP syntax errors: `php -l app/`
- [ ] No obvious security issues (sanitized inputs, no raw queries)
- [ ] Indonesian language for user-facing text
- [ ] Follow existing patterns (see AGENTS.md in respective directories)
