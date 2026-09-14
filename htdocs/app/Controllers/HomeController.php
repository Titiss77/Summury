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
        
        // OPTIMISATION : Mise en cache pour les visiteurs non connectés (5 minutes)
        $cacheKey = 'home_category_' . $headerId . '_' . ($userId ?? 'guest');
        $cache = \Config\Services::cache();
        
        $headersWithNoLogin = $model->getActiveHeaders($userId);
        $headersWithLogin = $model->getHeaders($userId);
        
        if (!$groupedItems = $cache->get($cacheKey)) {
            $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);
            $cache->save($cacheKey, $groupedItems, 300);
        }
        
        $pendingTotal = 0;
        $toAdminCount = 0;
        $pendingRevisionIds = [];

        // Les calculs lourds ne sont faits que si l'utilisateur est connecté
        if (auth()->loggedIn()) {
            $revModel = new ItemRevisionModel();
            $pendingRevisionIds = $revModel->where('revision_status', 'pending')->findColumn('original_item_id') ?? [];
            
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