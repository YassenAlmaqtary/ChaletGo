<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | ChaletGo application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different types of requests
    |
    */
    'rate_limiting' => [
        'api' => [
            'general' => 120, // requests per minute
            'auth' => 5,      // login attempts per minute
            'upload' => 10,   // file uploads per minute
            'booking' => 20,  // booking requests per minute
        ],
        'web' => [
            'general' => 60,  // requests per minute
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure file upload security settings
    |
    */
    'file_upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB in bytes
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp'
        ],
        'max_dimensions' => [
            'width' => 2048,
            'height' => 2048,
        ],
        'scan_for_malware' => true,
        'remove_exif' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation
    |--------------------------------------------------------------------------
    |
    | Configure input validation and sanitization
    |
    */
    'input_validation' => [
        'max_string_length' => 10000,
        'scan_for_xss' => true,
        'scan_for_sql_injection' => true,
        'scan_for_path_traversal' => true,
        'scan_for_code_injection' => true,
        'remove_html_tags' => true,
        'convert_special_chars' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers to be sent with responses
    |
    */
    'headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
        'hsts_max_age' => 31536000, // 1 year
        'csp' => [
            'default_src' => "'self'",
            'script_src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            'style_src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
            'font_src' => "'self' https://fonts.gstatic.com",
            'img_src' => "'self' data: https:",
            'connect_src' => "'self'",
            'frame_ancestors' => "'none'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Configure API-specific security settings
    |
    */
    'api' => [
        'require_https' => env('APP_ENV') === 'production',
        'jwt_ttl' => 60, // minutes
        'jwt_refresh_ttl' => 20160, // minutes (2 weeks)
        'max_login_attempts' => 5,
        'lockout_duration' => 15, // minutes
        'require_email_verification' => true,
        'require_phone_verification' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | Configure database security settings
    |
    */
    'database' => [
        'encrypt_sensitive_data' => true,
        'log_queries' => env('APP_ENV') !== 'production',
        'prevent_sql_injection' => true,
        'use_prepared_statements' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure security logging and monitoring
    |
    */
    'logging' => [
        'log_failed_logins' => true,
        'log_suspicious_activity' => true,
        'log_file_uploads' => true,
        'log_rate_limit_hits' => true,
        'alert_on_multiple_failures' => true,
        'max_log_size' => 100 * 1024 * 1024, // 100MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | Configure encryption settings
    |
    */
    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'key_rotation_days' => 90,
        'encrypt_cookies' => true,
        'encrypt_session' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Settings
    |--------------------------------------------------------------------------
    |
    | Configure Cross-Origin Resource Sharing
    |
    */
    'cors' => [
        'allowed_origins' => [
            'http://localhost:3000',
            'https://chaletgo.com',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'expose_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],
        'max_age' => 86400, // 24 hours
        'supports_credentials' => true,
    ],
];
