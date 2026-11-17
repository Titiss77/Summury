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

        foreach ($rows as $item) {
            if (microtime(true) - $started >= self::MAX_SECONDS) break;
            $lastProcessedId = (int) $item->id;
            $url = str_replace(['{ep2}', '{ep}'], [str_pad((string) ($item->episode ?: 1), 2, '0', STR_PAD_LEFT), (string) ($item->episode ?: 1)], trim((string) $item->lien));
            $parts = parse_url($url);
            $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
            $isDead = false;
            $statusCode = 0;
            $isConclusive = false;

            if ($host !== '' && in_array($host, $whitelist, true)) {
                // Trusted domains are intentionally exempt from automated classification.
                $trustedUpdate = $hasLinkCheckedAt ? ['link_checked_at' => $now] : [];
                if ($trustedUpdate !== []) $db->table('item')->where('id', $item->id)->update($trustedUpdate);
                continue;
            }

            if (ExternalUrlGuard::isPublicHttpUrl($url)) {
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
                        $response = $client->get($url);
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
                    'url_testee' => $url, 'code_erreur' => $statusCode,
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
