<?php

declare(strict_types=1);

namespace Config;

class Optimize
{
    /**
     * @see https://codeigniter.com/user_guide/concepts/factories.html#config-caching
     */
    public bool $configCacheEnabled = false; // <-- Passer à false

    /**
     * @see https://codeigniter.com/user_guide/concepts/autoloader.html#file-locator-caching
     */
    public bool $locatorCacheEnabled = false; // <-- Passer à false
}