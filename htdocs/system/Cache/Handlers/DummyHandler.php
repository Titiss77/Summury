<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

/**
 * Dummy cache handler.
 *
 * @see DummyHandlerTest
 */
class DummyHandler extends BaseHandler
{
    public function initialize(): void {}

    public function get(string $key)
    {
        return null;
    }

    public function remember(string $key, int $ttl, \Closure $callback)
    {
        return null;
    }

    public function save(string $key, $value, int $ttl = 60)
    {
        return true;
    }

    public function delete(string $key)
    {
        return true;
    }

    /**
     * @return int
     */
    public function deleteMatching(string $pattern)
    {
        return 0;
    }

    public function increment(string $key, int $offset = 1)
    {
        return true;
    }

    public function decrement(string $key, int $offset = 1)
    {
        return true;
    }

    public function clean()
    {
        return true;
    }

    public function getCacheInfo()
    {
        return null;
    }

    public function getMetaData(string $key)
    {
        return null;
    }

    public function isSupported(): bool
    {
        return true;
    }
}
