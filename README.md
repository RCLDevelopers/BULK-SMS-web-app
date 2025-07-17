# BULK SMS Web Application

A comprehensive SMS management system built with Laravel, Livewire, and Tailwind CSS. This application provides user management, SMS sending/receiving capabilities, and real-time statistics.

## Features

- User authentication (login/register)
- Role-based access control (Admin, User)
- Dashboard with real-time statistics
- Bulk SMS sending and receiving
- Contact management with groups
- SMS templates and scheduling
- Reporting and analytics
- Responsive design for all devices
- SMS delivery reports
- Balance and credit management
- API integration for developers

## Requirements

- PHP 8.1 or higher
- Composer 2.0 or higher
- Node.js (v16 or higher)
- NPM (v7+) or Yarn (v1.22+)
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) with mod_rewrite enabled
- PHP extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/RCLDevelopers/BULK-SMS-web-app.git
   cd BULK-SMS-web-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   ```

4. **Create and configure the environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database**
   Edit the `.env` file and update the database configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bulk_sms
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run database migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```
   This will create all necessary database tables and seed initial data including admin user.

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Set storage and cache permissions**
   ```bash
   php artisan storage:link
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

9. **Configure your web server**
   - Point your web server's document root to the `public` directory
   - Make sure mod_rewrite is enabled
   - Set proper file permissions

## Running the Application

### Development

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

2. In a new terminal, start Vite for asset compilation:
   ```bash
   npm run dev
   ```

3. Access the application at: `http://localhost:8000`

### Production

For production, make sure to:
1. Set `APP_ENV=production` in your `.env` file
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Configure your web server (Apache/Nginx) to point to the `public` directory

## Default Admin Account

A default admin account is created during database seeding:

- **Email:** admin@example.com
- **Password:** password

**Security Note:** It is strongly recommended to change these credentials immediately after your first login.

## Environment Variables

Key environment variables to configure in your `.env` file:

### Application
- `APP_NAME`: Your application name
- `APP_ENV`: Application environment (local, production)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_URL`: Application URL (e.g., https://yourdomain.com)
- `TIMEZONE`: Application timezone (default: UTC)

### Database
- `DB_*`: Database configuration

### Email
- `MAIL_*`: Email configuration for notifications

### SMS Gateway
- `SMS_DRIVER`: Choose your SMS gateway (twilio, nexmo, etc.)
- `SMS_FROM`: Default sender number/name
- `SMS_QUEUE`: Whether to queue SMS messages (true/false)

### Twilio (if using Twilio)
- `TWILIO_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_NUMBER`

### Nexmo (if using Nexmo)
- `NEXMO_KEY`
- `NEXMO_SECRET`
- `NEXMO_FROM`

### Cache & Session
- `CACHE_DRIVER`: Cache driver (file, redis, memcached)
- `SESSION_DRIVER`: Session driver (file, database, redis)
- `QUEUE_CONNECTION`: Queue connection (sync, database, redis)

## Directory Structure

```
sms-app/
├── app/                 # Application code
├── bootstrap/           # Framework bootstrap files
├── config/              # Configuration files
├── database/            # Database migrations and seeders
├── public/              # Publicly accessible files
├── resources/
│   ├── js/              # JavaScript files
│   ├── css/             # CSS files
│   └── views/           # Blade templates
├── routes/              # Application routes
├── storage/             # Storage for logs, cache, etc.
└── tests/               # Test files
```

## Security

- **Always** use HTTPS in production
- Keep all dependencies updated to their latest secure versions
- Never commit `.env` file to version control (it's in `.gitignore` by default)
- Use strong, unique passwords for all accounts
- Regularly backup your database and application files
- Set proper file permissions (folders: 755, files: 644)
- Enable CSRF protection (enabled by default in Laravel)
- Use rate limiting for API endpoints
- Implement proper input validation and output escaping

## Contributing

We welcome contributions from the community. To contribute to the BULK SMS Web Application:

1. Fork the repository
2. Create a new branch for your feature or bugfix (`git checkout -b feature/AmazingFeature`)
3. Make your changes and write tests if applicable
4. Run the test suite and ensure all tests pass
5. Commit your changes with a descriptive message (`git commit -m 'Add some AmazingFeature'`)
6. Push to your fork (`git push origin feature/AmazingFeature`)
7. Create a new Pull Request with a clear description of your changes

Please follow PSR-12 coding standards and write clear commit messages.

## License

This project is open-source and available under the [MIT License](LICENSE).

## Support

For support, please:
1. Check the [GitHub Issues](https://github.com/RCLDevelopers/BULK-SMS-web-app/issues) for existing solutions
2. If you find a bug or have a feature request, open a new issue
3. For security-related issues, please email security@example.com

## Credits

- [Laravel](https://laravel.com/)
- [Livewire](https://laravel-livewire.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Alpine.js](https://alpinejs.dev/)

## Donate

If you find this project useful, consider supporting its development:
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/yourdonatelink)
