<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;

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

        $stats = $itemModel->getUserProfileStats($userId);
        $series = $itemModel->getUserProfileSeries($userId);

        $data = [
            'user' => $user,
            'totalItems' => $stats['total_items'],
            'publicItems' => $stats['public_items'],
            'statusAVoir' => $stats['status_a_voir'],
            'statusEnCours' => $stats['status_en_cours'],
            'statusEnPause' => $stats['status_en_pause'],
            'statusTermine' => $stats['status_termine'],
            'statusAucun' => $stats['status_aucun'],
            'totalSeriesEnCours' => $stats['total_series_en_cours'],
            'totalEpisodesEnCours' => $stats['total_episodes_en_cours'],
            'inProgressEnCoursSeries' => $series['en_cours'],
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
            'new_password'     => 'required|min_length[12]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $user = auth()->user();

        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('current_password'),
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

        return redirect()->to('p')->with('message', 'Votre mot de passe a été mis à jour avec succès.');
    }

    /**
     * Traite la mise à jour de l'adresse e-mail de l'utilisateur.
     */
    public function updateEmail()
    {
        $user = auth()->user();
        $newEmail = $this->request->getPost('new_email');

        if ($newEmail === $user->email) {
            return redirect()->back()->with('message', 'Cette adresse e-mail est déjà la vôtre.');
        }

        $rules = [
            'new_email'          => 'required|valid_email|is_unique[auth_identities.secret]',
            'password_for_email' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('password_for_email'),
        ];
        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $oldEmail = $user->email;
        $user->fill(['email' => $newEmail]);
        $users->save($user);

        $audit = new AuditLogModel();
        $audit->logAction('Modification Profil', "L'utilisateur ID {$user->id} a modifié son adresse e-mail ('{$oldEmail}' -> '{$newEmail}').");

        return redirect()->to('p')->with('message', 'Votre adresse e-mail a été mise à jour avec succès.');
    }

    /**
     * Traite la suppression définitive du compte utilisateur (Conformité RGPD)
     * et conserve les contributions publiques.
     */
    public function deleteAccount()
    {
        $user = auth()->user();
        $userId = (int) $user->id;

        $rules = [
            'password_for_deletion' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Le mot de passe est requis pour supprimer le compte.');
        }

        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('password_for_deletion'),
        ];
        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->with('error', 'Mot de passe incorrect. Suppression annulée.');
        }

        $itemModel = new ItemModel();
        
        // 1. Sauvegarde des contributions : Transfert des cartes publiques (is_public = 1) au Super Admin (ID = 1)
        $itemModel->builder()
                  ->where('id_user', $userId)
                  ->where('is_public', 1)
                  ->update(['id_user' => 1]);

        // 2. Suppression forte des cartes restantes (privées ou en attente d'approbation)
        $itemModel->builder()
                  ->where('id_user', $userId)
                  ->delete();

        // 3. Suppression des propositions de modifications (brouillons) en attente
        $revisionModel = new ItemRevisionModel();
        $revisionModel->where('id_user', $userId)->delete();

        $audit = new AuditLogModel();
        $audit->logAction('Suppression Compte', "L'utilisateur ID {$userId} ({$user->username}) a supprimé son compte définitivement. Ses cartes publiques ont été transférées à l'administration.");

        // 4. Suppression de l'utilisateur
        $users = auth()->getProvider();
        $users->delete($userId, true);

        // 5. Déconnexion
        auth()->logout();

        return redirect()->to('/')->with('message', 'Votre compte a été définitivement supprimé. Vos cartes publiques ont été conservées et anonymisées.');
    }
}