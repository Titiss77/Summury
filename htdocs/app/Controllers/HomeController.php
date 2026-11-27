<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;
use App\Models\SiteConfigModel;
use App\Models\WhitelistModel;

class HomeController extends BaseController
{
    public function __construct()
    {
        helper('auth');
    }

    private function reportDomainStates(): array
    {
        $states = [];
        $db = db_connect();
        $normalizeHost = static function (string $url): ?string {
            if (!preg_match('~^https?://~i', $url) && !str_starts_with($url, '//')) {
                $url = 'https://' . $url;
            } elseif (str_starts_with($url, '//')) {
                $url = 'https:' . $url;
            }
            $host = parse_url($url, PHP_URL_HOST);
            if (!is_string($host) || $host === '') {
                return null;
            }

            return (string) preg_replace('/^www\./i', '', strtolower(rtrim($host, '.')));
        };

        $logs = $db->table('cron_logs')->select('task_name, url_testee')
            ->whereIn('task_name', [CronLogModel::TASK_PENDING_USER_REPORT, CronLogModel::TASK_CONFIRMED_DEAD])
            ->get()->getResultArray();
        foreach ($logs as $log) {
            $host = $normalizeHost((string) ($log['url_testee'] ?? ''));
            if ($host === null) {
                continue;
            }
            $state = $log['task_name'] === CronLogModel::TASK_PENDING_USER_REPORT ? 'pending' : 'confirmed';
            if (!isset($states[$host]) || $state === 'confirmed') {
                $states[$host] = $state;
            }
        }

        $reportedItems = $db->table('item')->select('lien, link_status')
            ->where('deleted_at', null)
            ->whereIn('link_status', ['reported', 'dead'])
            ->get()->getResultArray();
        foreach ($reportedItems as $item) {
            $host = $normalizeHost((string) ($item['lien'] ?? ''));
            if ($host === null) {
                continue;
            }
            $state = $item['link_status'] === 'reported' ? 'pending' : 'confirmed';
            if (!isset($states[$host]) || $state === 'confirmed') {
                $states[$host] = $state;
            }
        }

        $whitelistCutoff = date('Y-m-d H:i:s', time() - (WhitelistModel::PROTECTION_DAYS * 86400));
        $whitelist = (new WhitelistModel())->where('created_at >=', $whitelistCutoff)->findAll();
        foreach ($whitelist as $entry) {
            $host = $normalizeHost((string) ($entry['domain'] ?? ''));
            if ($host !== null && !isset($states[$host])) {
                $states[$host] = 'whitelist';
            }
        }

        return $states;
    }

    /**
     * Page d'accueil par défaut. Redirige vers la première catégorie disponible.
     */
    public function index()
    {
        $model = new ItemModel();
        $isLoggedIn = auth()->loggedIn();
        $userId = $isLoggedIn ? auth()->id() : null;
        $this->response->noCache();

        $headersWithLogin = $isLoggedIn ? $model->getHeaders() : [];
        $headersWithNoLogin = $isLoggedIn ? $headersWithLogin : $model->getActiveHeaders(null);

        if (!empty($headersWithNoLogin) && !$this->request->getGet('i')) {
            return redirect()->to('c/' . $headersWithNoLogin[0]['id']);
        }

        $headerId = !empty($headersWithNoLogin) ? $headersWithNoLogin[0]['id'] : null;
        $groupedItems = $headerId ? $model->getItemsGroupedByHeaderAndDivision($userId, $headerId) : [];

        return view('home', [
            'headersWithNoLogin' => $headersWithNoLogin,
            'headersWithLogin' => $headersWithLogin,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'supportedDomains' => [],
            'reportDomainStates' => $this->reportDomainStates(),
        ]);
    }

    /**
     * Affiche les cartes d'une catégorie spécifique.
     *
     * @param mixed $headerId
     */
    public function categorie($headerId)
    {
        $model = new ItemModel();
        $isLoggedIn = auth()->loggedIn();
        $userId = $isLoggedIn ? auth()->id() : null;
        $isAjax = $this->request->isAJAX();
        $this->response->noCache();

        // Inutile de charger les onglets de navigation lors d'un swap AJAX
        $headersWithLogin = [];
        $headersWithNoLogin = [];
        if (!$isAjax) {
            if ($isLoggedIn) {
                $headersWithLogin = $model->getHeaders();
                $headersWithNoLogin = $headersWithLogin;
            } else {
                $headersWithNoLogin = $model->getActiveHeaders(null);
            }
        }

        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);

        $pendingTotal = 0;
        $toAdminCount = 0;
        $pendingRevisionIds = [];
        $passedReleases = [];

        if ($isLoggedIn) {
            $revModel = new ItemRevisionModel();
            $pendingRevisionIds = array_fill_keys(
                array_map('intval', $revModel->where('revision_status', 'pending')->findColumn('original_item_id') ?? []),
                true,
            );

            $passedReleases = $model->select('item.id, item.titre, item.date_sortie, d.nom')
                ->join('division d', 'item.id_division = d.id')
                ->where('item.id_user', $userId)
                ->where('item.date_sortie IS NOT NULL')
                ->where('item.date_sortie <=', date('Y-m-d H:i:s'))
                ->where('item.date_sortie >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->findAll();

            if (auth()->user()->inGroup('admin', 'superadmin')) {
                $pendingItemsCount = $model->where('is_public', 2)->countAllResults();
                $pendingRevisionsCount = $revModel->where('revision_status', 'pending')->countAllResults();
                $pendingTotal = $pendingItemsCount + $pendingRevisionsCount;

                $toAdminCount = $model->where('id_division <=', 11)
                    ->where('is_public', 1)
                    ->where('id_user !=', 1)
                    ->countAllResults();
            }
        }

        $supportedDomains = (new SiteConfigModel())->where('is_active', 1)->findColumn('domain') ?? [];

        return view('home', [
            'headersWithNoLogin' => $headersWithNoLogin,
            'headersWithLogin' => $headersWithLogin,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'pendingTotal' => $pendingTotal,
            'toAdminCount' => $toAdminCount,
            'supportedDomains' => $supportedDomains,
            'pendingRevisionIds' => $pendingRevisionIds,
            'passedReleases' => $passedReleases,
            'reportDomainStates' => $this->reportDomainStates(),
        ]);
    }

    public function legal()
    {
        return view('rgpd/legal');
    }

    public function privacy()
    {
        return view('rgpd/privacy');
    }

    public function cgu()
    {
        return view('rgpd/cgu');
    }
}