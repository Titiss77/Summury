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
        if (! $this->hasValidCronToken()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }

        return $this->response->setJSON([
            'status' => 'disabled',
            'message' => 'Le scan Ping se lance depuis la page administration des liens morts.',
        ]);
    }

    /** Return one list page to a trusted worker running outside Byethost. */
    public function exportProbeList()
    {
        if (! $this->hasValidCronToken()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        if (! is_file($path) || ! is_readable($path)) {
            return $this->response->setStatusCode(409)->setJSON(['status' => 'list_required', 'message' => 'Générez liste.log depuis la page admin.']);
        }

        $origins = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $origins = $origins === false ? [] : array_values(array_unique(array_map('trim', $origins)));
        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $origins = array_values(array_filter($origins, function (string $origin) use ($whitelist): bool {
            $parts = parse_url($origin);
            return ! in_array(strtolower(rtrim((string) ($parts['host'] ?? ''), '.')), $whitelist, true);
        }));
        $offset = max(0, (int) $this->request->getGet('offset'));
        $limit = min(50, max(1, (int) ($this->request->getGet('limit') ?: 20)));
        return $this->response->setJSON([
            'origins' => array_slice($origins, $offset, $limit),
            'offset' => $offset,
            'next_offset' => min($offset + $limit, count($origins)),
            'total' => count($origins),
        ]);
    }

    /** Apply results from the independent checker to every active card at that origin. */
    public function importProbeResults()
    {
        if (! $this->hasValidCronToken()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }
        $payload = $this->request->getJSON(true);
        $results = is_array($payload) ? ($payload['results'] ?? null) : null;
        $jobId = is_array($payload) ? (string) ($payload['job_id'] ?? '') : '';
        $state = $this->readPingJobState();
        if (! is_array($results) || count($results) > 1000 || $jobId === '' || ($state['id'] ?? '') !== $jobId || ! in_array(($state['status'] ?? ''), ['pending', 'running'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'invalid_payload']);
        }

        $db = db_connect();
        $hasLinkCheckedAt = $db->fieldExists('link_checked_at', 'item');
        $now = date('Y-m-d H:i:s');
        $deadCount = 0;
        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $allowedOrigins = is_file(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log')
            ? array_map('trim', file(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [])
            : [];
        $cronLogs = new CronLogModel();
        foreach ($results as $result) {
            if (! is_array($result) || ! isset($result['origin'], $result['status'])) continue;
            $origin = $this->genericOrigin((string) $result['origin']);
            $status = (string) $result['status'];
            if ($origin === null || ! in_array($origin, $allowedOrigins, true) || ! in_array($status, ['alive', 'dead', 'unknown'], true)) continue;

            $parts = parse_url($origin);
            $host = strtolower((string) ($parts['host'] ?? ''));
            if (in_array($host, $whitelist, true)) continue;
            $items = $this->findItemsForOrigin($host, $parts['port'] ?? null);
            foreach ($items as $item) {
                $update = $hasLinkCheckedAt
                    ? ['link_checked_at' => $status === 'unknown' ? date('Y-m-d H:i:s', strtotime('-29 days')) : $now]
                    : [];
                if ($status === 'dead' && $item->link_status !== 'dead') $update['link_status'] = 'dead';
                elseif ($status === 'alive' && $item->link_status === 'dead') $update['link_status'] = 'ok';
                if ($update !== []) $db->table('item')->where('id', $item->id)->update($update);

                if ($status === 'dead' && $item->link_status !== 'dead') {
                    $cronLogs->insert([
                        'task_name' => 'check_dead_links', 'last_run' => $now,
                        'item_id' => $item->id, 'titre' => $item->titre,
                        'url_testee' => $origin, 'code_erreur' => (int) ($result['http_code'] ?? 0),
                    ]);
                    ++$deadCount;
                }
            }
        }

        if ($deadCount > 0) (new AuditLogModel())->logAction('Cron', "Vérification distante : {$deadCount} nouveaux liens morts détectés.");
        $state['status'] = 'done';
        $state['completed_at'] = $now;
        $state['origins_checked'] = count($results);
        $state['new_dead_cards'] = $deadCount;
        $this->writePingJobState($state);
        return $this->response->setJSON(['status' => 'imported', 'new_dead_cards' => $deadCount]);
    }

    private function hasValidCronToken(): bool
    {
        $secret = (string) env('cron.secret', '');
        $provided = (string) ($this->request->getHeaderLine('X-Cron-Token') ?: $this->request->getGet('token'));
        return $secret !== '' && hash_equals($secret, $provided);
    }

    /** Polling endpoint for the Windows agent; protected by the cron token. */
    public function getPingJob()
    {
        if (! $this->hasValidCronToken()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }
        $state = $this->readPingJobState();
        if (! in_array(($state['status'] ?? ''), ['pending', 'running'], true)) return $this->response->setJSON(['status' => 'idle']);

        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        $origins = is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) : [];
        $origins = array_values(array_unique(array_map('trim', $origins)));
        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $origins = array_values(array_filter($origins, static function (string $origin) use ($whitelist): bool {
            $parts = parse_url($origin);
            return ! in_array(strtolower(rtrim((string) ($parts['host'] ?? ''), '.')), $whitelist, true);
        }));
        if (($state['status'] ?? '') === 'pending') {
            $state['status'] = 'running';
            $state['worker_started_at'] = date('Y-m-d H:i:s');
            $this->writePingJobState($state);
        }
        return $this->response->setJSON(['status' => 'pending', 'job_id' => $state['id'], 'origins' => $origins]);
    }

    public function pingScanStatus()
    {
        $state = $this->readPingJobState();
        $requestedId = (string) $this->request->getGet('job_id');
        if ($requestedId === '' || ($state['id'] ?? '') !== $requestedId) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'not_found']);
        }
        return $this->response->setJSON($state);
    }

    private function pingJobStatePath(): string
    {
        return WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'ping-scan-state.json';
    }

    private function readPingJobState(): array
    {
        $contents = @file_get_contents($this->pingJobStatePath());
        $state = is_string($contents) ? json_decode($contents, true) : null;
        return is_array($state) ? $state : [];
    }

    private function writePingJobState(array $state): void
    {
        file_put_contents($this->pingJobStatePath(), json_encode($state, JSON_THROW_ON_ERROR), LOCK_EX);
    }

    /** Create a ping scan request for the local Windows agent. */
    public function adminScan()
    {
        // Always rebuild the deduplicated origin list from the current database
        // so the administrator only needs to press the scan button.
        $generated = $this->generateLinkList();
        if ($generated->getStatusCode() >= 400) {
            return $generated;
        }

        $listPath = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        if (! is_file($listPath)) {
            return $this->response->setStatusCode(409)->setJSON(['status' => 'list_required', 'message' => 'Générez d’abord liste.log.']);
        }
        $origins = file($listPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $origins = array_values(array_unique(array_map('trim', $origins)));
        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $origins = array_values(array_filter($origins, static function (string $origin) use ($whitelist): bool {
            $parts = parse_url($origin);
            return ! in_array(strtolower(rtrim((string) ($parts['host'] ?? ''), '.')), $whitelist, true);
        }));
        return $this->response->setJSON([
            'status' => 'ready',
            'origins' => $origins,
            'total' => count($origins),
            'csrf_token' => csrf_hash(),
        ]);
    }

    /** Import Check-Host results from this session-authenticated admin page. */
    public function adminImportPingResults()
    {
        $payload = $this->request->getJSON(true);
        $results = is_array($payload) ? ($payload['results'] ?? null) : null;
        if (! is_array($results) || count($results) > 1000) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'invalid_payload']);
        }

        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        $allowedOrigins = is_file($path) ? array_map('trim', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) : [];
        $whitelist = array_map(static fn ($d) => strtolower(rtrim((string) $d, '.')), (new WhitelistModel())->findColumn('domain') ?? []);
        $db = db_connect();
        $hasLinkCheckedAt = $db->fieldExists('link_checked_at', 'item');
        $now = date('Y-m-d H:i:s');
        $deadCount = 0;
        $cronLogs = new CronLogModel();

        foreach ($results as $result) {
            if (! is_array($result) || ! isset($result['origin'], $result['status'])) continue;
            $origin = $this->genericOrigin((string) $result['origin']);
            $status = (string) $result['status'];
            if ($origin === null || ! in_array($origin, $allowedOrigins, true) || ! in_array($status, ['alive', 'dead', 'unknown'], true)) continue;
            $parts = parse_url($origin);
            $host = strtolower((string) ($parts['host'] ?? ''));
            if (in_array($host, $whitelist, true)) continue;

            foreach ($this->findItemsForOrigin($host, $parts['port'] ?? null) as $item) {
                $update = $hasLinkCheckedAt
                    ? ['link_checked_at' => $status === 'unknown' ? date('Y-m-d H:i:s', strtotime('-29 days')) : $now]
                    : [];
                if ($status === 'dead' && $item->link_status !== 'dead') $update['link_status'] = 'dead';
                elseif ($status === 'alive' && $item->link_status === 'dead') $update['link_status'] = 'ok';
                if ($update !== []) $db->table('item')->where('id', $item->id)->update($update);

                if ($status === 'dead' && $item->link_status !== 'dead') {
                    $cronLogs->insert([
                        'task_name' => 'check_dead_links', 'last_run' => $now,
                        'item_id' => $item->id, 'titre' => $item->titre,
                        'url_testee' => $origin, 'code_erreur' => 0,
                    ]);
                    ++$deadCount;
                }
            }
        }

        if ($deadCount > 0) (new AuditLogModel())->logAction('Cron', "Ping externe : {$deadCount} nouvelles cartes mortes détectées.");
        return $this->response->setJSON(['status' => 'imported', 'origins' => count($results), 'new_dead_cards' => $deadCount]);
    }

    /** Scan only the unique generic origins from the generated review list. */
    private function processOriginListBatch(int $afterIndex)
    {
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'liste.log';
        if (! is_file($path) || ! is_readable($path)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'list_required',
                'message' => 'Générez d’abord liste.log avec le bouton « Générer la liste des URL ».',
            ]);
        }

        $origins = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($origins === false) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Impossible de lire liste.log.']);
        }
        $origins = array_values(array_unique(array_map('trim', $origins)));
        $total = count($origins);
        if ($afterIndex >= $total) {
            return $this->response->setJSON(['status' => 'complete', 'complete' => true, 'checked' => 0, 'dead' => 0, 'next_index' => $total, 'total_origins' => $total]);
        }

        $started = microtime(true);
        $now = date('Y-m-d H:i:s');
        $db = db_connect();
        $hasLinkCheckedAt = $db->fieldExists('link_checked_at', 'item');
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
        $nextIndex = $afterIndex;
        $cronLogs = new CronLogModel();
        foreach (array_slice($origins, $afterIndex, self::MAX_ITEMS, true) as $index => $origin) {
            if (microtime(true) - $started >= self::MAX_SECONDS) break;
            $origin = $this->genericOrigin(trim($origin)) ?? '';
            $parts = parse_url($origin);
            $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
            if ($host === '' || ! ExternalUrlGuard::isPublicHttpUrl($origin)) {
                ++$inconclusive;
                $nextIndex = $index + 1;
                continue;
            }

            $items = $this->findItemsForOrigin($host, $parts['port'] ?? null);
            $isDead = false;
            $isConclusive = false;
            $statusCode = 0;
            if (in_array($host, $whitelist, true)) {
                $isConclusive = true;
            } else {
                try {
                    $answers = [];
                    foreach (['https://dns.google/resolve', 'https://cloudflare-dns.com/dns-query'] as $indexDns => $endpoint) {
                        $dns = $client->get($endpoint, [
                            'query' => ['name' => $host, 'type' => 'A'],
                            'headers' => $indexDns === 1 ? ['accept' => 'application/dns-json'] : [],
                        ]);
                        $data = json_decode((string) $dns->getBody(), true);
                        $answers[] = $dns->getStatusCode() === 200 && is_array($data) ? ($data['Status'] ?? null) : null;
                    }
                    if ($answers === [3, 3]) {
                        $isDead = $isConclusive = true;
                        $statusCode = 404;
                    } else {
                        $response = $client->get($origin);
                        $statusCode = $response->getStatusCode();
                        if (in_array($statusCode, [404, 410], true)) $isDead = $isConclusive = true;
                        elseif ($statusCode >= 200 && $statusCode < 400) $isConclusive = true;
                    }
                } catch (Throwable $e) {
                    log_message('debug', '[Cron] Origin inconclusive {host}: {error}', ['host' => $host, 'error' => $e->getMessage()]);
                }
            }

            if (! $isConclusive) ++$inconclusive;
            foreach ($items as $item) {
                $update = $hasLinkCheckedAt
                    ? ['link_checked_at' => $isConclusive ? $now : date('Y-m-d H:i:s', strtotime('-29 days'))]
                    : [];
                if ($isDead && $item->link_status !== 'dead') $update['link_status'] = 'dead';
                elseif ($isConclusive && ! $isDead && $item->link_status === 'dead') $update['link_status'] = 'ok';
                if ($update !== []) $db->table('item')->where('id', $item->id)->update($update);

                if ($isDead && $item->link_status !== 'dead') {
                    $cronLogs->insert([
                        'task_name' => 'check_dead_links', 'last_run' => $now,
                        'item_id' => $item->id, 'titre' => $item->titre,
                        'url_testee' => $origin, 'code_erreur' => $statusCode,
                    ]);
                    ++$dead;
                }
            }

            ++$checked;
            $nextIndex = $index + 1;
        }

        if ($dead > 0) (new AuditLogModel())->logAction('Cron', "Vérification auto : {$dead} nouveaux liens morts détectés.");
        $complete = $nextIndex >= $total;
        return $this->response->setJSON([
            'status' => $complete ? 'complete' : 'executed',
            'complete' => $complete,
            'checked' => $checked,
            'dead' => $dead,
            'inconclusive' => $inconclusive,
            'next_index' => $nextIndex,
            'total_origins' => $total,
        ]);
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

    /** Find active cards associated with one origin, ignoring their URL paths. */
    private function findItemsForOrigin(string $host, ?int $port): array
    {
        $target = strtolower($host) . ($port !== null && ! in_array($port, [80, 443], true) ? ':' . $port : '');
        $candidates = db_connect()->table('item')
            ->select('id, titre, lien, link_status')
            ->where('deleted_at', null)
            ->where('lien IS NOT NULL', null, false)
            ->like('lien', $host)
            ->get()->getResult();

        $matches = [];
        foreach ($candidates as $item) {
            $origin = $this->genericOrigin((string) $item->lien);
            if ($origin === null) continue;
            $parts = parse_url($origin);
            $key = strtolower((string) ($parts['host'] ?? '')) . (isset($parts['port']) ? ':' . $parts['port'] : '');
            if ($key === $target) $matches[] = $item;
        }

        return $matches;
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
