<?php declare(strict_types=1);

namespace App\Models;

use App\Entities\Item;
use CodeIgniter\Model;

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
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getItemsGroupedByHeaderAndDivision($userId = null, $headerId = null)
    {
        $builder = $this->db->table('item i')
            ->select('h.nom AS header_nom, d.nom AS division_nom, i.*')
            ->join('division d', 'i.id_division = d.id')
            ->join('header h', 'd.id_header = h.id')
            ->where('i.id_user !=', 0)->where('i.deleted_at IS NULL')
        ;

        if (null === $userId) {
            $builder->where('i.is_public', 1);
        } else {
            $builder->groupStart()->where('i.id_user', $userId)->orWhere('i.is_public', 1)->groupEnd();
        }

        if (null !== $headerId) {
            $builder->where('h.id', $headerId);
        }

        $builder->orderBy('h.id', 'ASC')->orderBy('d.id', 'ASC')->orderBy('i.position', 'ASC');
        $results = $builder->get()->getCustomResultObject(Item::class);

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

    // Nouvelle méthode pour ne récupérer que les onglets contenant des cartes
    public function getActiveHeaders($userId = null)
    {
        $builder = $this->db->table('header h')
            ->select('h.*')
            ->distinct() // Pour ne pas récupérer la catégorie en double si elle a plusieurs cartes
            ->join('division d', 'd.id_header = h.id')
            ->join('item i', 'i.id_division = d.id')
            ->where('i.deleted_at IS NULL')
            ->where('i.id_user !=', 0);

        // On filtre selon ce que la personne a le droit de voir
        if (null === $userId) {
            $builder->where('i.is_public', 1); // Visiteur = Uniquement publique
        } else {
            // Utilisateur co = Ses propres cartes OU les cartes publiques
            $builder->groupStart()->where('i.id_user', $userId)->orWhere('i.is_public', 1)->groupEnd();
        }

        return $builder->orderBy('h.id', 'ASC')->get()->getResultArray();
    }

    public function getDivisions()
    {
        return $this->db->table('division')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    public function getHeaders()
    {
        // On garde cette méthode intacte pour que le formulaire de création continue d'afficher TOUTES les catégories
        return $this->db->table('header')->orderBy('id', 'ASC')->get()->getResultArray();
    }

    public function checkToGlobal()
    {
        return $this->db->query('SELECT * FROM `item` WHERE id_division >= 5 AND id_division < 11 AND is_public = 1 AND deleted_at IS NULL;')->getCustomResultObject(Item::class);
    }
    
    public function getDeletedItems($userId = null)
    {
        $builder = $this->db->table('item i')
            ->select('u.username AS author_name, i.*')
            ->join('users u', 'i.id_user = u.id', 'left')
            ->where('i.deleted_at IS NOT NULL');

        // Si le contrôleur a passé un ID (c'est-à-dire que ce n'est pas un admin)
        // on filtre pour n'afficher que ses cartes.
        // Si l'ID est null (passé par l'admin), on ne filtre pas.
        if ($userId !== null) {
            $builder->where('i.id_user', $userId);
        }

        // On trie par date de suppression de la plus récente à la plus ancienne
        $builder->orderBy('i.deleted_at', 'DESC');

        return $builder->get()->getCustomResultObject(Item::class);
    }
}