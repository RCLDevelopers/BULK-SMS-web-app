# SMS Application

A comprehensive SMS management system built with Laravel, Livewire, and Tailwind CSS. This application provides user management, SMS sending/receiving capabilities, and real-time statistics.

## Features

- User authentication (login/register)
- Role-based access control (Admin, User)
- Dashboard with real-time statistics
- SMS sending and receiving
- Contact management
- SMS templates
- Reporting and analytics
- Responsive design

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js (v16 or higher)
- NPM or Yarn
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP's built-in development server

## Installation

1. **Clone the repository**
   ```bash
   git clone [your-repository-url] sms-app
   cd sms-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
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
   DB_DATABASE=sms_app
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate --seed
   ```
   This will create the necessary database tables and seed initial admin user.

7. **Build assets**
   ```bash
   npm run build
   ```

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

**Important:** Change these credentials immediately after first login.

## Environment Variables

Key environment variables to configure:

- `APP_NAME`: Your application name
- `APP_ENV`: Application environment (local, production)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_URL`: Application URL
- `DB_*`: Database configuration
- `MAIL_*`: Email configuration for notifications
- `SMS_DRIVER`: SMS gateway driver (e.g., twilio, nexmo)
- `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_NUMBER`: Twilio credentials if using Twilio

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

- Always use HTTPS in production
- Keep dependencies updated
- Store sensitive data in `.env` file (never commit this file)
- Use strong passwords for all accounts
- Regularly backup your database

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-source and available under the [MIT License](LICENSE).

## Support

For support, please open an issue in the GitHub repository or contact the development team.
