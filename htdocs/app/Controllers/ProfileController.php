<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ItemModel;

class ProfileController extends BaseController
{
    /**
     * Affiche le profil de l'utilisateur connecté ainsi que ses statistiques globales.
     */
    public function index()
    {
        $user = auth()->user();
        $itemModel = new ItemModel();

        $counts = $itemModel->getUserDashboardCounts((int) $user->id);

        $episodesStats = $itemModel->getGlobalEpisodesStats($user->id);
        $inProgressSeries = $itemModel->getInProgressSeriesStats($user->id);

        $data = [
            'user'          => $user,
            'totalItems'    => $counts['total_items'] ?? 0,
            'publicItems'   => $counts['public_items'] ?? 0,
            'statusAVoir'   => $counts['status_a_voir'] ?? 0,
            'statusEnCours' => $counts['status_en_cours'] ?? 0,
            'statusEnPause' => $counts['status_en_pause'] ?? 0,
            'statusTermine' => $counts['status_termine'] ?? 0,
            'statusAucun'   => $counts['status_aucun'] ?? 0,

            // Nouvelles variables injectées vers la vue
            'totalSeries'         => $episodesStats->total_series ?? 0,
            'totalEpisodes'    => $episodesStats->total_episodes ?? 0,
            'inProgressSeries' => $inProgressSeries,
        ];

        return view('profile/index', $data);
    }

    /**
     * Traite la mise à jour du mot de passe de l'utilisateur.
     */
    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $user = auth()->user();
        $currentPassword = $this->request->getPost('current_password');

        $credentials = [
            'email' => $user->email,
            'password' => $currentPassword,
        ];

        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        $audit = new AuditLogModel();
        $audit->logAction('Modification Profil', "L'utilisateur ID {$user->id} a modifié son mot de passe.");

        return redirect()->to('profile')->with('message', 'Votre mot de passe a été mis à jour avec succès.');
    }
}