<?php
declare(strict_types=1);

return [
    'mail' => [
        // 'test' stores submissions locally; set to 'smtp' to send real email.
        'driver' => 'test',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-gmail-address@gmail.com',
        'password' => 'your-google-app-password',
        'to_email' => 'your-receiving-address@example.com',
    ],
];
