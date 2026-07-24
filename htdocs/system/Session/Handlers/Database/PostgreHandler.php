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

namespace CodeIgniter\Session\Handlers\Database;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Session\Handlers\DatabaseHandler;

/**
 * Session handler for Postgre.
 *
 * @see PostgreHandlerTest
 */
class PostgreHandler extends DatabaseHandler
{
    /**
     * Cleans up expired sessions.
     *
     * @param int $max_lifetime sessions that have not updated
     *                          for the last max_lifetime seconds will be removed
     *
     * @return false|int returns the number of deleted sessions on success, or false on failure
     */
    #[\ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        $separator = '\'';
        $interval = implode($separator, ['', "{$max_lifetime} second", '']);

        return $this->db->table($this->table)->where('timestamp <', "now() - INTERVAL {$interval}", false)->delete() ? 1 : $this->fail();
    }

    /**
     * Sets SELECT clause.
     */
    protected function setSelect(BaseBuilder $builder): void
    {
        $builder->select("encode(data, 'base64') AS data");
    }

    /**
     * Decodes column data.
     *
     * @param string $data
     *
     * @return false|string
     */
    protected function decodeData($data)
    {
        return base64_decode(rtrim($data), true);
    }

    /**
     * Prepare data to insert/update.
     */
    protected function prepareData(string $data): string
    {
        return '\x'.bin2hex($data);
    }

    /**
     * Lock the session.
     */
    protected function lockSession(string $sessionID): bool
    {
        $arg = "hashtext('{$sessionID}')".($this->matchIP ? ", hashtext('{$this->ipAddress}')" : '');
        if (false !== $this->db->simpleQuery("SELECT pg_advisory_lock({$arg})")) {
            $this->lock = $arg;

            return true;
        }

        return $this->fail();
    }

    /**
     * Releases the lock, if any.
     */
    protected function releaseLock(): bool
    {
        if (!$this->lock) {
            return true;
        }

        if (false !== $this->db->simpleQuery("SELECT pg_advisory_unlock({$this->lock})")) {
            $this->lock = false;

            return true;
        }

        return $this->fail();
    }
}
