<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;
use App\Models\WhitelistModel;

class ItemController extends BaseController
{
    /**
     * Affiche le tableau de bord de modération (Nouvelles cartes & propositions de modif).
     */
    public function pending()
    {
        helper('text');
        
        $itemModel = new ItemModel();
        $revisionModel = new ItemRevisionModel();
        
        $revisions = $revisionModel->getPendingRevisions();
        
        foreach ($revisions as &$revision) {
            $changes = [];
            $fieldsToCompare = [
                'titre' => 'Titre',
                'sous_categorie' => 'Sous-catégorie',
                'status' => 'Statut',
                'image' => 'Image / Couverture',
                'lien' => 'Lien de visionnage/lecture',
                'description' => 'Description',
                'saison' => 'Saison',
                'total_saisons' => 'Total saisons',
                'episode' => 'Épisode',
                'total_episodes' => 'Total épisodes',
                'date_sortie' => 'Date de sortie',
            ];
            
            foreach ($fieldsToCompare as $field => $label) {
                $oldValue = $revision['original_'.$field] ?? '';
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
            
            $revision['changes'] = $changes;
        }
        
        $data = ['pendingItems' => $itemModel->where('is_public', 2)->findAll(), 'pendingRevisions' => $revisions];
        
        return view('admin/items/pending', $data);
    }

    /**
     * Valide et passe en 'public' une carte proposée
     *
     * @param mixed $id
     */
    public function approve($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        
        if ($item) {
            $itemModel->update($id, [
                'is_public' => 1,
                'status'    => 'Public'
            ]);
            (new AuditLogModel())->logAction('Modération : Approbation Carte', "Le SuperAdmin a validé la nouvelle publication de la carte ID {$id} ('{$item->titre}').");
        }
        
        return redirect()->back()->with('message', 'Nouvelle carte validée ! Elle est désormais visible de tous avec le statut Public.');
    }

    /**
     * Refuse la publication d'une carte (elle redevient privée)
     *
     * @param mixed $id
     */
    public function reject($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        
        if ($item) {
            $itemModel->update($id, [
                'is_public' => 0,
                'status'    => 'Privé'
            ]);
            (new AuditLogModel())->logAction('Modération : Refus Carte', "Le SuperAdmin a refusé la publication de la carte ID {$id} ('{$item->titre}'). Rétrogradation en privée.");
        }
        
        return redirect()->back()->with('error', "Nouvelle carte refusée. Elle est repassée en privée pour l'utilisateur avec le statut Privé.");
    }

    /**
     * Approuve un brouillon et écrase les données de la carte publique.
     *
     * @param mixed $revisionId
     */
    public function approveRevision($revisionId)
    {
        $revisionModel = new ItemRevisionModel();
        $itemModel = new ItemModel();
        
        $revision = $revisionModel->find($revisionId);
        
        if ($revision && 'pending' === $revision['revision_status']) {
            $updateData = [
                'titre' => $revision['titre'], 'sous_categorie' => $revision['sous_categorie'],
                'status' => 'Public', 
                'image' => $revision['image'],
                'lien' => $revision['lien'], 'description' => $revision['description'],
                'saison' => $revision['saison'], 'total_saisons' => $revision['total_saisons'],
                'episode' => $revision['episode'], 'total_episodes' => $revision['total_episodes'],
                'date_sortie' => $revision['date_sortie'],
            ];
            
            $itemModel->update($revision['original_item_id'], $updateData);
            $revisionModel->update($revisionId, ['revision_status' => 'approved']);
            
            (new AuditLogModel())->logAction('Modération : Approbation Draft', "Validation du Draft ID {$revisionId}. Les données de la carte publique ID {$revision['original_item_id']} ('{$revision['titre']}') ont été écrasées avec succès.");
            
            return redirect()->back()->with('message', 'La modification a été fusionnée et est maintenant en ligne.');
        }
        
        return redirect()->back()->with('error', 'Révision introuvable ou déjà traitée.');
    }

    /**
     * Rejette la demande de modification (Brouillon supprimé, original conservé)
     *
     * @param mixed $revisionId
     */
    public function rejectRevision($revisionId)
    {
        $revisionModel = new ItemRevisionModel();
        $revision = $revisionModel->find($revisionId);
        
        if ($revision) {
            $revisionModel->update($revisionId, ['revision_status' => 'rejected']);
            (new AuditLogModel())->logAction('Modération : Refus Draft', "Rejet du Draft ID {$revisionId} pour la carte ID {$revision['original_item_id']}. La version publique n'a pas été affectée.");
        }
        
        return redirect()->back()->with('error', 'La modification a été refusée.');
    }

    /**
     * Suppression autoritaire d'une carte par un modérateur.
     *
     * @param mixed $id
     */
    public function delete($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);
        
        if ($item) {
            $cronLogModel = new CronLogModel();
            $associatedReports = $cronLogModel->whereIn('task_name', [CronLogModel::TASK_PENDING_USER_REPORT, CronLogModel::TASK_CONFIRMED_DEAD])
                ->where('item_id', $id)->findAll();
            foreach ($associatedReports as $report) {
                $host = $this->normalizeReportHost((string) ($report['url_testee'] ?? ''));
                $remainingCards = $host === null ? [] : array_values(array_filter(
                    $this->activeCardsForReportHost($host),
                    static fn ($card) => (int) $card->id !== (int) $id
                ));
                if ($remainingCards !== []) {
                    $cronLogModel->update($report['id'], [
                        'item_id' => $remainingCards[0]->id,
                        'titre' => $remainingCards[0]->titre,
                    ]);
                } else {
                    $cronLogModel->delete($report['id']);
                }
            }

            $itemModel->delete($id);
            $cronLogModel->where('item_id', $id)->delete();
            (new AuditLogModel())->logAction('Modération : Suppression Carte', "Suppression définitive de la carte ID {$id} ('{$item->titre}').");
            
            return redirect()->back()->with('message', "La carte '{$item->titre}' a été définitivement supprimée.");
        }
        
        return redirect()->back()->with('error', 'Carte introuvable.');
    }

    /**
     * Affiche la liste complète des liens identifiés comme 'morts'.
     */
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
                    if (!in_array($domain, $domains)) {
                        $domains[] = $domain;
                    }
                }
            }
        }
        
        sort($domains);
        return view('admin/items/dead_links', [
            'deadItems' => $cronLogModel->where('task_name', CronLogModel::TASK_CONFIRMED_DEAD)->where('item_id IS NOT NULL')->findAll(),
            'pendingReports' => (new CronLogModel())->where('task_name', CronLogModel::TASK_PENDING_USER_REPORT)->orderBy('last_run', 'ASC')->findAll(),
            'domains' => $domains,
        ]);
    }

    private function normalizeReportHost(string $url): ?string
    {
        if (! preg_match('~^https?://~i', $url) && ! str_starts_with($url, '//')) $url = 'https://' . $url;
        elseif (str_starts_with($url, '//')) $url = 'https:' . $url;
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') return null;
        return (string) preg_replace('/^www\./i', '', strtolower(rtrim($host, '.')));
    }

    private function activeCardsForReportHost(string $host): array
    {
        $candidates = db_connect()->table('item')
            ->select('id, titre, lien, link_status')
            ->where('deleted_at', null)
            ->where('lien IS NOT NULL', null, false)
            ->like('lien', $host)
            ->get()->getResult();

        return array_values(array_filter($candidates, fn ($item) => $this->normalizeReportHost((string) $item->lien) === $host));
    }

    /** Admin confirmation promotes every card on the host to confirmed dead. */
    public function approveUserLinkReport(string $id)
    {
        if (! ctype_digit($id) || (int) $id < 1) {
            return redirect()->back()->with('error', 'Identifiant de signalement invalide.');
        }
        $id = (int) $id;
        $cronLogs = new CronLogModel();
        $report = $cronLogs->find($id);
        if (! $report || $report['task_name'] !== CronLogModel::TASK_PENDING_USER_REPORT) {
            return redirect()->back()->with('error', 'Ce signalement est introuvable ou déjà traité.');
        }

        $host = $this->normalizeReportHost((string) $report['url_testee']);
        if ($host === null) return redirect()->back()->with('error', 'Le domaine du signalement est invalide.');
        $db = db_connect();
        $db->transBegin();
        try {
            $cards = $this->activeCardsForReportHost($host);
            if ($cards === []) throw new \RuntimeException('Aucune carte active ne correspond à ce domaine.');
            $itemModel = new ItemModel();
            foreach ($cards as $card) {
                if (! $itemModel->update($card->id, ['link_status' => 'dead'])) {
                    throw new \RuntimeException('Impossible de confirmer toutes les cartes du domaine.');
                }
            }
            $cronLogs->update($id, [
                'task_name' => CronLogModel::TASK_CONFIRMED_DEAD,
                'url_testee' => 'https://' . $host . '/',
                'code_erreur' => 0,
            ]);
            (new AuditLogModel())->logAction('Signalement confirmé', "Domaine {$host} confirmé comme mort par l’administration (" . count($cards) . ' carte(s)).');
            if (! $db->transStatus()) throw new \RuntimeException('La transaction de modération a échoué.');
            $db->transCommit();
            return redirect()->back()->with('message', "Le domaine {$host} est confirmé comme mort.");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Échec de confirmation du signalement {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Le signalement n’a pas pu être confirmé.');
        }
    }

    /** Reject a report, restore its cards, and place the host in the 7-day whitelist. */
    public function rejectUserLinkReport(string $id)
    {
        if (! ctype_digit($id) || (int) $id < 1) {
            return redirect()->back()->with('error', 'Identifiant de signalement invalide.');
        }
        $id = (int) $id;
        $cronLogs = new CronLogModel();
        $report = $cronLogs->find($id);
        if (! $report || $report['task_name'] !== CronLogModel::TASK_PENDING_USER_REPORT) {
            return redirect()->back()->with('error', 'Ce signalement est introuvable ou déjà traité.');
        }

        $host = $this->normalizeReportHost((string) $report['url_testee']);
        if ($host === null) return redirect()->back()->with('error', 'Le domaine du signalement est invalide.');
        $db = db_connect();
        $db->transBegin();
        try {
            $itemModel = new ItemModel();
            foreach ($this->activeCardsForReportHost($host) as $card) {
                if ($card->link_status === 'reported' && ! $itemModel->update($card->id, ['link_status' => 'ok'])) {
                    throw new \RuntimeException('Impossible de restaurer les cartes du domaine.');
                }
            }
            $cronLogs->update($id, ['task_name' => CronLogModel::TASK_REJECTED_USER_REPORT]);

            $whitelist = new WhitelistModel();
            $existing = $whitelist->where('domain', $host)->first();
            if ($existing) $whitelist->update($existing['id'], ['created_at' => date('Y-m-d H:i:s')]);
            else $whitelist->insert(['domain' => $host, 'created_at' => date('Y-m-d H:i:s')]);

            (new AuditLogModel())->logAction('Signalement rejeté', "Signalement du domaine {$host} rejeté par l’administration; délai de protection de 7 jours appliqué.");
            if (! $db->transStatus()) throw new \RuntimeException('La transaction de modération a échoué.');
            $db->transCommit();
            return redirect()->back()->with('message', "Signalement rejeté. {$host} est protégé contre les nouveaux signalements pendant 7 jours.");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Échec du rejet du signalement {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Le signalement n’a pas pu être rejeté.');
        }
    }

    /**
     * Migration en masse : Remplace un ancien domaine par un nouveau dans tous les liens de la base.
     */
    public function bulkUpdateDomain()
    {
        if ($this->request->is('post')) {
            $oldDomain = rtrim(str_replace(['https://', 'http://'], '', $this->request->getPost('old_domain')), '/');
            $newDomain = rtrim(str_replace(['https://', 'http://'], '', $this->request->getPost('new_domain')), '/');
            
            if (empty($oldDomain) || empty($newDomain)) {
                return redirect()->back()->with('error', 'Les champs sont requis.');
            }
            
            $itemModel = new ItemModel();
            $itemsToUpdate = $itemModel->like('lien', $oldDomain)->findAll();
            $count = 0;
            
            foreach ($itemsToUpdate as $item) {
                $newLien = str_replace($oldDomain, $newDomain, $item->lien);
                $itemModel->update($item->id, ['lien' => $newLien, 'link_status' => 'ok']);
                (new CronLogModel())->where('item_id', $item->id)->delete();
                $count++;
            }
            
            if ($count > 0) {
                (new AuditLogModel())->logAction('Maintenance', "Migration de '{$oldDomain}' vers '{$newDomain}' sur {$count} carte(s).");
                return redirect()->back()->with('message', "Le domaine a été remplacé sur {$count} carte(s).");
            }
            
            return redirect()->back()->with('error', "Aucune carte avec le domaine '{$oldDomain}'.");
        }
        return redirect()->back();
    }

    /**
     * Marque un domaine comme faux positif, le met temporairement en whitelist et retire ses alertes.
     */
    public function falseNegativeDomain()
    {
        $url = $this->request->getGet('url');
        
        if (empty($url)) {
            return redirect()->back();
        }
        
        $cronLogModel = new CronLogModel();
        $itemModel = new ItemModel();
        $whitelistModel = new WhitelistModel();

        // Le domaine est protégé temporairement contre un nouveau faux signalement.
        $host = $this->normalizeReportHost((string) $url) ?? '';
        $count = 0;
        if ($host !== '') {
            foreach ($this->activeCardsForReportHost($host) as $card) {
                if (in_array($card->link_status, ['dead', 'reported'], true)) {
                    $itemModel->update($card->id, ['link_status' => 'ok']);
                    ++$count;
                }
            }
        }
        
        if (!empty($host)) {
            // Nettoyage de sécurité basique
            $existing = $whitelistModel->where('domain', $host)->first();
            if (!$existing) {
                $whitelistModel->insert([
                    'domain' => $host,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // A new false-negative review starts a fresh temporary cooldown.
                $whitelistModel->update($existing['id'], ['created_at' => date('Y-m-d H:i:s')]);
            }
        }
        
        // Supprime l'alerte du tableau de bord
        $logs = $cronLogModel->where('task_name', CronLogModel::TASK_CONFIRMED_DEAD)->findAll();
        foreach ($logs as $log) {
            if ($this->normalizeReportHost((string) ($log['url_testee'] ?? '')) === $host) {
                $cronLogModel->delete($log['id']);
            }
        }
        
        (new AuditLogModel())->logAction('Faux Négatif', "L'alerte pour le domaine {$url} a été ignorée ({$count} cartes restaurées) et mise en whitelist temporaire pour 7 jours.");
        
        return redirect()->back()->with('message', "Alerte retirée. Le domaine est considéré comme fonctionnel et ne pourra pas être signalé pendant 7 jours ({$count} carte(s) restaurée(s)).");
    }
}
