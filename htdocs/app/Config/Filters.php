<?php declare(strict_types=1);

namespace Config;

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
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'minifier'      => HtmlMinifier::class,
        'errorlogger'   => \App\Filters\ErrorLogger::class,
    ];

    public array $required = [
        'before' => [
            // 'forcehttps', // Géré via .htaccess maintenant
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
                'honeypot', // ACTIVE ANTI-SPAM (Point 18)
                // 'secureheaders',
                'minifier',
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