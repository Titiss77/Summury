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

namespace CodeIgniter\Publisher;

use CodeIgniter\Exceptions\RuntimeException;

/**
 * Replace Text Content.
 *
 * @see ContentReplacerTest
 */
class ContentReplacer
{
    /**
     * Replace content.
     *
     * @param array $replaces [search => replace]
     */
    public function replace(string $content, array $replaces): string
    {
        return strtr($content, $replaces);
    }

    /**
     * Add line after the line with the string.
     *
     * @param string $content whole content
     * @param string $line    line to add
     * @param string $after   string to search
     *
     * @return null|string updated content, or null if not updated
     */
    public function addAfter(string $content, string $line, string $after): ?string
    {
        $pattern = '/(.*)(\n[^\n]*?'.preg_quote($after, '/').'[^\n]*?\n)/su';
        $replace = '$1$2'.$line."\n";

        return $this->add($content, $line, $pattern, $replace);
    }

    /**
     * Add line before the line with the string.
     *
     * @param string $content whole content
     * @param string $line    line to add
     * @param string $before  string to search
     *
     * @return null|string updated content, or null if not updated
     */
    public function addBefore(string $content, string $line, string $before): ?string
    {
        $pattern = '/(\n)([^\n]*?'.preg_quote($before, '/').')(.*)/su';
        $replace = '$1'.$line."\n".'$2$3';

        return $this->add($content, $line, $pattern, $replace);
    }

    /**
     * Add text.
     *
     * @param string $text    text to add
     * @param string $pattern regexp search pattern
     * @param string $replace regexp replacement including text to add
     *
     * @return null|string updated content, or null if not updated
     */
    private function add(string $content, string $text, string $pattern, string $replace): ?string
    {
        $return = preg_match('/'.preg_quote($text, '/').'/u', $content);

        if (false === $return) {
            // Regexp error.
            throw new RuntimeException('Regex error. PCRE error code: '.preg_last_error());
        }

        if (1 === $return) {
            // It has already been updated.
            return null;
        }

        $return = preg_replace($pattern, $replace, $content);

        if (null === $return) {
            // Regexp error.
            throw new RuntimeException('Regex error. PCRE error code: '.preg_last_error());
        }

        return $return;
    }
}
