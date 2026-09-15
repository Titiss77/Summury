<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\SiteConfigModel;
use App\Models\ItemRevisionModel;

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
        
        // Empêche le navigateur de garder l'HTML en cache (Bfcache)
        $this->response->noCache();

        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);

        if (!empty($headersWithNoLogin)) {
            return redirect()->to('categorie/'.$headersWithNoLogin[0]['id']);
        }

        if (!empty($headersWithLogin)) {
            return redirect()->to('categorie/'.$headersWithLogin[0]['id']);
        }

        return view('home', [
            'headersWithNoLogin' => $headersWithNoLogin, 
            'headersWithLogin' => $headersWithLogin, 
            'groupedItems' => [], 
            'supportedDomains' => []
        ]);
    }

    public function categorie($headerId)
    {
        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;
        
        // Empêche le navigateur de garder l'HTML en cache pour un affichage en temps réel
        $this->response->noCache();

        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);
        
        // Requête directe sans passer par le cache serveur
        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);
        
        $pendingTotal = 0;
        $toAdminCount = 0;
        $pendingRevisionIds = [];
        $passedReleases = []; // <-- Nouvelle variable

        if (auth()->loggedIn()) {
            $revModel = new ItemRevisionModel();
            $pendingRevisionIds = $revModel->where('revision_status', 'pending')->findColumn('original_item_id') ?? [];
            
            // mais qui datent de moins de 7 jours.eferfsfr
            $passedReleases = $model->join('division d', 'id_division = d.id')
                                    ->where('id_user', $userId)
                                    ->where('date_sortie IS NOT NULL')
                                    ->where('date_sortie <=', date('Y-m-d H:i:s'))
                                    ->where('date_sortie >=', date('Y-m-d H:i:s', strtotime('-3 days')))
                                    ->findAll();
            
            if (auth()->user()->inGroup('admin', 'superadmin')) {
                $pendingItemsCount = $model->where('is_public', 2)->countAllResults();
                $pendingRevisionsCount = $revModel->where('revision_status', 'pending')->countAllResults();
                $pendingTotal = $pendingItemsCount + $pendingRevisionsCount;
                
                $toAdminCount = $model->where('id_division <', 11)
                    ->where('is_public', 1)
                    ->where('id_user !=', 1)
                    ->countAllResults();
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
            'passedReleases' => $passedReleases, // <-- On l'envoie à la vue
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
}