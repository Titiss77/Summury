<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Item;
use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table = 'item';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    // On force le chemin absolu (le \ au début) pour éviter le bug "App\Models\Item"
    protected $returnType = Item::class;

    protected $allowedFields = [
        'id_user', 'id_division', 'titre', 'titre_original', 'status',
        'is_public', 'description', 'date_sortie', 'image', 'lien',
        'link_status', 'saison', 'episode', 'position',
    ];

    // --- ACTIVATION DU SOFT DELETE ---
    protected $useSoftDeletes = true;
    protected $useTimestamps = true; // Requis pour remplir automatiquement les dates
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getItemsGroupedByHeaderAndDivision($userId = null, $headerId = null)
    {
        $builder = $this
            ->db
            ->table('item i')
            ->select('h.nom AS header_nom, d.nom AS division_nom, i.*')
            ->join('division d', 'i.id_division = d.id')
            ->join('header h', 'd.id_header = h.id')
            ->where('i.id_user !=', 0) // Exclure l'utilisateur 0
            ->where('i.deleted_at IS NULL') // <--- AJOUT VITAL : Exclure les éléments dans la corbeille
        ;

        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder
                ->groupStart()
                ->where('i.id_user', $userId)
                ->orWhere('i.is_public', 1)
                ->groupEnd()
            ;
        }

        if (null !== $headerId) {
            $builder->where('h.id', $headerId);
        }

        // Tri par ordre ajouté
        $builder
            ->orderBy('h.id', 'ASC')
            ->orderBy('d.id', 'ASC')
            ->orderBy('i.position', 'ASC')
            ->orderBy('i.titre', 'ASC')
        ;

        // On utilise la classe absolue ici aussi
        $results = $builder->get()->getCustomResultObject(Item::class);

        $groupedData = [];
        foreach ($results as $item) {
            $header = $item->header_nom;
            $division = $item->division_nom;

            if (!isset($groupedData[$header])) {
                $groupedData[$header] = [];
            }
            if (!isset($groupedData[$header][$division])) {
                $groupedData[$header][$division] = [];
            }

            $groupedData[$header][$division][] = $item;
        }

        return $groupedData;
    }

    public function getDivisions()
    {
        return $this->db->table('division')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    public function getHeaders()
    {
        return $this->db->table('header')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    public function checkToGlobal()
    {
        // On exclut les éléments supprimés (Soft Delete) de cette requête manuelle
        $command = 'SELECT * FROM `item` WHERE id_division >= 5 AND id_division < 11 AND is_public = 1 AND deleted_at IS NULL;';

        return $this->db->query($command)->getCustomResultObject(Item::class);
    }
}
