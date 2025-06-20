# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 ticketing system application with Livewire components for dynamic UI interactions. The system allows users to create and manage support tickets with different categories, priorities, and statuses.

## Architecture

- **Backend**: Laravel 12 framework with PHP 8.2+
- **Frontend**: Blade templates with Bootstrap 5 and Tailwind CSS
- **Database**: SQLite (for development)
- **Real-time UI**: Livewire 3.6 for dynamic components
- **Testing**: Pest PHP framework
- **Build Tools**: Vite for asset compilation

### Key Models and Relationships
- `User` model handles authentication and user management
- `Tickets` model with auto-generated ticket numbers (format: SLX{YYYYMMDD}-XXXX)
- `TicketCategory` model for ticket categorization
- Tickets belong to users (customers) and can be assigned to technicians (also users)

### Ticket System Schema
- Tickets have statuses: `open`, `in_progress`, `closed`
- Priority levels: `low`, `medium`, `high`, `urgent`
- Auto-generated unique ticket numbers using boot method in Tickets model

## Development Commands

### Setup and Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Generate application key (if .env doesn't exist)
php artisan key:generate

# Run database migrations
php artisan migrate

# Create SQLite database file (if needed)
touch database/database.sqlite
```

### Development Server
```bash
# Start all development services (Laravel server, queue worker, Vite)
composer dev

# Or start individually:
php artisan serve          # Laravel development server
php artisan queue:listen   # Queue worker
npm run dev                # Vite development server
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run Pest tests directly
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/ExampleTest.php
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code formatting without fixing
./vendor/bin/pint --test
```

### Asset Building
```bash
# Build assets for development
npm run dev

# Build assets for production
npm run build
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m
```

### Livewire Components
```bash
# Create new Livewire component
php artisan make:livewire ComponentName

# Create Livewire component in subdirectory
php artisan make:livewire Folder/ComponentName
```

## File Structure Notes

- Livewire components are in `app/Livewire/` with corresponding views in `resources/views/livewire/`
- Main layout is in `resources/views/layouts/app.blade.php`
- Authentication routes are handled by Laravel UI package
- Frontend assets use both Bootstrap 5 and Tailwind CSS (mixed approach)
- SQLite database file is in `database/database.sqlite`

## Authentication

The application uses Laravel's built-in authentication system with Laravel UI package. Auth routes are automatically registered via `Auth::routes()` in `routes/web.php`.