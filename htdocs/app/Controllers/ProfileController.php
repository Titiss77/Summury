<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;

class ProfileController extends BaseController
{
    private const ALLOWED_TABS = ['profile', 'contributions', 'display', 'data', 'security', 'danger'];

    /**
     * Affiche le profil de l'utilisateur connecté avec gestion par onglets (?tab=...).
     */
    public function index()
    {
        $user = auth()->user();
        $userId = (int) $user->id;
        $itemModel = new ItemModel();

        $requestedTab = (string) ($this->request->getGet('tab') ?? 'profile');
        $activeTab = in_array($requestedTab, self::ALLOWED_TABS, true) ? $requestedTab : 'profile';

        $roleLabel = 'Membre';
        if ($user->inGroup('superadmin')) {
            $roleLabel = 'Super Administrateur';
        } elseif ($user->inGroup('admin')) {
            $roleLabel = 'Administrateur';
        }

        $data = [
            'user' => $user,
            'activeTab' => $activeTab,
            'roleLabel' => $roleLabel,
            'memberSince' => $this->formatMemberSince($user->created_at ?? null),
            'totalItems' => 0,
            'publicItems' => 0,
            'statusAVoir' => 0,
            'statusEnCours' => 0,
            'statusEnPause' => 0,
            'statusTermine' => 0,
            'statusAucun' => 0,
            'totalSeriesEnCours' => 0,
            'totalEpisodesEnCours' => 0,
            'inProgressEnCoursSeries' => [],
            'totalSeriesEnPause' => 0,
            'totalEpisodesEnPause' => 0,
            'inProgressEnPauseSeries' => [],
            'userPublicItems' => [],
            'userRevisions' => [],
            'userReports' => [],
            'deletedItemsCount' => 0,
            'recentLogs' => [],
        ];

        if ('profile' === $activeTab) {
            $stats = $itemModel->getUserProfileStats($userId);
            $series = $itemModel->getUserProfileSeries($userId);

            $data['totalItems'] = $stats['total_items'];
            $data['publicItems'] = $stats['public_items'];
            $data['statusAVoir'] = $stats['status_a_voir'];
            $data['statusEnCours'] = $stats['status_en_cours'];
            $data['statusEnPause'] = $stats['status_en_pause'];
            $data['statusTermine'] = $stats['status_termine'];
            $data['statusAucun'] = $stats['status_aucun'];
            $data['totalSeriesEnCours'] = $stats['total_series_en_cours'];
            $data['totalEpisodesEnCours'] = $stats['total_episodes_en_cours'];
            $data['inProgressEnCoursSeries'] = $series['en_cours'];
            $data['totalSeriesEnPause'] = $stats['total_series_en_pause'];
            $data['totalEpisodesEnPause'] = $stats['total_episodes_en_pause'];
            $data['inProgressEnPauseSeries'] = $series['en_pause'];
        } elseif ('contributions' === $activeTab) {
            $data['userPublicItems'] = $itemModel
                ->where('id_user', $userId)
                ->whereIn('is_public', [1, 2])
                ->orderBy('updated_at', 'DESC')
                ->findAll();

            $data['userRevisions'] = db_connect()->table('item_revisions ir')
                ->select('ir.id, ir.original_item_id, ir.titre, ir.revision_status, i.titre AS original_titre')
                ->join('item i', 'ir.original_item_id = i.id', 'left')
                ->where('ir.id_user', $userId)
                ->orderBy('ir.id', 'DESC')
                ->limit(25)
                ->get()
                ->getResultArray();

            $data['userReports'] = (new AuditLogModel())
                ->where('user_id', $userId)
                ->where('action', 'Signalement Utilisateur')
                ->orderBy('created_at', 'DESC')
                ->findAll(20);
        } elseif ('data' === $activeTab) {
            $data['totalItems'] = $itemModel
                ->where('id_user', $userId)
                ->countAllResults();

            $data['deletedItemsCount'] = (new ItemModel())
                ->onlyDeleted()
                ->where('id_user', $userId)
                ->countAllResults();
        } elseif ('security' === $activeTab) {
            $data['recentLogs'] = (new AuditLogModel())
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll(15);
        }

        return view('profile/index', $data);
    }

    /**
     * Exporte la collection de l'utilisateur connecté au format JSON ou CSV.
     */
    public function exportData(string $format)
    {
        $format = strtolower(trim($format));
        if (!in_array($format, ['json', 'csv'], true)) {
            return redirect()->to('p?tab=data')->with('error', "Format d'export non pris en charge.");
        }

        $userId = (int) auth()->id();
        $rows = db_connect()->table('item i')
            ->select('i.id, d.nom AS division, i.sous_categorie, i.titre, i.status, i.is_public, i.saison, i.total_saisons, i.episode, i.total_episodes, i.date_sortie, i.lien, i.image, i.description, i.created_at, i.updated_at')
            ->join('division d', 'i.id_division = d.id', 'left')
            ->where('i.id_user', $userId)
            ->where('i.deleted_at IS NULL', null, false)
            ->orderBy('i.id_division', 'ASC')
            ->orderBy('i.position', 'ASC')
            ->get()
            ->getResultArray();

        (new AuditLogModel())->logAction('Export Données', 'Export de la collection au format ' . strtoupper($format) . ' (' . count($rows) . ' carte(s)).');

        $dateSuffix = date('Y-m-d');

        if ('json' === $format) {
            $payload = json_encode([
                'exported_at' => date('c'),
                'username' => auth()->user()->username,
                'count' => count($rows),
                'items' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $this->response
                ->setHeader('Content-Type', 'application/json; charset=UTF-8')
                ->setHeader('Content-Disposition', "attachment; filename=\"collection-{$dateSuffix}.json\"")
                ->setBody((string) $payload);
        }

        $stream = fopen('php://temp', 'r+');
        // BOM UTF-8 pour une ouverture propre sous Excel
        fwrite($stream, "\xEF\xBB\xBF");

        $headers = [
            'ID',
            'Division',
            'Sous-catégorie',
            'Titre',
            'Statut',
            'Visibilité',
            'Saison',
            'Total saisons',
            'Épisode',
            'Total épisodes',
            'Date de sortie',
            'Lien',
            'Image',
            'Description',
            'Créée le',
            'Modifiée le',
        ];
        fputcsv($stream, $headers, ';');

        foreach ($rows as $row) {
            $visibility = match ((int) ($row['is_public'] ?? 0)) {
                1 => 'Publique',
                2 => 'En attente',
                default => 'Privée',
            };

            fputcsv($stream, [
                $row['id'] ?? '',
                $row['division'] ?? '',
                $row['sous_categorie'] ?? '',
                $row['titre'] ?? '',
                $row['status'] ?? '',
                $visibility,
                $row['saison'] ?? '',
                $row['total_saisons'] ?? '',
                $row['episode'] ?? '',
                $row['total_episodes'] ?? '',
                $row['date_sortie'] ?? '',
                $row['lien'] ?? '',
                $row['image'] ?? '',
                $row['description'] ?? '',
                $row['created_at'] ?? '',
                $row['updated_at'] ?? '',
            ], ';');
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"collection-{$dateSuffix}.csv\"")
            ->setBody((string) $csvContent);
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
            return redirect()->to('p?tab=security')->with('errors', $this->validator->getErrors());
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
            return redirect()->to('p?tab=security')->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $user->password = $this->request->getPost('new_password');
        $users->save($user);

        $audit = new AuditLogModel();
        $audit->logAction('Modification Profil', "L'utilisateur ID {$user->id} a modifié son mot de passe.");

        return redirect()->to('p?tab=security')->with('message', 'Votre mot de passe a été mis à jour avec succès.');
    }

    /**
     * Traite la mise à jour de l'adresse e-mail de l'utilisateur.
     */
    public function updateEmail()
    {
        $user = auth()->user();
        $newEmail = $this->request->getPost('new_email');

        if ($newEmail === $user->email) {
            return redirect()->to('p?tab=security')->with('message', 'Cette adresse e-mail est déjà la vôtre.');
        }

        $rules = [
            'new_email'          => 'required|valid_email|is_unique[auth_identities.secret]',
            'password_for_email' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('p?tab=security')->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();
        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('password_for_email'),
        ];
        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->to('p?tab=security')->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $oldEmail = $user->email;
        $user->fill(['email' => $newEmail]);
        $users->save($user);

        $audit = new AuditLogModel();
        $audit->logAction('Modification Profil', "L'utilisateur ID {$user->id} a modifié son adresse e-mail ('{$oldEmail}' -> '{$newEmail}').");

        return redirect()->to('p?tab=security')->with('message', 'Votre adresse e-mail a été mise à jour avec succès.');
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
            return redirect()->to('p?tab=danger')->with('error', 'Le mot de passe est requis pour supprimer le compte.');
        }

        $credentials = [
            'email'    => $user->email,
            'password' => $this->request->getPost('password_for_deletion'),
        ];
        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return redirect()->to('p?tab=danger')->with('error', 'Mot de passe incorrect. Suppression annulée.');
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

    /**
     * Formate la date d'inscription en français (ex: "septembre 2026").
     */
    private function formatMemberSince(mixed $createdAt): string
    {
        if (empty($createdAt)) {
            return 'Membre vérifié';
        }

        $timestamp = is_object($createdAt) && method_exists($createdAt, 'getTimestamp')
            ? $createdAt->getTimestamp()
            : strtotime((string) $createdAt);

        if (false === $timestamp) {
            return 'Membre vérifié';
        }

        $months = [
            1 => 'janvier',
            2 => 'février',
            3 => 'mars',
            4 => 'avril',
            5 => 'mai',
            6 => 'juin',
            7 => 'juillet',
            8 => 'août',
            9 => 'septembre',
            10 => 'octobre',
            11 => 'novembre',
            12 => 'décembre',
        ];

        $monthName = $months[(int) date('n', $timestamp)] ?? '';

        return 'Depuis ' . $monthName . ' ' . date('Y', $timestamp);
    }
}