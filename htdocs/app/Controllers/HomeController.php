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
        if (!auth()->loggedIn()) {
            // Met en cache la page pendant 5 minutes (300 secondes) pour les visiteurs
            $this->cachePage(300); 
        }

        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;
        
        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);

        if (!empty($headersWithNoLogin)) {
            return redirect()->to('categorie/'.$headersWithNoLogin[0]['id']);
        }
        if (!empty($headersWithLogin)) {
            return redirect()->to('categorie/'.$headersWithLogin[0]['id']);
        }

        return view('home', ['headersWithNoLogin' => $headersWithNoLogin, 'headersWithLogin' => $headersWithLogin, 'groupedItems' => [], 'supportedDomains' => []]);
    }

    public function categorie($headerId)
    {
        if (!auth()->loggedIn()) {
            // Cache la page des catégories pour les visiteurs
            $this->cachePage(300); 
        }

        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;
        
        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);
        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);
        
        $pendingCount = 0;
        $toAdminCount = 0;
        if (auth()->loggedIn() && auth()->user()->inGroup('admin', 'superadmin')) {
            $pendingCount = $model->where('is_public', 2)->countAllResults();
            $toAdminCount = $model->where('id_division <', 11)
                ->where('is_public', 1)
                ->where('id_user !=', 1)
                ->countAllResults();
        }

        $siteConfigModel = new SiteConfigModel();
        $supportedDomains = $siteConfigModel->where('is_active', 1)->findColumn('domain') ?? [];
        
        $pendingRevisionIds = [];
        if (auth()->loggedIn()) {
            $revModel = new ItemRevisionModel();
            $pendingRevisionIds = $revModel->where('revision_status', 'pending')
                                           ->findColumn('original_item_id') ?? [];
        }

        return view('home', [
            'headersWithNoLogin' => $headersWithNoLogin,
            'headersWithLogin' => $headersWithLogin,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'pendingCount' => $pendingCount,
            'toAdminCount' => $toAdminCount,
            'supportedDomains' => $supportedDomains,
            'pendingRevisionIds' => $pendingRevisionIds,
        ]);
    }

    public function legal()
    {
        $this->cachePage(86400); // 24h
        return view('rgpd/legal');
    }
    
    public function privacy()
    {
        $this->cachePage(86400); // 24h
        return view('rgpd/privacy');
    }
}