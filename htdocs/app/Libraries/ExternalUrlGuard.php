<?php

declare(strict_types=1);

namespace App\Libraries;

final class ExternalUrlGuard
{
    public static function isPublicHttpUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        if (!in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $ipHost = trim($host, '[]');
        if (filter_var($ipHost, FILTER_VALIDATE_IP) !== false) {
            return filter_var(
                $ipHost,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
            ) !== false;
        }

        if (!str_contains($host, '.')) {
            return false;
        }

        return !preg_match('/\.(?:localhost|local|localdomain|lan|internal|intranet|test)$/i', $host);
    }
}
