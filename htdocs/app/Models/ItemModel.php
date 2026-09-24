<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Item;
use CodeIgniter\Model;

/**
 * Modèle principal gérant les cartes (Items) de l'application.
 */
class ItemModel extends Model
{
    protected $table = 'item';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = Item::class;
    protected $allowedFields = [
        'id_user', 'id_division', 'sous_categorie', 'titre', 'titre_original', 'status',
        'is_public', 'description', 'date_sortie', 'image', 'lien',
        'link_status', 'saison', 'total_saisons', 'episode', 'total_episodes', 'position',
        'episode_global', 'total_episodes_global'
    ];

    // Active la corbeille au lieu de la suppression définitive (Soft Deletes)
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Récupère les cartes triées et structurées par Onglet (Header) > Division > Sous-catégorie.
     *
     * @param null|mixed $userId
     * @param null|mixed $headerId
     */
    public function getItemsGroupedByHeaderAndDivision($userId = null, $headerId = null)
    {
        $builder = $this->db->table('item i')
            ->select('h.nom AS header_nom, d.nom AS division_nom, i.*')
            ->join('division d', 'i.id_division = d.id')
            ->join('header h', 'd.id_header = h.id')
            ->where('i.id_user !=', 0)
            ->where('i.deleted_at IS NULL')
        ;

        // Filtre de visibilité : Cartes publiques pour les visiteurs, ou mixtes pour le propriétaire
        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder->groupStart()
                ->where('i.id_user', $userId)
                ->orWhere('i.is_public', 1)
                ->groupEnd()
            ;
        }

        if (null !== $headerId) {
            $builder->where('h.id', $headerId);
        }

        $builder->orderBy('h.id', 'ASC')->orderBy('d.id', 'ASC')->orderBy('i.position', 'ASC');
        $results = $builder->get()->getCustomResultObject(Item::class);

        // Structuration des données pour l'affichage dans la vue
        $groupedData = [];
        foreach ($results as $item) {
            $header = $item->header_nom;
            $division = $item->division_nom;
            $subCat = empty($item->sous_categorie) ? 'Sans sous-catégorie' : $item->sous_categorie;

            if (!isset($groupedData[$header])) {
                $groupedData[$header] = [];
            }
            if (!isset($groupedData[$header][$division])) {
                $groupedData[$header][$division] = [];
            }
            if (!isset($groupedData[$header][$division][$subCat])) {
                $groupedData[$header][$division][$subCat] = [];
            }
            $groupedData[$header][$division][$subCat][] = $item;
        }

        return $groupedData;
    }

    /**
     * Récupère uniquement les onglets (Headers) contenant au moins une carte visible.
     *
     * @param null|mixed $userId
     */
    public function getActiveHeaders($userId = null)
    {
        $builder = $this->db->table('header h')
            ->select('h.*')
            ->distinct()
            ->join('division d', 'd.id_header = h.id')
            ->join('item i', 'i.id_division = d.id')
            ->where('i.deleted_at IS NULL')
            ->where('i.id_user !=', 0)
        ;

        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder->groupStart()
                ->where('i.id_user', $userId)
                ->orWhere('i.is_public', 1)
                ->groupEnd()
            ;
        }

        return $builder->orderBy('h.id', 'ASC')->get()->getResultArray();
    }

    /**
     * Récupère toutes les divisions (pour les formulaires).
     */
    public function getDivisions()
    {
        return $this->db->table('division')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    /**
     * Récupère tous les onglets, même vides (pour les formulaires).
     */
    public function getHeaders()
    {
        return $this->db->table('header')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    /**
     * Récupère les cartes publiques appartenant à des utilisateurs standards (transférables à l'admin).
     */
    public function checkToGlobal()
    {
        return $this->where('id_division <=', 11)
            ->where('is_public', 1)
            ->where('id_user !=', 1) // On exclut celles déjà possédées par le SuperAdmin
            ->orderBy('created_at', 'DESC')
            ->findAll()
        ;
    }

    /**
     * Récupère la liste des cartes placées dans la corbeille.
     *
     * @param null|mixed $userId
     */
    public function getDeletedItems($userId = null)
    {
        $builder = $this->db->table('item i')
            ->select('u.username AS author_name, i.*')
            ->join('users u', 'i.id_user = u.id', 'left')
            ->where('i.deleted_at IS NOT NULL')
        ;

        if (null !== $userId) {
            $builder->where('i.id_user', $userId);
        }

        $builder->orderBy('i.deleted_at', 'DESC');

        return $builder->get()->getCustomResultObject(Item::class);
    }

    /**
     * Récupère le total global des épisodes visionnés et restants de manière intelligente.
     */
    public function getGlobalEpisodesStats(int $userId)
    {
        return $this->select('
            SUM(
                CASE 
                    WHEN status = "Terminé" THEN COALESCE(episode_global, episode, 0)
                    ELSE COALESCE(episode_global, episode, 1) - 1 
                END
            ) as total_vus, 
            
            SUM(
                CASE 
                    WHEN status = "Terminé" THEN 0
                    WHEN COALESCE(total_episodes_global, total_episodes) IS NOT NULL THEN 
                        COALESCE(total_episodes_global, total_episodes) - COALESCE(episode_global, episode, 1) + 1
                    ELSE 0
                END
            ) as total_restants
        ')
        ->where('id_user', $userId)
        ->where('status !=', 'À voir') // On ignore les œuvres pas encore commencées
        ->where('episode IS NOT NULL')
        ->where('total_episodes IS NOT NULL')
        ->first();
    }

    /**
     * Récupère le détail des séries "En cours" avec le calcul exact des épisodes/saisons restants.
     */
    public function getInProgressSeriesStats(int $userId)
    {
        // vu_global = episode_global - 1
        // reste_global = total_episodes_global - episode_global + 1
        return $this->select('titre, (episode_global - 1) as vu_global, total_episodes_global, saison, total_saisons, (total_episodes_global - episode_global + 1) as reste_global, (total_saisons - saison + 1) as reste_s')
                    ->where('id_user', $userId)
                    ->where('status', 'En cours')
                    ->findAll();
    }
}