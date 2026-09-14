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
        // Système de cache SQL
        $cache = \Config\Services::cache();
        $cacheKey = 'items_grouped_' . ($userId ?? 'public') . '_' . ($headerId ?? 'all');

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $builder = $this->db->table('item i')
            ->select('h.nom AS header_nom, d.nom AS division_nom, i.*')
            ->join('division d', 'i.id_division = d.id')
            ->join('header h', 'd.id_header = h.id')
            ->where('i.id_user !=', 0)->where('i.deleted_at IS NULL');

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

        // Sauvegarde pour 5 minutes
        $cache->save($cacheKey, $groupedData, 300);
        return $groupedData;
    }

    public function getActiveHeaders($userId = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'active_headers_' . ($userId ?? 'public');

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $builder = $this->db->table('header h')
            ->select('h.*')
            ->distinct()
            ->join('division d', 'd.id_header = h.id')
            ->join('item i', 'i.id_division = d.id')
            ->where('i.deleted_at IS NULL')
            ->where('i.id_user !=', 0);

        if (null === $userId) {
            $builder->where('i.is_public', 1); 
        } else {
            $builder->groupStart()->where('i.id_user', $userId)->orWhere('i.is_public', 1)->groupEnd();
        }

        $result = $builder->orderBy('h.id', 'ASC')->get()->getResultArray();
        $cache->save($cacheKey, $result, 300);
        
        return $result;
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
        return $this->where('id_division <', 11)->where('is_public', 1)->where('id_user !=', 1)->findAll();
    }
    
    public function getDeletedItems($userId = null)
    {
        $builder = $this->db->table('item i')
            ->select('u.username AS author_name, i.*')
            ->join('users u', 'i.id_user = u.id', 'left')
            ->where('i.deleted_at IS NOT NULL');

        if ($userId !== null) {
            $builder->where('i.id_user', $userId);
        }

        $builder->orderBy('i.deleted_at', 'DESC');
        return $builder->get()->getCustomResultObject(Item::class);
    }
}