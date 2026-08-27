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
        'link_status', 'saison', 'episode', 'total_episodes', 'position',
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
            ->where('i.id_user !=', 0)->where('i.deleted_at IS NULL');
        
        if (null === $userId) {
            $builder->where('i.is_public', 2);
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
            if (!isset($groupedData[$header])) $groupedData[$header] = [];
            if (!isset($groupedData[$header][$division])) $groupedData[$header][$division] = [];
            if (!isset($groupedData[$header][$division][$subCat])) $groupedData[$header][$division][$subCat] = [];
            $groupedData[$header][$division][$subCat][] = $item;
        }
        return $groupedData;
    }

    public function getDivisions() { return $this->db->table('division')->orderBy('id', 'ASC')->get()->getResultArray(); }
    public function getHeaders() { return $this->db->table('header')->orderBy('id', 'ASC')->get()->getResultArray(); }
    public function checkToGlobal() { return $this->db->query('SELECT * FROM `item` WHERE id_division >= 5 AND id_division < 11 AND is_public = 1 AND deleted_at IS NULL;')->getCustomResultObject(Item::class); }
}