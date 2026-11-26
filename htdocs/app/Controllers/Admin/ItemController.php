<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ExternalUrlGuard;
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
                $oldValue = $revision['original_' . $field] ?? '';
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
     * Valide et passe en 'public' une carte proposée.
     *
     * @param mixed $id
     */
    public function approve($id)
    {
        $itemModel = new ItemModel();
        $item = $itemModel->find($id);

        if ($item) {
            if ((int) $item->id_division === ItemModel::PERSONAL_DIVISION_ID) {
                $itemModel->update($id, [
                    'is_public' => 0,
                    'status' => $item->status === 'Public' ? 'Aucun' : $item->status,
                ]);
                (new AuditLogModel())->logAction('Modération : Carte personnelle gardée privée', "La carte personnelle ID {$id} ('{$item->titre}') ne peut pas être publiée.");
                return redirect()->back()->with('error', 'Cette carte appartient à la division Personnelles et ne peut pas être publiée.');
            }

            $itemModel->update($id, [
                'is_public' => 1,
                'status'    => 'Public',
            ]);
            (new AuditLogModel())->logAction('Modération : Approbation Carte', "Le SuperAdmin a validé la nouvelle publication de la carte ID {$id} ('{$item->titre}').");
        }

        return redirect()->back()->with('message', 'Nouvelle carte validée ! Elle est désormais visible de tous avec le statut Public.');
    }

    /**
     * Refuse la publication d'une carte (elle redevient privée).
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
                'status'    => 'Aucun',
            ]);
            (new AuditLogModel())->logAction('Modération : Refus Carte', "Le SuperAdmin a refusé la publication de la carte ID {$id} ('{$item->titre}'). Rétrogradation en privée.");
        }

        return redirect()->back()->with('error', "Nouvelle carte refusée. Elle est repassée en privée pour l'utilisateur.");
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
     * Rejette la demande de modification (Brouillon supprimé, original conservé).
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
     * Affiche la liste complète des liens morts, des signalements en attente
     * et des domaines déclarés comme valides (whitelist).
     */
    public function deadLinks()
    {
        $deadItems = db_connect()->table('item')
            ->select('id AS item_id, titre, lien AS url_testee')
            ->where('link_status', 'dead')
            ->where('deleted_at', null)
            ->get()->getResultArray();

        $domains = [];
        $normalizedDeadItems = [];
        foreach ($deadItems as $deadItem) {
            $domain = $this->normalizeReportHost((string) ($deadItem['url_testee'] ?? ''));
            if ($domain === null) {
                continue;
            }
            $domains[$domain] = $domain;
            $deadItem['url_testee'] = 'https://' . $domain . '/';
            $normalizedDeadItems[] = $deadItem;
        }
        $domains = array_values($domains);
        sort($domains);

        $whitelistModel = new WhitelistModel();
        $validDomains = [];
        foreach ($whitelistModel->getAllOrdered() as $entry) {
            $host = $this->normalizeReportHost((string) ($entry['domain'] ?? ''));
            if ($host === null) {
                continue;
            }

            $url = 'https://' . $host . '/';
            if (! ExternalUrlGuard::isPublicHttpUrl($url)) {
                continue;
            }

            $validDomains[] = [
                'id'           => (int) $entry['id'],
                'host'         => $host,
                'url'          => $url,
                'created_at'   => $entry['created_at'] ?? null,
                'is_protected' => $whitelistModel->isProtectionActive($entry['created_at'] ?? null),
                'expires_at'   => $whitelistModel->getProtectionExpiresAt($entry['created_at'] ?? null),
                'items'        => $this->activeCardsForReportHost($host),
            ];
        }

        return view('admin/items/dead_links', [
            'deadItems'      => $normalizedDeadItems,
            'pendingReports' => (new CronLogModel())->where('task_name', CronLogModel::TASK_PENDING_USER_REPORT)->orderBy('last_run', 'ASC')->findAll(),
            'domains'        => $domains,
            'validDomains'   => $validDomains,
        ]);
    }

    private function normalizeReportHost(string $url): ?string
    {
        if (! preg_match('~^https?://~i', $url) && ! str_starts_with($url, '//')) {
            $url = 'https://' . $url;
        } elseif (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }
        return (string) preg_replace('/^www\./i', '', strtolower(rtrim($host, '.')));
    }

    private function activeCardsForReportHost(string $host): array
    {
        $candidates = (new ItemModel())->getActiveNonPersonalCandidatesByHost($host);

        return array_values(array_filter(
            $candidates,
            fn ($item) => $this->normalizeReportHost((string) $item->lien) === $host
        ));
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
        if ($host === null) {
            return redirect()->back()->with('error', 'Le domaine du signalement est invalide.');
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $cards = $this->activeCardsForReportHost($host);
            if ($cards === []) {
                throw new \RuntimeException('Aucune carte active ne correspond à ce domaine.');
            }
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
            if (! $db->transStatus()) {
                throw new \RuntimeException('La transaction de modération a échoué.');
            }
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
        if ($host === null) {
            return redirect()->back()->with('error', 'Le domaine du signalement est invalide.');
        }
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

            (new WhitelistModel())->upsertDomain($host);

            (new AuditLogModel())->logAction('Signalement rejeté', "Signalement du domaine {$host} rejeté par l’administration; délai de protection de 7 jours appliqué.");
            if (! $db->transStatus()) {
                throw new \RuntimeException('La transaction de modération a échoué.');
            }
            $db->transCommit();
            return redirect()->back()->with('message', "Signalement rejeté. {$host} est protégé contre les nouveaux signalements pendant 7 jours.");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Échec du rejet du signalement {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Le signalement n’a pas pu être rejeté.');
        }
    }

    /**
     * Retire un domaine déclaré comme valide (whitelist) et déclare toutes ses cartes actives comme mortes.
     */
    public function declareValidDomainDead(string $id)
    {
        if (! ctype_digit($id) || (int) $id < 1) {
            return redirect()->back()->with('error', 'Identifiant de domaine invalide.');
        }

        $id = (int) $id;
        $whitelistModel = new WhitelistModel();
        $entry = $whitelistModel->find($id);

        if (! $entry) {
            return redirect()->back()->with('error', 'Ce domaine valide est introuvable ou a déjà été retiré.');
        }

        $host = $this->normalizeReportHost((string) ($entry['domain'] ?? ''));
        if ($host === null) {
            return redirect()->back()->with('error', 'Le domaine enregistré est invalide.');
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $cards = $this->activeCardsForReportHost($host);
            $itemModel = new ItemModel();
            $cronLogs = new CronLogModel();

            foreach ($cards as $card) {
                if (! $itemModel->update($card->id, ['link_status' => 'dead'])) {
                    throw new \RuntimeException('Impossible de passer toutes les cartes du domaine en lien mort.');
                }
            }

            $whitelistModel->delete($id);

            $existingLogs = $cronLogs->whereIn('task_name', [
                CronLogModel::TASK_PENDING_USER_REPORT,
                CronLogModel::TASK_CONFIRMED_DEAD,
            ])->findAll();

            foreach ($existingLogs as $log) {
                if ($this->normalizeReportHost((string) ($log['url_testee'] ?? '')) === $host) {
                    $cronLogs->delete($log['id']);
                }
            }

            if ($cards !== []) {
                $cronLogs->insert([
                    'item_id'     => $cards[0]->id,
                    'titre'       => $cards[0]->titre,
                    'url_testee'  => 'https://' . $host . '/',
                    'code_erreur' => 0,
                    'task_name'   => CronLogModel::TASK_CONFIRMED_DEAD,
                    'last_run'    => date('Y-m-d H:i:s'),
                ]);
            }

            $count = count($cards);
            (new AuditLogModel())->logAction(
                'Domaine déclaré mort',
                "Le domaine {$host} a été retiré des domaines valides et déclaré comme mort par l’administration ({$count} carte(s) impactée(s))."
            );

            if (! $db->transStatus()) {
                throw new \RuntimeException('La transaction de passage en lien mort a échoué.');
            }

            $db->transCommit();

            if ($count === 0) {
                return redirect()->back()->with('message', "Le domaine {$host} a été retiré des domaines valides (aucune carte active associée).");
            }

            return redirect()->back()->with('message', "Le domaine {$host} a été déclaré comme mort ({$count} carte(s) impactée(s)).");
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Échec du passage en lien mort pour le domaine valide {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Impossible de déclarer ce domaine comme mort.');
        }
    }

    /**
     * Migration en masse : Remplace un ancien domaine par un nouveau dans tous les liens de la base.
     */
    public function bulkUpdateDomain()
    {
        if ($this->request->is('post')) {
            $oldDomain = rtrim(str_replace(['https://', 'http://'], '', (string) $this->request->getPost('old_domain')), '/');
            $newDomain = rtrim(str_replace(['https://', 'http://'], '', (string) $this->request->getPost('new_domain')), '/');

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
            $whitelistModel->upsertDomain($host);
        }

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
