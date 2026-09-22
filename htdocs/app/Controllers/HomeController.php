<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\ItemRevisionModel;
use App\Models\SiteConfigModel;

class HomeController extends BaseController
{
    public function __construct()
    {
        helper('auth');
    }

    public function index()
    {
        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;
        $this->response->noCache();
        
        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);

        // Si un paramètre de redirection d'hébergeur est présent (comme ?i=1), 
        // on évite la redirection automatique pour casser la boucle infinie.
        if (!empty($headersWithNoLogin) && !$this->request->getGet('i')) {
            return redirect()->to('categorie/'.$headersWithNoLogin[0]['id']);
        }
        if (!empty($headersWithLogin) && !$this->request->getGet('i') && empty($headersWithNoLogin)) {
            return redirect()->to('categorie/'.$headersWithLogin[0]['id']);
        }

        $headerId = !empty($headersWithNoLogin) ? $headersWithNoLogin[0]['id'] : (!empty($headersWithLogin) ? $headersWithLogin[0]['id'] : null);
        
        $groupedItems = $headerId ? $model->getItemsGroupedByHeaderAndDivision($userId, $headerId) : [];
        
        // ... (conservez le reste du chargement des variables si besoin ou déléguez à categorie)
        
        return view('home', [
            'headersWithNoLogin' => $headersWithNoLogin,
            'headersWithLogin' => $headersWithLogin,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'supportedDomains' => [],
        ]);
    }

    public function categorie($headerId)
    {
        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;

        $this->response->noCache();

        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);

        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);

        $pendingTotal = 0;
        $toAdminCount = 0;
        $pendingRevisionIds = [];
        $passedReleases = [];

        if (auth()->loggedIn()) {
            $revModel = new ItemRevisionModel();
            $pendingRevisionIds = $revModel->where('revision_status', 'pending')->findColumn('original_item_id') ?? [];

            $passedReleases = $model->select('item.*, d.nom')
                ->join('division d', 'item.id_division = d.id')
                ->where('item.id_user', $userId)
                ->where('item.date_sortie IS NOT NULL')
                ->where('item.date_sortie <=', date('Y-m-d H:i:s'))
                ->where('item.date_sortie >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->findAll()
            ;

            if (auth()->user()->inGroup('admin', 'superadmin')) {
                $pendingItemsCount = $model->where('is_public', 2)->countAllResults();
                $pendingRevisionsCount = $revModel->where('revision_status', 'pending')->countAllResults();
                $pendingTotal = $pendingItemsCount + $pendingRevisionsCount;

                $toAdminCount = $model->where('id_division <', 11)
                    ->where('is_public', 1)
                    ->where('id_user !=', 1)
                    ->countAllResults()
                ;
            }
        }
        $siteConfigModel = new SiteConfigModel();
        $supportedDomains = $siteConfigModel->where('is_active', 1)->findColumn('domain') ?? [];

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

    // Nouvelle fonction CGU (Point 2)
    public function cgu()
    {
        return view('rgpd/cgu');
    }
}