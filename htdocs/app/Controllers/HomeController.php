<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemModel;

class HomeController extends BaseController
{
    public function index()
    {
        $model = new ItemModel();
        $headers = $model->getHeaders();

        if (!empty($headers)) {
            return redirect()->to('categorie/'.$headers[0]['id']);
        }

        return view('home', ['headers' => [], 'groupedItems' => []]);
    }

    public function categorie($headerId)
    {
        $model = new ItemModel();
        $userId = auth()->loggedIn() ? auth()->id() : null;

        $headers = $model->getHeaders();
        $groupedItems = $model->getItemsGroupedByHeaderAndDivision($userId, $headerId);

        $pendingCount = 0;
        $toAdminCount = 0; // --- NOUVEAU COMPTEUR ---

        if (auth()->loggedIn() && auth()->user()->inGroup('admin', 'superadmin')) {
            // Compte des cartes en attente d'inspection
            $pendingCount = $model->where('is_public', 2)->countAllResults();

            // --- NOUVEAU : Compte des cartes à passer en admin ---
            $toAdminCount = $model->where('id_division >=', 5)
                ->where('id_division <', 11)
                ->where('is_public', 1)
                ->where('id_user !=', 1)
                ->countAllResults()
            ;
        }

        return view('home', [
            'headers' => $headers,
            'groupedItems' => $groupedItems,
            'currentHeaderId' => $headerId,
            'pendingCount' => $pendingCount,
            'toAdminCount' => $toAdminCount, // On passe ce nouveau compteur à la vue
        ]);
    }
}
