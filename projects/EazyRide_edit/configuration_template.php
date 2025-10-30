<?php
/**
 * VoltiaCar Configuration Template
 * 
 * IMPORTANT: Copy this file to .env or create your own configuration file
 * DO NOT commit sensitive credentials to version control
 * 
 * Instructions:
 * 1. Copy this file and rename it (e.g., config.php or .env)
 * 2. Fill in all the placeholder values with your actual configuration
 * 3. Ensure proper file permissions (readable by web server, not publicly accessible)
 * 4. Add your config file to .gitignore
 */

// ============================================================================
// DATABASE CONFIGURATION - MariaDB/MySQL
// ============================================================================

/**
 * Database Host
 * Default: 'localhost' for local development
 * For Docker: 'mariadb' (service name)
 * For production: Your database server hostname or IP
 */
define('DB_HOST', 'localhost');

/**
 * Database Name
 * The name of your MariaDB/MySQL database
 */
define('DB_NAME', 'simsdb');

/**
 * Database Username
 * User with full privileges on the database
 */
define('DB_USER', 'simsuser');

/**
 * Database Password
 * IMPORTANT: Use a strong password in production
 */
define('DB_PASS', 'your_secure_password_here');

/**
 * Database Port
 * Default MySQL/MariaDB port is 3306
 */
define('DB_PORT', '3306');

/**
 * Database Character Set
 * Recommended: utf8mb4 for full Unicode support
 */
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// DATABASE CONFIGURATION - MongoDB (Optional)
// ============================================================================

/**
 * MongoDB Host
 * Default: 'localhost' for local development
 * For Docker: 'mongodb' (service name)
 */
define('MONGO_HOST', 'localhost');

/**
 * MongoDB Port
 * Default MongoDB port is 27017
 */
define('MONGO_PORT', '27017');

/**
 * MongoDB Database Name
 */
define('MONGO_DB', 'simsdb');

/**
 * MongoDB Username
 */
define('MONGO_USER', 'simsadmin');

/**
 * MongoDB Password
 * IMPORTANT: Use a strong password in production
 */
define('MONGO_PASS', 'your_secure_mongodb_password_here');

// ============================================================================
// APPLICATION SETTINGS
// ============================================================================

/**
 * Application Base URL
 * The full URL where your application is hosted
 * Examples: 
 * - Development: 'http://localhost:8080'
 * - Production: 'https://voltiacar.com'
 * NO trailing slash
 */
define('APP_URL', 'http://localhost:8080');

/**
 * Application Name
 */
define('APP_NAME', 'VoltiaCar');

/**
 * Application Environment
 * Options: 'development', 'staging', 'production'
 */
define('APP_ENV', 'production');

/**
 * Debug Mode
 * Set to false in production to hide error messages
 */
define('APP_DEBUG', false);

/**
 * Default Language
 * Options: 'ca' (Catalan), 'es' (Spanish), 'en' (English)
 */
define('DEFAULT_LANG', 'ca');

/**
 * Timezone
 * See: https://www.php.net/manual/en/timezones.php
 */
define('APP_TIMEZONE', 'Europe/Madrid');

// ============================================================================
// SECURITY SETTINGS
// ============================================================================

/**
 * Session Lifetime (in seconds)
 * Default: 7200 (2 hours)
 */
define('SESSION_LIFETIME', 7200);

/**
 * Session Cookie Name
 */
define('SESSION_NAME', 'voltiacar_session');

/**
 * CSRF Token Name
 */
define('CSRF_TOKEN_NAME', 'csrf_token');

/**
 * Password Hashing Algorithm
 * Recommended: PASSWORD_DEFAULT (uses bcrypt)
 */
define('PASSWORD_ALGO', PASSWORD_DEFAULT);

/**
 * Password Minimum Length
 */
define('PASSWORD_MIN_LENGTH', 8);

/**
 * Enable HTTPS Only Cookies
 * Set to true in production if using HTTPS
 */
define('SECURE_COOKIES', false);

/**
 * Enable HTTP Only Cookies
 * Recommended: true (prevents JavaScript access to cookies)
 */
define('HTTP_ONLY_COOKIES', true);

/**
 * Same Site Cookie Policy
 * Options: 'Strict', 'Lax', 'None'
 */
define('SAME_SITE_COOKIES', 'Lax');

// ============================================================================
// FILE UPLOAD SETTINGS
// ============================================================================

/**
 * Upload Directory
 * Path relative to application root
 */
define('UPLOAD_DIR', __DIR__ . '/../images/avatars/');

/**
 * Maximum File Upload Size (in bytes)
 * Default: 5242880 (5 MB)
 */
define('MAX_UPLOAD_SIZE', 5242880);

/**
 * Allowed File Types for Avatar Upload
 * Comma-separated MIME types
 */
define('ALLOWED_AVATAR_TYPES', 'image/jpeg,image/png,image/gif,image/webp');

/**
 * Allowed File Extensions for Avatar Upload
 */
define('ALLOWED_AVATAR_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

/**
 * Driver License Upload Directory
 */
define('DRIVER_LICENSE_DIR', __DIR__ . '/../images/driver_licenses/');

/**
 * Allowed File Types for Driver License
 */
define('ALLOWED_LICENSE_TYPES', 'image/jpeg,image/png,application/pdf');

// ============================================================================
// EMAIL SETTINGS (Optional - for future implementation)
// ============================================================================

/**
 * SMTP Host
 */
define('SMTP_HOST', 'smtp.example.com');

/**
 * SMTP Port
 * Common ports: 587 (TLS), 465 (SSL), 25 (non-encrypted)
 */
define('SMTP_PORT', 587);

/**
 * SMTP Username
 */
define('SMTP_USER', 'noreply@voltiacar.com');

/**
 * SMTP Password
 */
define('SMTP_PASS', 'your_smtp_password_here');

/**
 * SMTP Encryption
 * Options: 'tls', 'ssl', or empty string for none
 */
define('SMTP_ENCRYPTION', 'tls');

/**
 * Email From Address
 */
define('EMAIL_FROM', 'noreply@voltiacar.com');

/**
 * Email From Name
 */
define('EMAIL_FROM_NAME', 'VoltiaCar');

// ============================================================================
// API KEYS (Optional - for maps and external services)
// ============================================================================

/**
 * Google Maps API Key
 * Required for map functionality
 * Get your key at: https://console.cloud.google.com/
 */
define('GOOGLE_MAPS_API_KEY', 'your_google_maps_api_key_here');

/**
 * Mapbox API Key (Alternative to Google Maps)
 * Get your key at: https://www.mapbox.com/
 */
define('MAPBOX_API_KEY', 'your_mapbox_api_key_here');

// ============================================================================
// PAYMENT SETTINGS (Optional - for future implementation)
// ============================================================================

/**
 * Payment Gateway
 * Options: 'stripe', 'paypal', etc.
 */
define('PAYMENT_GATEWAY', 'stripe');

/**
 * Stripe Publishable Key
 */
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key_here');

/**
 * Stripe Secret Key
 */
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key_here');

/**
 * Currency
 * ISO 4217 currency code
 */
define('CURRENCY', 'EUR');

// ============================================================================
// LOGGING SETTINGS
// ============================================================================

/**
 * Enable Error Logging
 */
define('ENABLE_LOGGING', true);

/**
 * Log File Path
 */
define('LOG_FILE', __DIR__ . '/../logs/app.log');

/**
 * Log Level
 * Options: 'debug', 'info', 'warning', 'error', 'critical'
 */
define('LOG_LEVEL', 'error');

// ============================================================================
// RATE LIMITING (Optional - for API protection)
// ============================================================================

/**
 * Enable Rate Limiting
 */
define('ENABLE_RATE_LIMIT', true);

/**
 * Maximum Requests per Minute
 */
define('RATE_LIMIT_REQUESTS', 60);

/**
 * Rate Limit Window (in seconds)
 */
define('RATE_LIMIT_WINDOW', 60);

// ============================================================================
// VEHICLE SETTINGS
// ============================================================================

/**
 * Default Price per Minute (in currency units)
 */
define('DEFAULT_PRICE_PER_MINUTE', 0.30);

/**
 * Unlock Fee (in currency units)
 */
define('UNLOCK_FEE', 1.00);

/**
 * Minimum Booking Duration (in minutes)
 */
define('MIN_BOOKING_DURATION', 15);

/**
 * Maximum Booking Duration (in minutes)
 */
define('MAX_BOOKING_DURATION', 1440); // 24 hours

/**
 * Booking Advance Time (in days)
 * How far in advance users can book vehicles
 */
define('BOOKING_ADVANCE_DAYS', 7);

// ============================================================================
// SUBSCRIPTION SETTINGS
// ============================================================================

/**
 * Basic Subscription Free Minutes
 */
define('BASIC_FREE_MINUTES', 25);

/**
 * Premium Subscription Free Minutes
 */
define('PREMIUM_FREE_MINUTES', 100);

/**
 * Premium Subscription Monthly Price
 */
define('PREMIUM_MONTHLY_PRICE', 9.99);

// ============================================================================
// APPLY TIMEZONE
// ============================================================================
date_default_timezone_set(APP_TIMEZONE);

// ============================================================================
// ERROR REPORTING
// ============================================================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', HTTP_ONLY_COOKIES ? 1 : 0);
ini_set('session.cookie_secure', SECURE_COOKIES ? 1 : 0);
ini_set('session.cookie_samesite', SAME_SITE_COOKIES);
ini_set('session.name', SESSION_NAME);

?>
