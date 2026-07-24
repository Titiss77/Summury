<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ItemRevisionModel extends Model
{
    protected $table = 'item_revisions';
    protected $primaryKey = 'id';
    protected $returnType = 'array'; // On peut utiliser des tableaux simples ici pour faciliter les manipulations
    protected $allowedFields = [
        'original_item_id',
        'id_user',
        'titre',
        'status',
        'image',
        'lien',
        'description',
        'episode',
        'saison',
        'position',
        'date_sortie',
        'revision_status',
    ];

    // Dates gérées automatiquement par la base (CURRENT_TIMESTAMP)
    protected $useTimestamps = false;

    /**
     * Récupère toutes les révisions en attente avec les infos de la carte originale.
     */
    public function getPendingRevisions()
    {
        return $this->db->table('item_revisions ir')
            ->select('ir.*, u.username as author_name, i.titre as original_titre')
            ->join('users u', 'ir.id_user = u.id')
            ->join('item i', 'ir.original_item_id = i.id')
            ->where('ir.revision_status', 'pending')
            ->get()
            ->getResultArray()
        ;
    }
}
