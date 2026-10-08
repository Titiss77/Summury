<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ItemModel;

class ProfileController extends BaseController
{
    /**
     * Affiche le profil de l'utilisateur connecté.
     */
    public function index()
    {
        $user = auth()->user();
        $itemModel = new ItemModel();
        $userId = (int) $user->id;

        // 2 requêtes SQL seulement :
        // 1. Tous les compteurs + statistiques globales
        // 2. Liste des séries en cours / en pause
        $stats = $itemModel->getUserProfileStats($userId);
        $series = $itemModel->getUserProfileSeries($userId);

        $data = [
            'user' => $user,

            // Cartes
            'totalItems' => $stats['total_items'],
            'publicItems' => $stats['public_items'],

            // Statuts
            'statusAVoir' => $stats['status_a_voir'],
            'statusEnCours' => $stats['status_en_cours'],
            'statusEnPause' => $stats['status_en_pause'],
            'statusTermine' => $stats['status_termine'],
            'statusAucun' => $stats['status_aucun'],

            // Séries en cours
            'totalSeriesEnCours' => $stats['total_series_en_cours'],
            'totalEpisodesEnCours' => $stats['total_episodes_en_cours'],
            'inProgressEnCoursSeries' => $series['en_cours'],

            // Séries en pause
            'totalSeriesEnPause' => $stats['total_series_en_pause'],
            'totalEpisodesEnPause' => $stats['total_episodes_en_pause'],
            'inProgressEnPauseSeries' => $series['en_pause'],
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
            return redirect()->back()->with(
                'errors',
                $this->validator->getErrors()
            );
        }

        $users = auth()->getProvider();
        $user = auth()->user();

        $credentials = [
            'email' => $user->email,
            'password' => $this->request->getPost('current_password'),
        ];

        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->with(
                'error',
                'Le mot de passe actuel est incorrect.'
            );
        }

        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        $audit = new AuditLogModel();
        $audit->logAction(
            'Modification Profil',
            "L'utilisateur ID {$user->id} a modifié son mot de passe."
        );

        return redirect()
            ->to('p')
            ->with(
                'message',
                'Votre mot de passe a été mis à jour avec succès.'
            );
    }
}