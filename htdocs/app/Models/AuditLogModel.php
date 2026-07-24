<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'action', 'details', 'ip_address', 'created_at'];
    protected $useTimestamps = false; // Géré manuellement ou par SQL (CURRENT_TIMESTAMP)

    /**
     * Enregistre une nouvelle action dans l'historique.
     *
     * * @param string $action Nom court de l'action (ex: 'carte_creee')
     * @param string $details Explications détaillées (ex: 'Création de la carte ID 12')
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
     * Récupère les logs récents avec le nom de l'utilisateur.
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
