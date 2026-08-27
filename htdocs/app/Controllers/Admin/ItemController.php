<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;

class ItemController extends BaseController
{
    public function pending()
    {
        helper('text');
        $itemModel = new ItemModel();
        $revisionModel = new ItemRevisionModel();
        $revisions = $revisionModel->getPendingRevisions();
        foreach ($revisions as &$revision) {
            $original = $itemModel->asArray()->find($revision['original_item_id']);
            $changes = [];
            $fieldsToCompare = [
                'titre' => 'Titre',
                'sous_categorie' => 'Sous-cat gorie',
                'status' => 'Statut',
                'image' => 'Image / Couverture',
                'lien' => 'Lien de visionnage/lecture',
                'description' => 'Description',
                'episode' => ' pisode',
                'total_episodes' => 'Total  pisodes',
                'saison' => 'Saison',
                'date_sortie' => 'Date de sortie',
            ];
            if ($original) {
                foreach ($fieldsToCompare as $field => $label) {
                    $oldValue = $original[$field] ?? '';
                    $newValue = $revision[$field] ?? '';
                    if ('date_sortie' === $field && (!empty($oldValue) || !empty($newValue))) {
                        $oldValue = $oldValue ? substr((string) $oldValue, 0, 10) : '';
                        $newValue = $newValue ? substr((string) $newValue, 0, 10) : '';
                    }
                    if ((string) $oldValue !== (string) $newValue) {
                        $changes[] = [
                            'field' => $field, 'label' => $label,
                            'old' => $oldValue, 'new' => $newValue,
                        ];
                    }
                }
            }
            $revision['changes'] = $changes;
        }
        $data = ['pendingItems' => $itemModel->where('is_public', 2)->findAll(), 'pendingRevisions' => $revisions];
        return view('admin/items/pending', $data);
    }
    public function approve($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        if ($item) {
            $itemModel->update($id, ['is_public' => 1]);
            (new AuditLogModel())->logAction('Mod ration : Approbation Carte', "Le SuperAdmin a valid  la nouvelle publication de la carte ID {$id} ('{$item->titre}').");
        }
        return redirect()->back()->with('message', 'Nouvelle carte valid e ! Elle est d sormais visible de tous.');
    }
    public function reject($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        if ($item) {
            $itemModel->update($id, ['is_public' => 0]);
            (new AuditLogModel())->logAction('Mod ration : Refus Carte', "Le SuperAdmin a refus  la publication de la carte ID {$id} ('{$item->titre}'). R trogradation en priv .");
        }
        return redirect()->back()->with('error', "Nouvelle carte refus e. Elle est repass e en priv  pour l'utilisateur.");
    }
    public function approveRevision($revisionId)
    {
        $revisionModel = new ItemRevisionModel();
        $itemModel = new ItemModel();
        $revision = $revisionModel->find($revisionId);
        if ($revision && 'pending' === $revision['revision_status']) {
            $updateData = [
                'titre' => $revision['titre'], 'sous_categorie' => $revision['sous_categorie'],
                'status' => $revision['status'], 'image' => $revision['image'],
                'lien' => $revision['lien'], 'description' => $revision['description'],
                'episode' => $revision['episode'], 'total_episodes' => $revision['total_episodes'],
                'saison' => $revision['saison'], 'date_sortie' => $revision['date_sortie'],
            ];
            $itemModel->update($revision['original_item_id'], $updateData);
            $revisionModel->update($revisionId, ['revision_status' => 'approved']);
            (new AuditLogModel())->logAction('Mod ration : Approbation Draft', "Validation du Draft ID {$revisionId}. Les donn es de la carte publique ID {$revision['original_item_id']} ('{$revision['titre']}') ont  t   cras es avec succ s.");
            return redirect()->back()->with('message', 'La modification a  t  fusionn e et est maintenant en ligne.');
        }
        return redirect()->back()->with('error', 'R vision introuvable ou d j  trait e.');
    }
    public function rejectRevision($revisionId)
    {
        $revisionModel = new ItemRevisionModel();
        $revision = $revisionModel->find($revisionId);
        if ($revision) {
            $revisionModel->update($revisionId, ['revision_status' => 'rejected']);
            (new AuditLogModel())->logAction('Mod ration : Refus Draft', "Rejet du Draft ID {$revisionId} pour la carte ID {$revision['original_item_id']}. La version publique n'a pas  t  affect e.");
        }
        return redirect()->back()->with('error', 'La modification a  t  refus e.');
    }
    public function delete($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        if ($item) {
            $itemModel->delete($id);
            (new CronLogModel())->where('item_id', $id)->delete();
            (new AuditLogModel())->logAction('Mod ration : Suppression Carte', "Suppression d finitive de la carte ID {$id} ('{$item->titre}').");
            return redirect()->back()->with('message', "La carte '{$item->titre}' a  t  d finitivement supprim e.");
        }
        return redirect()->back()->with('error', 'Carte introuvable.');
    }
    public function deadLinks()
    {
        $cronLogModel = new CronLogModel();
        $itemModel = new ItemModel();
        $items = $itemModel->asArray()->select('lien')->findAll();
        $domains = [];
        foreach ($items as $item) {
            if (!empty($item['lien'])) {
                $parsedUrl = parse_url($item['lien']);
                if (isset($parsedUrl['host'])) {
                    $domain = $parsedUrl['host'];
                    if (!in_array($domain, $domains)) $domains[] = $domain;
                }
            }
        }
        sort($domains);
        return view('admin/items/dead_links', ['deadItems' => $cronLogModel->where('item_id IS NOT NULL')->findAll(), 'domains' => $domains]);
    }
    public function bulkUpdateDomain()
    {
        if ($this->request->is('post')) {
            $oldDomain = rtrim(str_replace(['https://', 'http://'], '', $this->request->getPost('old_domain')), '/');
            $newDomain = rtrim(str_replace(['https://', 'http://'], '', $this->request->getPost('new_domain')), '/');
            if (empty($oldDomain) || empty($newDomain)) return redirect()->back()->with('error', 'Les champs sont requis.');
            $itemModel = new ItemModel();
            $itemsToUpdate = $itemModel->like('lien', $oldDomain)->findAll();
            $count = 0;
            foreach ($itemsToUpdate as $item) {
                $newLien = str_replace($oldDomain, $newDomain, $item->lien);
                $itemModel->update($item->id, ['lien' => $newLien, 'link_status' => 'ok']);
                (new CronLogModel())->where('item_id', $item->id)->delete();
                ++$count;
            }
            if ($count > 0) {
                (new AuditLogModel())->logAction('Maintenance', "Migration de '{$oldDomain}' vers '{$newDomain}' sur {$count} carte(s).");
                return redirect()->back()->with('message', "Le domaine a  t  remplac  sur {$count} carte(s).");
            }
            return redirect()->back()->with('error', "Aucune carte avec le domaine '{$oldDomain}'.");
        }
        return redirect()->back();
    }
}