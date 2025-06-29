# 🎫 Laravel 12 Advanced Ticketing System

A comprehensive ticketing system with advanced features including real-time notifications, role-based access control, manager analytics dashboard, and PDF reporting.

## 🚀 Quick Setup

### Option 1: Automated Setup (Recommended)
```bash
./setup.sh
```

### Option 2: Manual Setup
```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database setup
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder

# 4. Storage and assets
php artisan storage:link
npm run build

# 5. Start development server
composer dev
```

## 🔐 Default Login Credentials

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Super Admin | admin@soluxio.com | password | Full system access |
| Admin | admin.user@soluxio.com | password | Administrative access |
| Manager | manager@soluxio.com | password | Read-only analytics dashboard |
| Technician | tech@soluxio.com | password | Ticket management |
| User | user@soluxio.com | password | Create and view own tickets |

## 🎯 Key Features

### 1. **Enhanced Ticket System**
- ✅ Advanced status management (Open, In Progress, Assigned, Closed, Resolved, Pending, Escalated)
- ✅ Priority levels (Low, Medium, High, Critical, Urgent)
- ✅ Auto-generated ticket numbers (SLX{YYYYMMDD}-XXXX)
- ✅ File attachment support (Images, PDFs, Documents)
- ✅ Ticket assignment and claiming
- ✅ Advanced search and filtering
- ✅ Bulk operations (assign, status change, priority change, delete)

### 2. **Role-Based Access Control**
- ✅ 5 user roles with granular permissions
- ✅ 25+ specific permissions for different operations
- ✅ Middleware protection for all routes
- ✅ Role-based dashboard access

### 3. **Manager/Executive Dashboard**
- ✅ Interactive performance charts (Chart.js)
- ✅ Ticket trends analysis
- ✅ Technician performance metrics
- ✅ Category and priority distribution
- ✅ Resolution time analytics
- ✅ Customizable date ranges
- ✅ PDF report export

### 4. **Real-Time Notification System**
- ✅ Laravel Echo + Pusher integration
- ✅ Browser push notifications
- ✅ In-app toast notifications
- ✅ Real-time updates for ticket events
- ✅ Notification center with mark as read

### 5. **Activity Logging**
- ✅ Comprehensive audit trail
- ✅ User action tracking
- ✅ Ticket activity timeline
- ✅ Automatic logging for all operations

### 6. **PDF Report Generation**
- ✅ Manager dashboard reports
- ✅ Filtered ticket reports
- ✅ Technician performance reports
- ✅ Custom date range exports

## 📁 File Structure

```
├── app/
│   ├── Events/                    # Broadcasting events
│   ├── Http/Controllers/Admin/    # Admin controllers
│   ├── Models/                    # Enhanced models
│   ├── Notifications/             # Notification classes
│   └── ...
├── database/
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Role and permission seeders
├── resources/
│   ├── js/                        # Frontend JavaScript
│   └── views/                     # Blade templates
└── routes/web.php                 # Application routes
```

## 🔧 Configuration

### Broadcasting (Real-time Notifications)
1. Set up Pusher account at https://pusher.com
2. Add to `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Email Notifications
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

## 🎪 Usage Guide

### For Users
1. **Login** with your credentials
2. **Create Tickets** via `/tickets/create`
3. **Track Progress** in `/tickets`
4. **Receive Notifications** for updates

### For Technicians  
1. **View Available Tickets** in `/tickets`
2. **Claim Tickets** using the "Claim" button
3. **Update Status** and add resolution notes
4. **Upload Attachments** for evidence/documentation

### For Managers
1. **Access Dashboard** at `/manager/dashboard`
2. **View Analytics** with interactive charts
3. **Filter by Date Range** for specific periods
4. **Export Reports** to PDF for evaluation
5. **Monitor Performance** metrics in real-time

### For Admins
1. **Manage Users** in `/admin/users`
2. **Configure Roles** in `/admin/roles`
3. **Bulk Operations** on tickets
4. **System Configuration** and settings

## 🛠️ Troubleshooting

### Common Issues

1. **Migration Errors**
   ```bash
   php artisan migrate:reset
   php artisan migrate
   php artisan db:seed --class=RolePermissionSeeder
   ```

2. **Permission Errors**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

3. **Asset Build Issues**
   ```bash
   npm run build
   php artisan view:clear
   ```

4. **Real-time Not Working**
   - Check Pusher credentials in `.env`
   - Verify JavaScript console for errors
   - Ensure WebSocket connection is established

## 📊 Database Schema

### Key Tables
- `tickets` - Main ticket data with enhanced fields
- `ticket_categories` - Ticket categorization
- `ticket_subcategories` - Sub-category hierarchy  
- `ticket_attachments` - File management
- `users` - User management with roles
- `roles` - Spatie roles and permissions
- `activity_log` - Audit trail
- `notifications` - Laravel notifications

## 🔐 Security Features

- ✅ CSRF protection on all forms
- ✅ Role-based route middleware
- ✅ Permission-based access control
- ✅ File upload validation and security
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Secure file storage

## 🚀 Performance Optimizations

- ✅ Database indexes on frequently queried columns
- ✅ Eager loading for relationships
- ✅ Query optimization for dashboard charts
- ✅ Efficient notification queuing
- ✅ Asset compression and caching

## 📈 Scalability Considerations

- Queue jobs for heavy operations
- Database connection pooling
- Redis caching for frequently accessed data
- CDN for file attachments
- Load balancing for multiple instances

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Developed with ❤️ using Laravel 12, Livewire, and modern web technologies.**