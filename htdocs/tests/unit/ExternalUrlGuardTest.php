<?php

declare(strict_types=1);

use App\Libraries\ExternalUrlGuard;
use CodeIgniter\Test\CIUnitTestCase;

final class ExternalUrlGuardTest extends CIUnitTestCase
{
    public function testAcceptsSafeHttpUrlsForStorage(): void
    {
        $this->assertTrue(ExternalUrlGuard::isSafeHttpUrl('https://example.com/watch/episode-1'));
        $this->assertTrue(ExternalUrlGuard::isSafeHttpUrl('https://example.com/episode/1'));
        $this->assertTrue(ExternalUrlGuard::isSafeHttpUrl('http://8.8.8.8/'));
    }

    public function testRejectsUnsafeSchemesCredentialsAndLocalHosts(): void
    {
        foreach (['javascript:alert(1)', 'data:text/html,hello', 'ftp://example.com/file',
            'https://user:password@example.com/', 'http://127.0.0.1/admin', 'http://[::1]/admin',
            'http://localhost/', 'http://service.local/', 'http://192.168.1.1/'] as $url) {
            $this->assertFalse(ExternalUrlGuard::isSafeHttpUrl($url), $url);
        }
    }

    public function testRejectsMalformedAndOversizedUrls(): void
    {
        $this->assertFalse(ExternalUrlGuard::isSafeHttpUrl('not a URL'));
        $this->assertFalse(ExternalUrlGuard::isSafeHttpUrl('https://'.str_repeat('a', 2049).'.com/'));
    }

    public function testRejectsLiteralNonPublicAddressesForServerRequests(): void
    {
        foreach (['http://127.0.0.1/', 'http://10.0.0.1/', 'http://169.254.169.254/', 'http://[::1]/'] as $url) {
            $this->assertFalse(ExternalUrlGuard::isPublicHttpUrl($url), $url);
        }
        $this->assertTrue(ExternalUrlGuard::isPublicHttpUrl('http://8.8.8.8/'));
    }
}
