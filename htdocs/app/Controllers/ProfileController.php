<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\AuditLogModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $itemModel = new ItemModel();

        // Récupération des statistiques
        $totalItems = $itemModel->where('id_user', $user->id)->countAllResults();
        $publicItems = $itemModel->where('id_user', $user->id)->where('is_public', 1)->countAllResults();

        $data = [
            'user' => $user,
            'totalItems' => $totalItems,
            'publicItems' => $publicItems,
        ];

        return view('profile/index', $data);
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $user = auth()->user();
        
        $currentPassword = $this->request->getPost('current_password');
        
        // CodeIgniter Shield : Vérification de l'ancien mot de passe
        $credentials = [
            'email'    => $user->email,
            'password' => $currentPassword
        ];

        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        // Enregistrement du nouveau mot de passe (Shield gère le hashage automatiquement)
        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        // Historique de sécurité
        $audit = new AuditLogModel();
        $audit->logAction('Modification Profil', "L'utilisateur ID {$user->id} a modifié son mot de passe.");

        return redirect()->to('profile')->with('message', 'Votre mot de passe a été mis à jour avec succès.');
    }
}