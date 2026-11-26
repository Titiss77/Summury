<?php

declare(strict_types=1);

namespace App\Libraries;

final class ExternalUrlGuard
{
    public static function isSafeHttpUrl(string $url): bool
    {
        if ('' === $url || strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) return false;
        $parts = parse_url($url);
        if (!is_array($parts)) return false;
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        if (!in_array($scheme, ['http', 'https'], true) || '' === $host || isset($parts['user']) || isset($parts['pass'])) return false;
        if (filter_var($host, FILTER_VALIDATE_IP)) return self::isPublicIp($host);
        if (!str_contains($host, '.') || preg_match('/\.(?:localhost|local|localdomain|lan|internal|intranet|test)$/i', $host)) return false;
        return true;
    }

    public static function isPublicHttpUrl(string $url): bool
    {
        if (!self::isSafeHttpUrl($url)) return false;
        $host = strtolower(rtrim((string) parse_url($url, PHP_URL_HOST), '.'));
        if (filter_var($host, FILTER_VALIDATE_IP)) return self::isPublicIp($host);
        // Résolution DNS mise en cache pour éviter un lookup à chaque carte.
        // Les résultats privés sont également mémorisés, jamais autorisés.
        $cacheKey = 'url_guard_dns_v1_' . hash('sha256', $host);
        $addresses = cache()->get($cacheKey);

        if (!is_array($addresses)) {
            $addresses = [];
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (!empty($record['ip'])) $addresses[] = $record['ip'];
                    if (!empty($record['ipv6'])) $addresses[] = $record['ipv6'];
                }
            }
            if ($addresses === []) {
                $ipv4 = @gethostbynamel($host);
                if (is_array($ipv4)) $addresses = array_merge($addresses, $ipv4);
            }
            $addresses = array_values(array_unique(array_filter($addresses, 'is_string')));
            if ($addresses !== []) cache()->save($cacheKey, $addresses, 300);
        }

        if ($addresses === []) return false;
        foreach ($addresses as $address) {
            if (!self::isPublicIp((string) $address)) return false;
        }
        return true;
    }

    private static function isPublicIp(string $ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
