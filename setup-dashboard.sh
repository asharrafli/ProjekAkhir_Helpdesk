#!/bin/bash

# Dashboard Setup Script
# This script sets up the database and creates necessary data for the manager dashboard

echo "=== Setting up Manager Dashboard ==="

# Check if SQLite database exists
if [ ! -f "database/database.sqlite" ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

# Clear application cache
echo "Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database with roles and permissions
echo "Seeding database with roles and permissions..."
php artisan db:seed --class=RolePermissionSeeder

# Create sample tickets for demonstration
echo "Creating sample tickets..."
php artisan tinker --execute="
use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;

// Create sample category if it doesn't exist
if (!TicketCategory::exists()) {
    \$category = TicketCategory::create(['name' => 'General Support', 'description' => 'General support tickets']);
    
    // Create sample tickets
    \$users = User::all();
    \$technicians = User::role('technician')->get();
    
    if (\$users->count() > 0) {
        for (\$i = 0; \$i < 20; \$i++) {
            \$ticket = Tickets::create([
                'user_id' => \$users->random()->id,
                'category_id' => \$category->id,
                'title' => 'Sample Ticket ' . (\$i + 1),
                'title_ticket' => 'Sample Ticket ' . (\$i + 1),
                'description_ticket' => 'This is a sample ticket for demonstration purposes.',
                'status' => collect(['open', 'in_progress', 'closed', 'resolved'])->random(),
                'priority' => collect(['low', 'medium', 'high', 'urgent'])->random(),
                'due_date' => now()->addDays(rand(1, 30)),
                'last_activity_at' => now()->subDays(rand(0, 10)),
            ]);
            
            // Assign some tickets to technicians
            if (\$technicians->count() > 0 && rand(0, 1)) {
                \$ticket->update(['assigned_to' => \$technicians->random()->id]);
            }
            
            // Set resolution data for closed tickets
            if (in_array(\$ticket->status, ['closed', 'resolved'])) {
                \$ticket->update([
                    'resolved_at' => now()->subDays(rand(0, 5)),
                    'response_time_minutes' => rand(30, 1440), // 30 minutes to 24 hours
                ]);
            }
        }
    }
}
"

# Clear cache again
echo "Clearing cache after setup..."
php artisan config:clear
php artisan cache:clear

echo "=== Setup complete! ==="
echo ""
echo "Manager Dashboard setup is complete. You can now:"
echo "1. Login with manager credentials:"
echo "   Email: manager@soluxio.com"
echo "   Password: password"
echo ""
echo "2. Access the Manager Dashboard at: /manager/dashboard"
echo ""
echo "3. Start the development server with: composer dev"
echo ""
echo "If you don't see the dashboard, make sure you have the 'view-manager-dashboard' permission."