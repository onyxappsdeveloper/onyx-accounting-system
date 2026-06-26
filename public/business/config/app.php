<?php
/**
 * Application Configuration
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'ONYX Accounting System',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'https://business.onyxtechpay.com',
    'timezone' => $_ENV['TIMEZONE'] ?? 'Africa/Kampala',
    'currency' => $_ENV['CURRENCY'] ?? 'UGX',

    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key',
        'algorithm' => $_ENV['JWT_ALGORITHM'] ?? 'HS256',
        'expiry' => (int)($_ENV['JWT_EXPIRY'] ?? 86400),
    ],

    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.hostinger.com',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@business.onyxtechpay.com',
            'name' => $_ENV['MAIL_FROM_NAME'] ?? 'ONYX Accounting',
        ],
    ],

    'upload' => [
        'max_size' => (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760),
        'allowed_types' => explode(',', $_ENV['ALLOWED_UPLOAD_TYPES'] ?? 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx'),
        'directory' => 'uploads/',
    ],

    'pagination' => [
        'per_page' => 25,
    ],
];
