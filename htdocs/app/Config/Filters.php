<?php

declare(strict_types=1);

namespace Config;

use App\Filters\ErrorLogger;
use App\Filters\HtmlMinifier;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors' => Cors::class,
        'forcehttps' => ForceHTTPS::class,
        'pagecache' => PageCache::class,
        'performance' => PerformanceMetrics::class,
        'minifier' => HtmlMinifier::class,
        'errorlogger' => ErrorLogger::class,
        'antiinspect'   => \App\Filters\AntiInspectFilter::class,
        'headercloaker' => \App\Filters\HeaderCloaker::class,
        'emailobfuscator' => \App\Filters\EmailObfuscator::class,
    ];

    public array $required = [
        'before' => [
            //'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = ENVIRONMENT === 'production'
        ? [
            'before' => [
                'honeypot', // ACTIVE ANTI-SPAM (Point 18)
                // 'csrf',
                // 'invalidchars',
            ],
            'after' => [
                'honeypot',
                'headercloaker',   // 1. Modifie les en-têtes
                'antiinspect',     // 2. Injecte le JS de blocage
                'emailobfuscator', // 3. Encode les emails
                'minifier',        // 4. Compresse le tout (ton filtre actuel)
                'errorlogger',
            ],
        ]
        : [
            'before' => [
                'honeypot', // ACTIVE ANTI-SPAM
                // 'csrf',
                // 'invalidchars',
            ],
            'after' => [
                'honeypot', // ACTIVE ANTI-SPAM
                // 'secureheaders',
                'errorlogger',
            ],
        ];

    public array $methods = [];
    public array $filters = [];
}