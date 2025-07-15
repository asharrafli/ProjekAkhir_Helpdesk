# Manager Dashboard Troubleshooting Guide

## Common Issues and Solutions

### 1. Dashboard Not Showing / Permission Denied

**Symptoms:**
- Can't access `/manager/dashboard` route
- Getting permission denied errors
- Manager Dashboard link not visible in navigation

**Solution:**
1. Check user permissions:
   ```bash
   php artisan tinker
   ```
   ```php
   $user = User::find(1); // Replace with your user ID
   $user->permissions; // Check permissions
   $user->hasPermissionTo('view-manager-dashboard'); // Should return true
   ```

2. Assign manager role to user:
   ```php
   $user = User::where('email', 'your-email@example.com')->first();
   $user->assignRole('manager');
   ```

3. Or give permission directly:
   ```php
   $user->givePermissionTo('view-manager-dashboard');
   ```

### 2. Database Connection Issues

**Symptoms:**
- Database connection errors
- Migration issues
- No data showing on dashboard

**Solution:**
1. Check your `.env` file has correct database configuration:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=/full/path/to/database/database.sqlite
   ```

2. Create SQLite database file:
   ```bash
   touch database/database.sqlite
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

4. Seed the database:
   ```bash
   php artisan db:seed --class=RolePermissionSeeder
   ```

### 3. Dashboard Shows No Data

**Symptoms:**
- Dashboard loads but shows empty charts
- No tickets, users, or statistics
- Loading spinners never stop

**Solution:**
1. Check if you have sample data:
   ```bash
   php artisan tinker
   ```
   ```php
   App\Models\Tickets::count(); // Should return > 0
   App\Models\User::count(); // Should return > 0
   App\Models\TicketCategory::count(); // Should return > 0
   ```

2. Create sample data using the setup script:
   ```bash
   chmod +x setup-dashboard.sh
   ./setup-dashboard.sh
   ```

3. Or manually create sample tickets:
   ```php
   // In tinker
   $category = App\Models\TicketCategory::create(['name' => 'Test', 'description' => 'Test category']);
   $user = App\Models\User::first();
   
   App\Models\Tickets::create([
       'user_id' => $user->id,
       'category_id' => $category->id,
       'title' => 'Test Ticket',
       'title_ticket' => 'Test Ticket',
       'description_ticket' => 'Test description',
       'status' => 'open',
       'priority' => 'medium',
       'due_date' => now()->addDays(7),
       'last_activity_at' => now(),
   ]);
   ```

### 4. JavaScript/Chart Loading Issues

**Symptoms:**
- Charts not rendering
- JavaScript errors in console
- AJAX requests failing

**Solution:**
1. Check browser console for JavaScript errors

2. Verify Chart.js is loading:
   ```html
   <!-- Should be in dashboard view -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   ```

3. Check if chart data endpoint is working:
   ```
   GET /manager/dashboard/chart-data?type=ticket_trends
   ```

4. Clear application cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

### 5. Role/Permission System Not Working

**Symptoms:**
- Spatie Permission package errors
- Role assignment not working
- Permission checks failing

**Solution:**
1. Check if Spatie Permission is installed:
   ```bash
   composer show spatie/laravel-permission
   ```

2. Publish and run Permission migrations:
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

3. Clear permission cache:
   ```bash
   php artisan permission:cache-reset
   ```

### 6. Route Not Found

**Symptoms:**
- 404 errors on manager dashboard routes
- Route list doesn't show manager routes

**Solution:**
1. Check if routes are registered:
   ```bash
   php artisan route:list | grep manager
   ```

2. Verify route names match:
   ```
   manager.dashboard
   manager.dashboard.chart-data
   ```

3. Check middleware permissions in routes/web.php

## Quick Setup Commands

### Fresh Installation
```bash
# Clone/download the project
composer install
cp .env.example .env # Edit database settings
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

### Reset Dashboard
```bash
php artisan migrate:fresh --seed
php artisan permission:cache-reset
```

### Test User Creation
```bash
php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'Test Manager',
    'email' => 'test@manager.com',
    'password' => bcrypt('password'),
    'is_admin' => false,
    'status' => 'active',
]);
$user->assignRole('manager');
```

## Default Credentials

After running the seeder, you can use these credentials:

- **Super Admin**: admin@soluxio.com / password
- **Manager**: manager@soluxio.com / password  
- **Technician**: tech@soluxio.com / password
- **User**: user@soluxio.com / password

## Need More Help?

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true` in `.env`
3. Check database queries: Enable query logging in AppServiceProvider
4. Verify all dependencies are installed: `composer install`
5. Check if all migrations ran: `php artisan migrate:status`