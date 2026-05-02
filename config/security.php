<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les paramètres de sécurité de l'application
    |
    */
    
    'rate_limiting' => [
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
            'block_duration' => 15, // minutes
        ],
        'auth' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
            'block_duration' => 30, // minutes
        ],
    ],
    
    'cors' => [
        'allowed_origins' => [
            'http://localhost:3000',
            'http://localhost:8080',
            'https://naissancechain.test',
            'https://naissancechain.com',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
        'allow_credentials' => true,
        'max_age' => 86400,
    ],
    
    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';",
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],
    
    'validation' => [
        'max_string_length' => 1000,
        'sanitize_input' => true,
        'strict_content_type' => true,
        'block_suspicious_patterns' => true,
    ],
    
    'logging' => [
        'log_sql_queries' => env('SECURITY_LOG_SQL', false),
        'log_rate_limiting' => env('SECURITY_LOG_RATE_LIMIT', true),
        'log_suspicious_requests' => env('SECURITY_LOG_SUSPICIOUS', true),
        'retention_days' => 30,
    ],
    
    'sql_injection' => [
        'detect_patterns' => [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from\s+\w+\s+where\s+1\s*=\s*1/i',
            '/insert\s+into\s+\w+\s*\(.*\)\s*values\s*\(.*\)\s*;\s*drop/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/eval\s*\(/i',
        ],
        'suspicious_bindings' => [
            'union select',
            'drop table',
            'delete from',
            'insert into',
            'exec(',
            'system(',
            'eval(',
            '--',
            '/*',
            '*/',
            ';drop',
            ';delete',
            ';update',
        ],
    ],
    
    'xss_protection' => [
        'patterns' => [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
        ],
    ],
];
