<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Gère les brouillons (drafts) et propositions de modifications pour les cartes publiques.
 */
class ItemRevisionModel extends Model
{
    protected $table = 'item_revisions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'original_item_id', 'id_user', 'titre', 'sous_categorie', 'status',
        'image', 'lien', 'description', 'episode', 'total_episodes',
        'saison', 'total_saisons', 'position', 'date_sortie', 'revision_status',
    ];
    protected $useTimestamps = false;

    /**
     * Récupère la liste des modifications en attente d'approbation par la modération.
     */
    public function getPendingRevisions()
    {
        return $this->db->table('item_revisions ir')
            ->select('ir.*, u.username as author_name, i.titre as original_titre')
            ->join('users u', 'ir.id_user = u.id')
            ->join('item i', 'ir.original_item_id = i.id')
            ->where('ir.revision_status', 'pending')
            ->get()->getResultArray()
        ;
    }
}
