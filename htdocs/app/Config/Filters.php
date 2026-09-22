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

        'antiinspect' => \App\Filters\AntiInspectFilter::class,
        'headercloaker' => \App\Filters\HeaderCloaker::class,
        'emailobfuscator' => \App\Filters\EmailObfuscator::class,
    ];

    /*
     * Les filtres PageCache et PerformanceMetrics étaient exécutés
     * automatiquement sur toutes les requêtes.
     *
     * Ils ajoutent du traitement inutile à chaque page.
     *
     * La Debug Toolbar est uniquement activée lorsque CI_DEBUG est actif.
     */
    public array $required = [
        'before' => [],
        'after' => CI_DEBUG ? ['toolbar'] : [],
    ];

    public array $globals = [
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