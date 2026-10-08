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
        'id_user',
        'id_division',
        'sous_categorie',
        'titre',
        'titre_original',
        'status',
        'is_public',
        'description',
        'date_sortie',
        'image',
        'lien',
        'link_status',
        'saison',
        'total_saisons',
        'episode',
        'total_episodes',
        'position',
        'episode_global',
        'total_episodes_global',
    ];

    // Active la corbeille au lieu de la suppression définitive.
    protected $useSoftDeletes = true;

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Récupère les cartes triées et structurées par Onglet > Division > Sous-catégorie.
     *
     * @param null|mixed $userId
     * @param null|mixed $headerId
     */
    public function getItemsGroupedByHeaderAndDivision(
        $userId = null,
        $headerId = null
    ) {
        $builder = $this->db
            ->table('item i')
            ->select('h.nom AS header_nom, d.nom AS division_nom, i.*')
            ->join('division d', 'i.id_division = d.id')
            ->join('header h', 'd.id_header = h.id')
            ->where('i.id_user !=', 0)
            ->where('i.deleted_at IS NULL');

        // Cartes publiques pour les visiteurs,
        // cartes personnelles + publiques pour le propriétaire.
        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder
                ->groupStart()
                    ->where('i.id_user', $userId)
                    ->orWhere('i.is_public', 1)
                ->groupEnd();
        }

        if (null !== $headerId) {
            $builder->where('h.id', $headerId);
        }

        $results = $builder
            ->orderBy('h.id', 'ASC')
            ->orderBy('d.id', 'ASC')
            ->orderBy('i.position', 'ASC')
            ->get()
            ->getCustomResultObject(Item::class);

        $groupedData = [];

        foreach ($results as $item) {
            $header = $item->header_nom;
            $division = $item->division_nom;
            $subCat = empty($item->sous_categorie)
                ? 'Sans sous-catégorie'
                : $item->sous_categorie;

            $groupedData[$header][$division][$subCat][] = $item;
        }

        return $groupedData;
    }

    /**
     * Récupère uniquement les onglets contenant au moins une carte visible.
     *
     * @param null|mixed $userId
     */
    public function getActiveHeaders($userId = null)
    {
        $builder = $this->db
            ->table('header h')
            ->select('h.*')
            ->distinct()
            ->join('division d', 'd.id_header = h.id')
            ->join('item i', 'i.id_division = d.id')
            ->where('i.deleted_at IS NULL')
            ->where('i.id_user !=', 0);

        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder
                ->groupStart()
                    ->where('i.id_user', $userId)
                    ->orWhere('i.is_public', 1)
                ->groupEnd();
        }

        return $builder
            ->orderBy('h.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Récupère toutes les divisions.
     */
    public function getDivisions()
    {
        return $this->db
            ->table('division')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Récupère tous les onglets.
     */
    public function getHeaders()
    {
        return $this->db
            ->table('header')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Récupère les cartes publiques appartenant à des utilisateurs standards.
     */
    public function checkToGlobal()
    {
        return $this
            ->where('id_division <=', 11)
            ->where('is_public', 1)
            ->where('id_user !=', 1)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Récupère la liste des cartes placées dans la corbeille.
     *
     * @param null|mixed $userId
     */
    public function getDeletedItems($userId = null)
    {
        $builder = $this->db
            ->table('item i')
            ->select('u.username AS author_name, i.*')
            ->join('users u', 'i.id_user = u.id', 'left')
            ->where('i.deleted_at IS NOT NULL');

        if (null !== $userId) {
            $builder->where('i.id_user', $userId);
        }

        return $builder
            ->orderBy('i.deleted_at', 'DESC')
            ->get()
            ->getCustomResultObject(Item::class);
    }

    /**
     * Récupère toutes les statistiques nécessaires au profil
     * avec UNE SEULE requête SQL.
     */
    public function getUserProfileStats(int $userId): array
    {
        $syncedCondition = "
            id_division IN (1, 4)
            AND episode IS NOT NULL
            AND total_episodes IS NOT NULL
        ";

        $enCoursCondition = "
            status = 'En cours'
            AND {$syncedCondition}
        ";

        $enPauseCondition = "
            status = 'En pause'
            AND {$syncedCondition}
        ";

        $row = $this->db
            ->table($this->table)
            ->select('COUNT(*) AS total_items', false)

            ->select(
                'SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) AS public_items',
                false
            )

            ->select(
                'SUM(CASE WHEN status = "À voir" THEN 1 ELSE 0 END) AS status_a_voir',
                false
            )

            ->select(
                'SUM(CASE WHEN status = "En cours" THEN 1 ELSE 0 END) AS status_en_cours',
                false
            )

            ->select(
                'SUM(CASE WHEN status = "En pause" THEN 1 ELSE 0 END) AS status_en_pause',
                false
            )

            ->select(
                'SUM(CASE WHEN status = "Terminé" THEN 1 ELSE 0 END) AS status_termine',
                false
            )

            ->select(
                'SUM(CASE WHEN status = "Aucun" THEN 1 ELSE 0 END) AS status_aucun',
                false
            )

            // Épisodes restants : EN COURS
            ->select(
                "SUM(
                    CASE
                        WHEN {$enCoursCondition}
                        THEN
                            COALESCE(total_episodes_global, total_episodes)
                            - COALESCE(episode_global, episode, 1)
                            + 1
                        ELSE 0
                    END
                ) AS total_episodes_en_cours",
                false
            )

            // Saisons restantes : EN COURS
            ->select(
                "SUM(
                    CASE
                        WHEN {$enCoursCondition}
                        THEN total_saisons - saison + 1
                        ELSE 0
                    END
                ) AS total_series_en_cours",
                false
            )

            // Épisodes restants : EN PAUSE
            ->select(
                "SUM(
                    CASE
                        WHEN {$enPauseCondition}
                        THEN
                            COALESCE(total_episodes_global, total_episodes)
                            - COALESCE(episode_global, episode, 1)
                            + 1
                        ELSE 0
                    END
                ) AS total_episodes_en_pause",
                false
            )

            // Saisons restantes : EN PAUSE
            ->select(
                "SUM(
                    CASE
                        WHEN {$enPauseCondition}
                        THEN total_saisons - saison + 1
                        ELSE 0
                    END
                ) AS total_series_en_pause",
                false
            )

            ->where('id_user', $userId)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        $defaults = [
            'total_items' => 0,
            'public_items' => 0,
            'status_a_voir' => 0,
            'status_en_cours' => 0,
            'status_en_pause' => 0,
            'status_termine' => 0,
            'status_aucun' => 0,
            'total_series_en_cours' => 0,
            'total_episodes_en_cours' => 0,
            'total_series_en_pause' => 0,
            'total_episodes_en_pause' => 0,
        ];

        $row = array_merge($defaults, $row ?? []);

        return array_map('intval', $row);
    }

    /**
     * Récupère toutes les séries en cours et en pause
     * avec UNE SEULE requête SQL.
     *
     * Le découpage En cours / En pause est ensuite effectué
     * en PHP afin d'éviter deux requêtes identiques.
     */
    public function getUserProfileSeries(int $userId): array
    {
        $rows = $this->db
            ->table($this->table)
            ->select(
                'titre,
                status,
                episode_global,
                total_episodes_global,
                episode,
                total_episodes,
                saison,
                total_saisons'
            )
            ->where('id_user', $userId)
            ->whereIn('status', ['En cours', 'En pause'])
            ->whereIn('id_division', [1, 4])
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('position', 'ASC')
            ->get()
            ->getResult();

        $series = [
            'en_cours' => [],
            'en_pause' => [],
        ];

        foreach ($rows as $row) {
            // Calcul identique à l'ancien fonctionnement.
            $row->vu_global = $row->episode_global !== null
                ? (int) $row->episode_global - 1
                : null;

            if (
                $row->total_episodes_global !== null ||
                $row->total_episodes !== null
            ) {
                $totalEpisodes = $row->total_episodes_global
                    ?? $row->total_episodes;

                $episodeActuel = $row->episode_global
                    ?? $row->episode
                    ?? 1;

                $row->reste_global = (int) $totalEpisodes
                    - (int) $episodeActuel
                    + 1;
            } else {
                $row->reste_global = null;
            }

            if (
                $row->total_saisons !== null &&
                $row->saison !== null
            ) {
                $row->reste_s = (int) $row->total_saisons
                    - (int) $row->saison
                    + 1;
            } else {
                $row->reste_s = null;
            }

            if ($row->status === 'En cours') {
                $series['en_cours'][] = $row;
            } else {
                $series['en_pause'][] = $row;
            }
        }

        return $series;
    }
}