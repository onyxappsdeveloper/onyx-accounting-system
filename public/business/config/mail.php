<?php
/**
 * Mail Configuration
 */

return [
    'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.hostinger.com',
    'port' => $_ENV['MAIL_PORT'] ?? 587,
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@business.onyxtechpay.com',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'ONYX Accounting',
    ],
];
