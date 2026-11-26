<?php

declare(strict_types=1);

use App\Libraries\ExternalUrlGuard;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ExternalUrlGuardTest extends CIUnitTestCase
{
    public function testAcceptsPublicHttpUrls(): void
    {
        $this->assertTrue(ExternalUrlGuard::isPublicHttpUrl('https://example.com/watch/episode-1'));
        $this->assertTrue(ExternalUrlGuard::isPublicHttpUrl('http://8.8.8.8/'));
    }

    public function testRejectsLocalAndNonHttpUrls(): void
    {
        foreach ([
            'http://127.0.0.1/admin',
            'http://[::1]/admin',
            'http://localhost/',
            'http://service.local/',
            'ftp://example.com/file',
            'https://user:password@example.com/',
        ] as $url) {
            $this->assertFalse(ExternalUrlGuard::isPublicHttpUrl($url), $url);
        }
    }

    public function testRejectsMalformedAndOversizedUrls(): void
    {
        $this->assertFalse(ExternalUrlGuard::isPublicHttpUrl('not a URL'));
        $this->assertFalse(ExternalUrlGuard::isPublicHttpUrl('https://'.str_repeat('a', 2049).'.com/'));
    }
}
