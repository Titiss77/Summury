<?php

declare(strict_types=1);

namespace Config;

use App\Filters\AntiInspectFilter;
use App\Filters\EmailObfuscator;
use App\Filters\ErrorLogger;
use App\Filters\HeaderCloaker;
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
        'errorlogger'   => ErrorLogger::class,
        'antiinspect'   => AntiInspectFilter::class,
        'headercloaker' => HeaderCloaker::class,
        'emailobfuscator' => EmailObfuscator::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => CI_DEBUG ? ['toolbar'] : [],
    ];

    /**
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = ENVIRONMENT === 'production'
        ? [
            'before' => [
                'honeypot',
                'csrf',
            ],
            'after' => [
                'antiinspect',
                'headercloaker',
                'honeypot',
                'errorlogger',
            ],
        ]
        : [
            'before' => [
                'honeypot',
                'csrf',
            ],
            'after' => [
                'honeypot',
                'errorlogger',
            ],
        ];

    public array $methods = [];

    public array $filters = [];
}