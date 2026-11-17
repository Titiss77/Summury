<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\WhitelistModel;
use Config\Services;
use Throwable;

/** Small, resumable HTTP cron worker. One request is deliberately time-boxed. */
class CronController extends BaseController
{
    private const MAX_ITEMS = 20;
    private const MAX_SECONDS = 12;
    private const RECHECK_AFTER_DAYS = 30;

    public function run()
    {
        $secret = (string) env('cron.secret', '');
        $provided = (string) ($this->request->getHeaderLine('X-Cron-Token') ?: $this->request->getGet('token'));
        if ($secret === '' || ! hash_equals($secret, $provided)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }

        return $this->processBatch(false, 0);
    }

    /** Admin-only bounded batch used repeatedly by the full-scan button. */
    public function adminScan()
    {
        $afterId = max(0, (int) $this->request->getGet('after_id'));
        return $this->processBatch(true, $afterId);
    }

    /** Build a reviewable, deduplicated list of generic URL origins from active items. */
    public function generateLinkList()
    {
        $origins = [];
        $lastId = 0;
        $db = db_connect();

        do {
            $items = $db->table('item')
                ->select('id, lien')
                ->where('id >', $lastId)
                ->where('deleted_at', null)
                ->where('lien IS NOT NULL', null, false)
                ->where('lien !=', '')
                ->orderBy('id', 'ASC')
                ->limit(500)
                ->get()->getResult();

            foreach ($items as $item) {
                $lastId = (int) $item->id;
                $origin = $this->genericOrigin((string) $item->lien);
                if ($origin !== null) {
                    $parts = parse_url($origin);
                    $hostKey = strtolower((string) ($parts['host'] ?? '')) . (isset($parts['port']) ? ':' . $parts['port'] : '');
                    if (! isset($origins[$hostKey]) || ($parts['scheme'] ?? '') === 'https') {
                        $origins[$hostKey] = $origin;
                    }
                }
            }
        } while (count($items) === 500);

        $list = array_values($origins);
        sort($list, SORT_STRING);
        $directory = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Impossible de créer le répertoire des journaux.']);
        }

        $written = file_put_contents($directory . 'liste.log', implode(PHP_EOL, $list) . PHP_EOL, LOCK_EX);
        if ($written === false) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Impossible d’écrire liste.log.']);
        }

        return $this->response->setJSON([
            'status' => 'generated',
            'unique_origins' => count($list),
            'download_url' => base_url('a/d/list/download'),
        ]);
    }

    /** Download the generated list; this route is protected by the admin filter. */
    public function downloadLinkList()
    {
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        if (! is_file($path)) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'missing', 'message' => 'Générez d’abord la liste des liens.']);
        }

        return $this->response->download($path, null, true);
    }

    private function genericOrigin(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if (str_starts_with($url, '//')) $url = 'https:' . $url;
        elseif (! preg_match('~^https?://~i', $url)) $url = 'https://' . $url;

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $port = $parts['port'] ?? null;
        if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            $host .= ':' . $port;
        }

        return $scheme . '://' . $host . '/';
    }

    private function processBatch(bool $fullScan, int $afterId)
    {

        $started = microtime(true);
        $now = date('Y-m-d H:i:s');
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::RECHECK_AFTER_DAYS . ' days'));
        $db = db_connect();
        $hasLinkCheckedAt = $db->fieldExists('link_checked_at', 'item');
        if (! $hasLinkCheckedAt && ! $fullScan) {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'migration_required',
                'message' => 'Appliquez la migration link_checked_at pour activer le cron automatique.',
            ]);
        }
        $builder = $db->table('item')
            ->select('id, titre, lien, episode, link_status')
            ->where('deleted_at', null)
            ->where('lien IS NOT NULL', null, false)
            ->where('lien !=', '');
        if ($fullScan) {
            $builder->where('id >', $afterId)->orderBy('id', 'ASC');
        } else {
            $builder->groupStart()
                ->where('link_checked_at', null)
                ->orWhere('link_checked_at <', $cutoff)
                ->groupEnd()
                ->orderBy('link_checked_at', 'ASC');
        }
        $rows = $builder->limit(self::MAX_ITEMS)->get()->getResult();

        if ($rows === []) {
            return $this->response->setJSON(['status' => $fullScan ? 'complete' : 'idle', 'complete' => true, 'checked' => 0, 'dead' => 0, 'next_after_id' => $afterId]);
        }

        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $client = Services::curlrequest([
            'timeout' => 4,
            'connect_timeout' => 2,
            'http_errors' => false,
            'allow_redirects' => ['max' => 3, 'strict' => true, 'referer' => false],
            'verify' => true,
            'headers' => [
                'User-Agent' => 'TitissLinkChecker/1.0 (+https://titiss.space)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'fr,en;q=0.8',
                'Range' => 'bytes=0-0',
            ],
        ]);

        $checked = $dead = $inconclusive = 0;
        $lastProcessedId = $afterId;
        $cronLogs = new CronLogModel();
        $checksByOrigin = [];

        foreach ($rows as $item) {
            if (microtime(true) - $started >= self::MAX_SECONDS) break;
            $lastProcessedId = (int) $item->id;
            // Test the origin only; chapter and episode paths can legitimately 404.
            $parts = parse_url(trim((string) $item->lien));
            $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
            $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
            $port = isset($parts['port']) ? ':' . $parts['port'] : '';
            $urlToTest = $scheme . '://' . $host . $port . '/';
            $isDead = false;
            $statusCode = 0;
            $isConclusive = false;

            if ($host !== '' && in_array($host, $whitelist, true)) {
                // Trusted domains are intentionally exempt from automated classification.
                $trustedUpdate = $hasLinkCheckedAt ? ['link_checked_at' => $now] : [];
                if ($trustedUpdate !== []) $db->table('item')->where('id', $item->id)->update($trustedUpdate);
                continue;
            }

            if (array_key_exists($urlToTest, $checksByOrigin)) {
                [$isDead, $statusCode, $isConclusive] = $checksByOrigin[$urlToTest];
            } elseif (ExternalUrlGuard::isPublicHttpUrl($urlToTest)) {
                try {
                    // Require NXDOMAIN from two independent DoH operators. A blocked DoH
                    // request or any timeout remains inconclusive and never kills a link.
                    $answers = [];
                    foreach (['https://dns.google/resolve', 'https://cloudflare-dns.com/dns-query'] as $index => $endpoint) {
                        $dns = $client->get($endpoint, [
                            'query' => ['name' => $host, 'type' => 'A'],
                            'headers' => $index === 1 ? ['accept' => 'application/dns-json'] : [],
                        ]);
                        $data = json_decode((string) $dns->getBody(), true);
                        $answers[] = $dns->getStatusCode() === 200 && is_array($data) ? ($data['Status'] ?? null) : null;
                    }
                    if ($answers === [3, 3]) {
                        $isDead = $isConclusive = true;
                        $statusCode = 404;
                    } else {
                        $response = $client->get($urlToTest);
                        $statusCode = $response->getStatusCode();
                        if (in_array($statusCode, [404, 410], true)) {
                            $isDead = $isConclusive = true;
                        } elseif ($statusCode >= 200 && $statusCode < 400) {
                            $isConclusive = true;
                        }
                    }
                } catch (Throwable $e) {
                    // Network restrictions, TLS errors, rate limits and timeouts are unknown.
                    log_message('debug', '[Cron] Vérification inconclusive pour {host}: {error}', ['host' => $host, 'error' => $e->getMessage()]);
                }
                $checksByOrigin[$urlToTest] = [$isDead, $statusCode, $isConclusive];
            }

            ++$checked;
            if (! $isConclusive) ++$inconclusive;
            // Retry inconclusive network outcomes the next day instead of
            // delaying them for the full 30-day healthy-link interval.
            $nextCheckAt = $isConclusive ? $now : date('Y-m-d H:i:s', strtotime('-29 days'));
            $update = $hasLinkCheckedAt ? ['link_checked_at' => $nextCheckAt] : [];
            if ($isDead && $item->link_status !== 'dead') $update['link_status'] = 'dead';
            elseif ($isConclusive && ! $isDead && $item->link_status === 'dead') $update['link_status'] = 'ok';
            // Direct query builder keeps ItemModel timestamps from changing updated_at.
            if ($update !== []) $db->table('item')->where('id', $item->id)->update($update);

            if ($isDead && $item->link_status !== 'dead') {
                $cronLogs->insert([
                    'task_name' => 'check_dead_links', 'last_run' => $now,
                    'item_id' => $item->id, 'titre' => $item->titre,
                    'url_testee' => $urlToTest, 'code_erreur' => $statusCode,
                ]);
                ++$dead;
            }
        }

        if ($dead > 0) (new AuditLogModel())->logAction('Cron', "Vérification auto : {$dead} nouveaux liens morts détectés.");
        $lastBatchId = (int) $rows[count($rows) - 1]->id;
        $batchFinished = $lastProcessedId >= $lastBatchId;

        return $this->response->setJSON([
            'status' => 'executed', 'complete' => $fullScan && $batchFinished && count($rows) < self::MAX_ITEMS, 'checked' => $checked, 'dead' => $dead,
            'inconclusive' => $inconclusive, 'remaining_batch' => max(0, count($rows) - $checked),
            'next_after_id' => $lastProcessedId,
            // Keep the former response keys for any older admin tooling.
            'total_cards' => $checked, 'unique_urls' => $checked, 'dead_count' => $dead,
        ]);
    }
}
