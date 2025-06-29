#!/bin/bash

echo "🚀 Setting up Laravel 12 Advanced Ticketing System..."

# Install composer dependencies
echo "📦 Installing Composer dependencies..."
composer install

# Install npm dependencies
echo "📦 Installing NPM dependencies..."
npm install

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Create SQLite database if it doesn't exist
echo "🗄️ Setting up database..."
touch database/database.sqlite

# Clear any cached configurations
echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Seed roles and permissions
echo "👥 Seeding roles and permissions..."
php artisan db:seed --class=RolePermissionSeeder --force

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Build assets
echo "🎨 Building frontend assets..."
npm run build

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✅ Setup complete!"
echo ""
echo "🎯 Default login credentials:"
echo "Super Admin: admin@soluxio.com / password"
echo "Admin: admin.user@soluxio.com / password"
echo "Manager: manager@soluxio.com / password"
echo "Technician: tech@soluxio.com / password"
echo "User: user@soluxio.com / password"
echo ""
echo "🚀 Start the development server with: composer dev"
echo "📊 Access Manager Dashboard at: /manager/dashboard"
echo "🎫 Access Tickets at: /tickets"