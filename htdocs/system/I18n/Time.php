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

namespace CodeIgniter\I18n;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property int    $age
 * @property string $day
 * @property string $dayOfWeek
 * @property string $dayOfYear
 * @property bool   $dst
 * @property string $hour
 * @property bool   $local
 * @property string $minute
 * @property string $month
 * @property string $quarter
 * @property string $second
 * @property int    $timestamp
 * @property bool   $utc
 * @property string $weekOfMonth
 * @property string $weekOfYear
 * @property string $year
 *
 * @phpstan-consistent-constructor
 *
 * @see TimeTest
 */
class Time extends \DateTimeImmutable implements \Stringable
{
    use TimeTrait;
}
