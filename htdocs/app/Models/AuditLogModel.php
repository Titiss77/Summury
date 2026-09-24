<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

/**
 * Gère l'historique des actions (audit) pour la sécurité et la traçabilité.
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'action', 'details', 'ip_address', 'created_at'];

    // Géré manuellement lors de l'insertion pour plus de précision
    protected $useTimestamps = false;

    /**
     * Enregistre une nouvelle action dans l'historique de la plateforme.
     *
     * @param string $action  Nom court de l'action (ex: 'Création Carte')
     * @param string $details Explications détaillées
     */
    public function logAction(string $action, string $details = '')
    {
        $request = Services::request();
        $data = [
            'user_id' => auth()->loggedIn() ? auth()->id() : null,
            'action' => $action,
            'details' => $details,
            'ip_address' => $request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    /**
     * Récupère les logs récents en incluant le pseudo de l'utilisateur (si disponible).
     */
    public function getRecentLogs(int $limit = 200)
    {
        return $this->select('audit_logs.*, users.username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray()
        ;
    }
}
