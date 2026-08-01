<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\SiteConfigModel;

class HomeController extends BaseController
{
    public function __construct()
    {
        // Charge explicitement le helper auth de Shield pour que la fonction auth() soit reconnue
        helper('auth');
    }
         
    public function index()
    {
        $model = new ItemModel();
        $headers = $model->getHeaders();
        if (!empty($headers)) {
            return redirect()->to('categorie/'.$headers[0]['id']);
        }
        return view('home', ['headers' => [], 'groupedItems' => [], 'supportedDomains' => []]);
    }

    public function categorie($headerId)
    {
        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;
        $headers = $model->getHeaders();
        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);
        $pendingCount = 0;
        $toAdminCount = 0;

        if (auth()->loggedIn() && auth()->user()->inGroup('admin', 'superadmin')) {
            $pendingCount = $model->where('is_public', 2)->countAllResults();
            $toAdminCount = $model->where('id_division >=', 5)
                ->where('id_division <', 11)
                ->where('is_public', 1)
                ->where('id_user !=', 1)
                ->countAllResults();
        }

        // ==========================================
        // NOUVEAU : Récupération des domaines supportés en BDD
        // ==========================================
        $siteConfigModel = new SiteConfigModel();
        // On récupère uniquement la colonne 'domain' des sites actifs
        $supportedDomains = $siteConfigModel->where('is_active', 1)->findColumn('domain') ?? [];

        return view('home', [
            'headers' => $headers,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'pendingCount' => $pendingCount,
            'toAdminCount' => $toAdminCount,
            'supportedDomains' => $supportedDomains, // Transmis à la vue
        ]);
    }
}