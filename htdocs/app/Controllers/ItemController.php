<?php declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Item;
use App\Libraries\ExternalUrlGuard;
use App\Models\AuditLogModel;
use App\Models\CronLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;
use App\Models\SiteConfigModel;
use App\Models\StatutModel;
use App\Models\WhitelistModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;

class ItemController extends BaseController
{
    private const USER_REPORT_LIMIT_PER_HOUR = 3;
    private const USER_REPORT_LIMIT_PER_DAY = 21;
    private const IP_REPORT_LIMIT_PER_HOUR = 7;
    private const IP_REPORT_LIMIT_PER_DAY = 49;

    private $model;
    private $statutModel;

    public function __construct()
    {
        $this->model = new ItemModel();
        $this->statutModel = new StatutModel();
    }

    /**
     * Affiche le formulaire d'ajout ou de modification d'une carte.
     *
     * @param null|mixed $id
     *
     * @var null|Item
     */
    public function form($id = null)
    {
        $item = null;
        if (null !== $id) {
            $item = $this->model->find($id);
            if (!$this->canManageItem($item)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $userId = auth()->loggedIn() ? auth()->id() : null;
        $subCategories = [];

        if ($userId) {
            $subCategories = $this
                ->model
                ->where('id_user', $userId)
                ->where('sous_categorie IS NOT NULL')
                ->where('sous_categorie !=', '')
                ->distinct()
                ->findColumn('sous_categorie') ?? [];
        }

        $data = [
            'headers' => [],
            'divisions' => $this->model->getDivisions(),
            'subCategories' => $subCategories,
            'statuts' => $this->statutModel->where('nom !=', 'Public')->orderBy('ordre', 'ASC')->findAll(),
            'item' => $item,
            'view' => 'items/item_form',
            'redirect_url' => old('redirect_url') ?: ($this->request->getUserAgent()->getReferrer() ?? site_url('/')),
        ];

        return view('items/item_form', $data);
    }

    /**
     * Gère la sauvegarde (création ou mise à jour) d'une carte.
     */
    public function save()
    {
        if ($this->request->is('post')) {
            if (!auth()->loggedIn()) {
                return redirect()->to('z');
            }

            $rules = [
                'titre' => 'required|max_length[100]',
                'id' => 'permit_empty|is_natural_no_zero|is_not_unique[item.id]',
                'id_division' => 'required|is_natural_no_zero|is_not_unique[division.id]',
                'status' => 'required|is_not_unique[statuts.nom]',
                'saison' => 'permit_empty|is_natural',
                'total_saisons' => 'permit_empty|is_natural',
                'episode' => 'permit_empty|is_natural',
                'total_episodes' => 'permit_empty|is_natural',
                'sous_categorie_select' => 'permit_empty|max_length[255]',
                'sous_categorie_new' => 'permit_empty|max_length[255]',
                'image' => 'permit_empty|max_length[255]',
                'lien' => 'permit_empty|max_length[255]',
                'date_sortie' => 'permit_empty|valid_date[Y-m-d\TH:i]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Erreur dans le formulaire.');
            }

            $data = $this->request->getPost();
            $id = $this->request->getPost('id');
            $isAdmin = auth()->user()->inGroup('admin', 'superadmin');
            $isSuperAdmin = auth()->user()->inGroup('superadmin');
            $audit = new AuditLogModel();

            $wantsPublic = $this->request->getPost('is_public');
            $data['is_public'] = $wantsPublic ? ($isSuperAdmin ? 1 : 2) : 0;
            if ((int) ($data['id_division'] ?? 0) === ItemModel::PERSONAL_DIVISION_ID) {
                $data['is_public'] = 0;
            }

            // Formatage des données optionnelles
            $releaseDate = $this->request->getPost('date_sortie');
            $data['date_sortie'] = empty($releaseDate) ? null : str_replace('T', ' ', (string) $releaseDate) . ':00';

            $data['saison'] = ('' === $this->request->getPost('saison')) ? null : $this->request->getPost('saison');
            $data['total_saisons'] = ('' === $this->request->getPost('total_saisons')) ? null : $this->request->getPost('total_saisons');
            $data['episode'] = ('' === $this->request->getPost('episode')) ? null : $this->request->getPost('episode');
            $data['total_episodes'] = ('' === $this->request->getPost('total_episodes')) ? null : $this->request->getPost('total_episodes');

            $sousCatSelect = $this->request->getPost('sous_categorie_select');
            $sousCatNew = $this->request->getPost('sous_categorie_new');

            if ('__NEW__' === $sousCatSelect) {
                $data['sous_categorie'] = empty($sousCatNew) ? null : trim((string) $sousCatNew);
            } else {
                $data['sous_categorie'] = empty($sousCatSelect) ? null : trim((string) $sousCatSelect);
            }

            // FORCER LE STATUT SELON LA VISIBILITÉ
            if ($data['is_public'] == 1) {
                $data['status'] = 'Public';
            } elseif ($data['is_public'] == 0) {
                if (empty($data['status']) || $data['status'] === 'Public') {
                    $data['status'] = 'Aucun';
                }
            }

            $existing = null;
            if ($id) {
                $existing = $this->model->find($id);
                if ($existing) {
                    $data['id_user'] = $existing->id_user;
                }
            } else {
                $data['id_user'] = auth()->id();
            }

            $backUrl = $this->request->getPost('redirect_url') ?: site_url('/');
            $separator = (str_contains($backUrl, '?')) ? '&' : '?';

            // MISE À JOUR D'UNE CARTE EXISTANTE
            if ($id) {
                $canEdit = $existing && ((int) $existing->id_user === (int) auth()->id() || $isAdmin);

                if (!$canEdit) {
                    $audit->logAction('Violation Accès', "Tentative non autorisée de modification sur la carte ID {$id}.");
                    return redirect()->back()->with('error', "Vous n'avez pas les droits pour modifier cette carte.");
                }

                // Gère la proposition de brouillon si un utilisateur standard modifie une carte déjà publique
                if (1 == $existing->is_public && 0 != $data['is_public'] && !$isSuperAdmin) {
                    $revisionModel = new ItemRevisionModel();
                    $existingRevision = $revisionModel->where('original_item_id', $id)->where('revision_status', 'pending')->first();

                    $revisionData = [
                        'original_item_id' => $id,
                        'id_user' => auth()->id(),
                        'titre' => $data['titre'],
                        'sous_categorie' => $data['sous_categorie'] ?? $existing->sous_categorie,
                        'status' => $data['status'],
                        'image' => $data['image'] ?? $existing->image,
                        'lien' => $data['lien'] ?? null,
                        'description' => $data['description'] ?? null,
                        'episode' => $data['episode'] ?? null,
                        'total_episodes' => $data['total_episodes'] ?? null,
                        'saison' => $data['saison'] ?? null,
                        'total_saisons' => $data['total_saisons'] ?? null,
                        'position' => $existing->position,
                        'date_sortie' => $data['date_sortie'],
                        'revision_status' => 'pending',
                    ];

                    if ($existingRevision) {
                        $revisionData['id'] = $existingRevision['id'];
                    }

                    $revisionModel->save($revisionData);
                    $actionLog = $existingRevision ? 'Mise à jour Draft' : 'Soumission Draft';
                    $audit->logAction($actionLog, "L'utilisateur a proposé une modification pour la carte publique ID {$id} ('{$existing->titre}').");

                    return redirect()->to($backUrl . $separator . 'open=' . $existing->id_division . '#card-' . $id)->with('message', 'Votre modification a été soumise au SuperAdmin pour validation.');
                }

                $item = new Item($data);
                $progressChanged = !$existing;

                if ($existing) {
                    foreach (['titre', 'saison', 'episode', 'total_episodes', 'total_saisons'] as $field) {
                        if ((string) ($data[$field] ?? '') !== (string) ($existing->{$field} ?? '')) {
                            $progressChanged = true;
                            break;
                        }
                    }
                }

                if ($progressChanged && !empty($data['saison']) && !empty($data['episode'])) {
                    $globalData = $this->syncGlobalEpisodesWithTMDB($item);
                    $item->episode_global = $globalData['episode_global'];
                    $item->total_episodes_global = $globalData['total_episodes_global'];
                }

                $this->model->save($item);

                $statutVisibility = 1 == $data['is_public'] ? 'Publique' : 'Privée';
                $audit->logAction('Mise à jour Carte', "Modification de la carte ID {$id} ('{$data['titre']}'). Visibilité : {$statutVisibility}.");

                // Si la carte repasse en privé, on supprime ses brouillons en attente
                if (1 == $existing->is_public && 0 == $data['is_public']) {
                    (new ItemRevisionModel())->where('original_item_id', $id)->where('revision_status', 'pending')->delete();
                    $audit->logAction('Nettoyage Draft', "Passage en privée de la carte ID {$id} : Suppression automatique des drafts en attente.");
                }
            } else {
                // CRÉATION D'UNE NOUVELLE CARTE
                $maxPosition = $this->model->where('id_division', $data['id_division'])->where('id_user', $data['id_user'])->selectMax('position')->get()->getRow()->position;
                $data['position'] = (null !== $maxPosition) ? ((int) $maxPosition + 1) : 0;

                $item = new Item($data);
                $this->model->save($item);
                $newId = $this->model->getInsertID();

                $statutVisibility = 2 == $data['is_public'] ? 'En attente' : (1 == $data['is_public'] ? 'Publique' : 'Privée');
                $audit->logAction('Création Carte', "Création de la carte ID {$newId} ('{$data['titre']}'). Visibilité initiale: {$statutVisibility}.");
            }

            $subParam = !empty($data['sous_categorie']) ? '&subopen=' . urlencode($data['sous_categorie']) : '';

            if ($this->request->getPost('save_add_another')) {
                return redirect()->to('m')->with('success', 'Carte enregistrée. Vous pouvez en ajouter une autre.');
            }

            $targetId = $id ?: ($newId ?? '');

            return redirect()->to($backUrl . $separator . 'open=' . $data['id_division'] . $subParam . '#card-' . $targetId)->with('success', 'Carte enregistrée.');
        }
    }

    /**
     * Place une carte dans la corbeille.
     *
     * @param null|mixed $id
     */
    public function delete($id = null)
    {
        if (null !== $id) {
            $item = $this->model->find($id);
            $isAdmin = auth()->user()->inGroup('admin', 'superadmin');

            if ($item && ((int) $item->id_user === (int) auth()->id() || $isAdmin)) {
                $id_div = $item->id_division;
                $titre = $item->titre;

                $this->model->delete($id);
                (new AuditLogModel())->logAction('Suppression Carte', "Suppression de la carte ID {$id} ('{$titre}').");

                $backUrl = $this->request->getUserAgent()->getReferrer() ?: site_url('/');
                $separator = (str_contains($backUrl, '?')) ? '&' : '?';

                return redirect()->to($backUrl . $separator . 'open=' . $id_div . '#div-' . $id_div);
            }
        }
        return redirect()->back();
    }

    /**
     * Incrémente rapidement l'épisode depuis le dashboard et récupère le total d'épisodes
     * de la nouvelle saison via TMDB (avec fallback Jikan pour les animes).
     *
     * @param mixed $id
     */
    public function incrementEpisode($id)
    {
        $item = $this->model->find($id);

        if ($item) {
            if (!$this->canManageItem($item)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'success' => false,
                        'error' => 'Vous ne pouvez pas modifier cette carte.',
                        'csrf_token' => csrf_hash(),
                    ]);
                }
                return redirect()->back()->with('error', 'Vous ne pouvez pas modifier cette carte.');
            }

            $newEpisode = (int) $item->episode + 1;
            $newSaison = (int) $item->saison;
            $totalEpisodes = (int) $item->total_episodes;
            $totalSaisons = (int) $item->total_saisons;
            $newTotalEpisodes = $totalEpisodes > 0 ? $totalEpisodes : null;
            $updateData = [];

            // Détection automatique du passage à la saison suivante ou de la fin
            if ($totalEpisodes > 0 && $newEpisode > $totalEpisodes) {
                if ($totalSaisons > 0 && $newSaison >= $totalSaisons) {
                    $updateData['status'] = 'Terminé';
                    $newEpisode = $totalEpisodes;
                    $updateData['episode'] = $newEpisode;
                    $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Statut passé à Terminé.";
                } else {
                    $newEpisode = 1;
                    ++$newSaison;
                    $newTotalEpisodes = null;
                    $updateData['saison'] = $newSaison;
                    $updateData['episode'] = $newEpisode;
                    $updateData['total_episodes'] = null; // Vide l'ancien total par sécurité

                    $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode} (Saison {$newSaison})";
                }
            } else {
                $updateData['episode'] = $newEpisode;
                $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode}";
            }

            $this->model->update($id, $updateData);

            $wasComplete = 'Terminé' === $item->status;
            if (!empty($updateData['saison'])) {
                $updatedItem = $this->model->find($id);
                if ($updatedItem) {
                    // Récupération du nombre d'épisodes de la nouvelle saison + compteurs globaux via TMDB
                    $syncData = $this->syncGlobalEpisodesWithTMDB($updatedItem, true);

                    // Fallback sur Jikan (MyAnimeList) si TMDB n'a pas trouvé le total de la saison
                    if (empty($syncData['total_episodes'])) {
                        $jikanTotal = $this->fetchSeasonEpisodesFromJikan((string) $updatedItem->titre, $newSaison);
                        if (null !== $jikanTotal) {
                            $syncData['total_episodes'] = $jikanTotal;
                        }
                    }

                    if (!empty($syncData['total_episodes'])) {
                        $newTotalEpisodes = (int) $syncData['total_episodes'];
                    }

                    $this->model->update($id, $syncData);
                }
            } else {
                $this->model->update($id, [
                    'episode_global' => (int) ($item->episode_global ?? $item->episode ?? 0) + ($wasComplete ? 0 : 1),
                    'total_episodes_global' => $item->total_episodes_global ?? $item->total_episodes,
                ]);
            }

            (new AuditLogModel())->logAction('Incrémentation Rapide', $logMessage);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'new_episode' => $newEpisode,
                    'new_saison' => $newSaison,
                    'new_total_episodes' => $newTotalEpisodes,
                    'status' => $updateData['status'] ?? $item->status,
                    'csrf_token' => csrf_hash(),
                ]);
            }
        }
        return redirect()->back();
    }

    /**
     * Incrémente rapidement la saison.
     *
     * @param mixed $id
     */
    public function incrementSaison($id)
    {
        $item = $this->model->find($id);

        if ($item) {
            if (!$this->canManageItem($item)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'success' => false,
                        'error' => 'Vous ne pouvez pas modifier cette carte.',
                        'csrf_token' => csrf_hash(),
                    ]);
                }
                return redirect()->back()->with('error', 'Vous ne pouvez pas modifier cette carte.');
            }

            $newSaison = (int) $item->saison + 1;
            $this->model->update($id, ['saison' => $newSaison]);

            (new AuditLogModel())->logAction('Incrémentation Rapide', "Mise à jour de la carte ID {$id} ('{$item->titre}') : Saison passée à {$newSaison}.");

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'new_saison' => $newSaison, 'csrf_token' => csrf_hash()]);
            }
        }
        return redirect()->back();
    }

    /**
     * Moteur de recherche multi-sources (TMDB & Mangadex).
     */
    public function search()
    {
        $query = $this->request->getGet('q');
        $type = $this->request->getGet('type');

        if (empty($query)) {
            return $this->response->setJSON([]);
        }

        $client = Services::curlrequest([
            'timeout' => 8,
            'connect_timeout' => 5,
            'http_errors' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/site',
        ]);

        try {
            // Lecture des métadonnées directes si l'entrée est une URL
            if (filter_var($query, FILTER_VALIDATE_URL)) {
                $metaData = $this->scrapeOpenGraph($query);
                $body = $metaData ? [$metaData] : ['error' => 'Impossible de lire le lien.'];

                return $this->response->setJSON($body);
            }

            $unifiedResults = [];
            $apiKey = env('TMDB_API_KEY') ?? 'ba55da0439797150ed58c4e524584823';

            // Requête TMDB
            $url = 'https://api.themoviedb.org/3/search/multi?query=' . urlencode($query) . "&api_key={$apiKey}&language=fr-FR";
            $response = $client->get($url);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);

                if (isset($body['results']) && is_array($body['results'])) {
                    foreach ($body['results'] as $result) {
                        if (isset($result['media_type']) && 'person' === $result['media_type']) {
                            continue;
                        }

                        $isAnime = false;
                        if (isset($result['genre_ids']) && is_array($result['genre_ids']) && in_array(16, $result['genre_ids'])) {
                            if (isset($result['origin_country']) && is_array($result['origin_country']) && in_array('JP', $result['origin_country'])) {
                                $isAnime = true;
                            }
                        }

                        $mediaLabel = strtoupper($result['media_type'] ?? 'TMDB');
                        if ('TV' === $mediaLabel) {
                            $mediaLabel = $isAnime ? 'ANIME' : 'TV';
                        } elseif ('MOVIE' === $mediaLabel) {
                            $mediaLabel = $isAnime ? 'FILM ANIME' : 'MOVIE';
                        }

                        $item = [
                            'titre' => $result['title'] ?? $result['name'] ?? $result['original_name'] ?? 'Inconnu',
                            'imageThumb' => !empty($result['poster_path']) ? "https://image.tmdb.org/t/p/w200{$result['poster_path']}" : '',
                            'imageLarge' => !empty($result['poster_path']) ? "https://image.tmdb.org/t/p/w500{$result['poster_path']}" : '',
                            'description' => $result['overview'] ?? '',
                            'info' => substr($result['release_date'] ?? $result['first_air_date'] ?? '', 0, 4) . ' - ' . $mediaLabel,
                            'lien' => '',
                            'total_episodes' => '',
                            'total_saisons' => '',
                            'seasons_data' => null,
                        ];

                        if (isset($result['media_type']) && 'tv' === $result['media_type'] && isset($result['id'])) {
                            try {
                                $tvUrl = "https://api.themoviedb.org/3/tv/{$result['id']}?api_key={$apiKey}&language=fr-FR";
                                $tvResponse = $client->get($tvUrl);
                                if (200 === $tvResponse->getStatusCode()) {
                                    $tvBody = json_decode($tvResponse->getBody(), true);
                                    if (isset($tvBody['number_of_episodes'])) {
                                        $item['total_episodes'] = $tvBody['number_of_episodes'];
                                    }
                                    if (isset($tvBody['number_of_seasons'])) {
                                        $item['total_saisons'] = $tvBody['number_of_seasons'];
                                    }
                                    if (isset($tvBody['seasons'])) {
                                        $seasons = [];
                                        foreach ($tvBody['seasons'] as $season) {
                                            $seasons[$season['season_number']] = $season['episode_count'];
                                        }
                                        $item['seasons_data'] = $seasons;
                                    }
                                }
                            } catch (\Exception $e) {
                            }
                        }

                        $unifiedResults[] = $item;
                    }
                }
            }

            // Requête Mangadex
            try {
                $mdUrl = 'https://api.mangadex.org/manga?title=' . urlencode($query) . '&limit=5&includes[]=cover_art&order[relevance]=desc';
                $mdResponse = $client->get($mdUrl, [
                    'headers' => [
                        'User-Agent' => 'site-App/1.0',
                        'Accept' => 'application/json',
                    ],
                ]);

                if (200 === $mdResponse->getStatusCode()) {
                    $mdBody = json_decode($mdResponse->getBody(), true);

                    if (isset($mdBody['data']) && is_array($mdBody['data'])) {
                        foreach ($mdBody['data'] as $m) {
                            $attr = $m['attributes'] ?? [];
                            $titre = $attr['title']['en'] ?? $attr['title']['ja-ro'] ?? $attr['title']['fr'] ?? 'Inconnu';
                            if (is_array($titre)) {
                                $titre = 'Inconnu';
                            }
                            $description = $attr['description']['fr'] ?? $attr['description']['en'] ?? '';
                            $year = $attr['year'] ?? '';

                            $fileName = '';
                            if (isset($m['relationships'])) {
                                foreach ($m['relationships'] as $rel) {
                                    if ('cover_art' === $rel['type'] && isset($rel['attributes']['fileName'])) {
                                        $fileName = $rel['attributes']['fileName'];
                                        break;
                                    }
                                }
                            }

                            $imageThumb = $fileName ? "https://uploads.mangadex.org/covers/{$m['id']}/{$fileName}.256.jpg" : '';
                            $imageLarge = $fileName ? "https://uploads.mangadex.org/covers/{$m['id']}/{$fileName}" : '';

                            $unifiedResults[] = [
                                'titre' => $titre,
                                'imageThumb' => $imageThumb,
                                'imageLarge' => $imageLarge,
                                'description' => strip_tags((string) $description),
                                'info' => ($year ? $year . ' - ' : '') . 'MANGA',
                                'lien' => '',
                                'total_episodes' => $attr['lastChapter'] ?? '',
                                'total_saisons' => $attr['lastVolume'] ?? '',
                                'seasons_data' => null,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
            }

            $finalBody = ['unified' => $unifiedResults];

            return $this->response->setJSON($finalBody);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Erreur de recherche : ' . $e->getMessage()]);
        }
    }

    /**
     * Liste des cartes publiques pouvant être transférées.
     */
    public function checkToGlobal()
    {
        $revisionModel = new ItemRevisionModel();
        $siteConfigModel = new SiteConfigModel();
        $pendingRevisionIds = [];

        if (auth()->loggedIn()) {
            $pendingRevisionIds = array_fill_keys(
                array_map('intval', $revisionModel->where('revision_status', 'pending')->findColumn('original_item_id') ?? []),
                true,
            );
        }

        $supportedDomains = $siteConfigModel
            ->where('is_active', 1)
            ->findColumn('domain') ?? [];

        return view('items/global_items', [
            'items' => $this->model->checkToGlobal(),
            'pendingRevisionIds' => $pendingRevisionIds,
            'supportedDomains' => $supportedDomains,
        ]);
    }

    /**
     * Transfère la propriété d'une carte au profil d'administration.
     *
     * @param mixed $id
     */
    public function turnToAdmin($id)
    {
        $item = $this->model->find($id);
        $isAdmin = auth()->user()->inGroup('admin', 'superadmin');

        if ($item && ((int) $item->id_user === (int) auth()->id() || $isAdmin)) {
            $this->model->update($id, ['id_user' => 1]);
            (new AuditLogModel())->logAction('Transfert Carte', "La carte ID {$id} ('{$item->titre}') a été transférée à l'admin.");

            return redirect()->back()->with('message', "La carte a été transférée à l'admin avec succès.");
        }
        return redirect()->back()->with('error', "Vous n'avez pas les droits pour effectuer cette action.");
    }

    /**
     * Met à jour l'ordre d'affichage des cartes via un appel AJAX.
     */
    public function updateOrder()
    {
        if ($this->request->is('ajax')) {
            $json = $this->request->getJSON();
            if (isset($json->order) && is_array($json->order)) {
                if (!auth()->loggedIn()) {
                    return $this->response->setJSON(['success' => false, 'error' => 'Session expirée.']);
                }

                $userId = auth()->id();
                $isSuperAdmin = auth()->user()->inGroup('superadmin');

                $count = 0;
                foreach ($json->order as $index => $itemId) {
                    $item = $this->model->find($itemId);
                    if ($item && ((int) $item->id_user === (int) $userId || $isSuperAdmin)) {
                        $this->model->update($itemId, ['position' => $index]);
                        ++$count;
                    }
                }

                if ($count > 0) {
                    (new AuditLogModel())->logAction('Reorganisation', "Ordre d'affichage de {$count} carte(s) modifié.");
                }

                return $this->response->setJSON(['success' => true, 'message' => 'Ordre mis à jour.', 'csrf_token' => csrf_hash()]);
            }
        }
        return $this->response->setJSON(['success' => false, 'error' => 'Requête invalide.']);
    }

    /**
     * Vérifie en direct via AJAX la disponibilité d'une vidéo/lecteur sur une URL ciblée.
     */
    public function checkDispo()
    {
        $urlCible = $this->request->getGet('urlCible');
        if (!is_string($urlCible) || !ExternalUrlGuard::isPublicHttpUrl($urlCible)) {
            return $this->response->setJSON(['success' => false, 'error' => 'URL invalide.']);
        }

        $urlParts = parse_url((string) $urlCible);
        $host = strtolower(rtrim((string) ($urlParts['host'] ?? ''), '.'));

        // 1. Mise en cache de la table sites_config (Validité: 12 heures)
        $cacheKey = 'parsing_sites_config';
        if (!$sites = cache($cacheKey)) {
            $sites = (new SiteConfigModel())->where('is_active', 1)->findAll();
            foreach ($sites as &$site) {
                $site['ind_page'] = json_decode($site['indicateurs_page_invalide'], true) ?? [];
                $site['ind_lect'] = json_decode($site['indicateurs_lecteur'], true) ?? [];
            }
            cache()->save($cacheKey, $sites, 43200);
        }

        $currentConfig = null;
        foreach ($sites as $config) {
            $configuredHost = strtolower(parse_url(str_contains($config['domain'], '://') ? $config['domain'] : 'https://' . $config['domain'], PHP_URL_HOST));
            if ($host === $configuredHost || str_ends_with($host, '.' . $configuredHost)) {
                $currentConfig = $config;
                break;
            }
        }

        if (!$currentConfig)
            return $this->response->setJSON(['success' => false, 'error' => 'Domaine non supporté.']);

        $matches = [];
        @preg_match($currentConfig['regex_episode'], (string) $urlCible, $matches);
        $episodeExtrait = $matches[1] ?? null;

        try {
            $client = Services::curlrequest([
                'timeout' => 3,
                'connect_timeout' => 2,
                'http_errors' => false,
                'allow_redirects' => false,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/Checker'
            ]);

            $response = $client->get($urlCible);
            if ($response->getStatusCode() === 404) {
                return $this->response->setJSON(['success' => true, 'disponible' => false, 'details' => ['erreur' => 'Page 404']]);
            }

            $html = (string) $response->getBody();
            $estSurFicheAnime = false;
            $lecteurPresent = false;

            foreach ($currentConfig['ind_page'] as $indicator) {
                if (stripos($html, $indicator) !== false) {
                    $estSurFicheAnime = true;
                    break;
                }
            }

            if (!$estSurFicheAnime) {
                foreach ($currentConfig['ind_lect'] as $indicator) {
                    $indicatorFinal = $episodeExtrait ? str_replace('{ep}', (string) $episodeExtrait, $indicator) : $indicator;
                    if (stripos($html, $indicatorFinal) !== false) {
                        $lecteurPresent = true;
                        break;
                    }
                }
            }

            unset($html);

            return $this->response->setJSON([
                'success' => true,
                'disponible' => !$estSurFicheAnime && $lecteurPresent,
                'details' => ['estSurFicheAnime' => $estSurFicheAnime, 'lecteurPresent' => $lecteurPresent],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Timeout']);
        }
    }

    /**
     * Affiche la vue de la corbeille.
     */
    public function viewDeleted()
    {
        $userId = auth()->id();
        $isSuperAdmin = auth()->user()->inGroup('superadmin');
        $deletedItems = $this->model->getDeletedItems($isSuperAdmin ? null : $userId);

        return view('items/deleted_items', ['deletedItems' => $deletedItems]);
    }

    /**
     * Restaure une carte spécifique.
     *
     * @param mixed $id
     */
    public function restore($id)
    {
        $isSuperAdmin = auth()->user()->inGroup('superadmin');
        $item = $this->model->withDeleted()->find($id);

        if ($item && ((int) $item->id_user === (int) auth()->id() || $isSuperAdmin)) {
            $this->model->builder()->where('id', $id)->update(['deleted_at' => null]);
            (new AuditLogModel())->logAction('Restauration', "La carte ID {$id} ('{$item->titre}') a été restaurée de la corbeille.");

            return redirect()->back()->with('message', "La carte '{$item->titre}' a été restaurée avec succès.");
        }
        return redirect()->back()->with('error', "Vous n'avez pas l'autorisation de restaurer cette carte.");
    }

    /**
     * Supprime définitivement une carte.
     *
     * @param mixed $id
     */
    public function permanentDelete($id)
    {
        $isSuperAdmin = auth()->user()->inGroup('superadmin');
        $item = $this->model->withDeleted()->find($id);

        if ($item && ((int) $item->id_user === (int) auth()->id() || $isSuperAdmin)) {
            $titre = $item->titre;
            $this->model->delete($id, true);
            (new CronLogModel())->where('item_id', $id)->delete();
            (new AuditLogModel())->logAction('Suppression Définitive', "La carte ID {$id} ('{$titre}') a été détruite définitivement.");

            return redirect()->back()->with('message', "La carte '{$titre}' a été définitivement supprimée de la base de données.");
        }
        return redirect()->back()->with('error', "Vous n'avez pas l'autorisation de supprimer définitivement cette carte.");
    }

    /**
     * Restaure l'intégralité des cartes de la corbeille.
     */
    public function restoreAll()
    {
        $isSuperAdmin = auth()->user()->inGroup('superadmin');
        $userId = auth()->id();

        $builder = $this->model->builder()->where('deleted_at IS NOT NULL');
        if (!$isSuperAdmin) {
            $builder->where('id_user', $userId);
        }
        $builder->update(['deleted_at' => null]);

        (new AuditLogModel())->logAction('Restauration Globale', 'Toutes les cartes de la corbeille ont été restaurées.');

        return redirect()->back()->with('message', 'Vos cartes ont été restaurées avec succès.');
    }

    /**
     * Vide intégralement la corbeille.
     */
    public function emptyTrash()
    {
        $isSuperAdmin = auth()->user()->inGroup('superadmin');
        $userId = auth()->id();

        $query = $this->model->onlyDeleted();
        if (!$isSuperAdmin) {
            $query->where('id_user', $userId);
        }

        $itemsToDelete = $query->findAll();
        if (!empty($itemsToDelete)) {
            $itemIds = array_map(static fn(Item $item): int => (int) $item->id, $itemsToDelete);
            (new CronLogModel())->whereIn('item_id', $itemIds)->delete();
            $this->model->builder()->whereIn('id', $itemIds)->delete();

            (new AuditLogModel())->logAction('Vidage Corbeille', 'La corbeille a été vidée définitivement (' . count($itemIds) . ' cartes détruites).');
        }

        return redirect()->back()->with('message', 'Vos cartes supprimées ont été vidées définitivement.');
    }

    /**
     * Récupère les métadonnées OpenGraph (Titre, Image, Description) d'une URL fournie.
     */
    private function scrapeOpenGraph(string $url): ?array
    {
        $html = @file_get_contents($url);
        if (!$html) {
            return null;
        }

        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $tags = $doc->getElementsByTagName('meta');
        $data = ['titre' => '', 'description' => '', 'image' => '', 'lien' => $url, 'is_link' => true];

        foreach ($tags as $tag) {
            if ($tag->hasAttribute('property')) {
                $property = $tag->getAttribute('property');
                if ('og:title' === $property) {
                    $data['titre'] = $tag->getAttribute('content');
                }
                if ('og:description' === $property) {
                    $data['description'] = $tag->getAttribute('content');
                }
                if ('og:image' === $property) {
                    $data['image'] = $tag->getAttribute('content');
                }
            }
        }

        if (empty($data['titre'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $data['titre'] = $titles->item(0)->nodeValue;
            }
        }

        return $data;
    }

    /**
     * Interroge TMDB pour calculer les compteurs globaux (épisodes vus au total et total de la série)
     * et, si demandé, récupérer le nombre d'épisodes de la saison en cours.
     */
    private function syncGlobalEpisodesWithTMDB(Item $item, bool $updateSeasonTotal = false): array
    {
        $apiKey = env('TMDB_API_KEY') ?? 'ba55da0439797150ed58c4e524584823';
        $client = Services::curlrequest([
            'timeout' => 5,
            'http_errors' => false,
        ]);

        $episodeGlobal = (int) $item->episode;
        $totalEpisodesGlobal = null;
        $seasonTotalEpisodes = null;
        $currentSaison = (int) $item->saison;

        try {
            $url = 'https://api.themoviedb.org/3/search/multi?query=' . urlencode($item->titre) . "&api_key={$apiKey}&language=fr-FR";
            $response = $client->get($url);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);

                if (!empty($body['results'])) {
                    foreach ($body['results'] as $result) {
                        if (isset($result['media_type']) && 'tv' === $result['media_type'] && isset($result['id'])) {
                            $tvUrl = "https://api.themoviedb.org/3/tv/{$result['id']}?api_key={$apiKey}&language=fr-FR";
                            $tvResponse = $client->get($tvUrl);

                            if (200 === $tvResponse->getStatusCode()) {
                                $tvBody = json_decode($tvResponse->getBody(), true);
                                $totalEpisodesGlobal = $tvBody['number_of_episodes'] ?? null;
                                $tmdbTotalSaisons = $tvBody['number_of_seasons'] ?? 1;

                                // Si l'utilisateur est sur une saison supérieure à celle connue par TMDB (Cas des animes fusionnés comme Frieren)
                                if ($currentSaison > 1 && $currentSaison > $tmdbTotalSaisons && $totalEpisodesGlobal > 0) {
                                    $currentTotalEpisodes = (int) $item->total_episodes;

                                    // 1. Déduction mathématique si l'utilisateur a saisi le total de la saison manuellement
                                    if ($currentTotalEpisodes > 0 && $totalEpisodesGlobal >= $currentTotalEpisodes) {
                                        $previousEpisodes = $totalEpisodesGlobal - $currentTotalEpisodes;
                                        $episodeGlobal = $previousEpisodes + (int) $item->episode;
                                    }
                                    // 2. Fallback automatique via Jikan (MyAnimeList) pour retrouver les épisodes de la saison
                                    else {
                                        $jikanEps = $this->fetchSeasonEpisodesFromJikan((string) $item->titre, $currentSaison);
                                        if (null !== $jikanEps && $jikanEps > 0 && $totalEpisodesGlobal >= $jikanEps) {
                                            $previousEpisodes = $totalEpisodesGlobal - $jikanEps;
                                            $episodeGlobal = $previousEpisodes + (int) $item->episode;
                                            $seasonTotalEpisodes = $jikanEps;
                                            $item->total_episodes = $jikanEps;
                                        }
                                    }
                                    if ((int) $item->total_saisons < $currentSaison) {
                                        $item->total_saisons = $currentSaison;
                                    }
                                } else {
                                    // Comportement standard (TMDB a correctement séparé les saisons)
                                    if (isset($tvBody['seasons']) && is_array($tvBody['seasons'])) {
                                        $previousEpisodes = 0;
                                        foreach ($tvBody['seasons'] as $season) {
                                            $seasonNum = (int) ($season['season_number'] ?? -1);
                                            $epCount = (int) ($season['episode_count'] ?? 0);

                                            if ($seasonNum > 0 && $seasonNum < $currentSaison) {
                                                $previousEpisodes += $epCount;
                                            } elseif ($seasonNum === $currentSaison && $epCount > 0) {
                                                $seasonTotalEpisodes = $epCount;
                                            }
                                        }
                                        if ($currentSaison > 1) {
                                            $episodeGlobal = $previousEpisodes + (int) $item->episode;
                                        }
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence
        }

        $result = [
            'episode_global' => $episodeGlobal,
            'total_episodes_global' => $totalEpisodesGlobal,
        ];

        if ($updateSeasonTotal && null !== $seasonTotalEpisodes) {
            $result['total_episodes'] = $seasonTotalEpisodes;
        }

        return $result;
    }

    /**
     * Fallback via Jikan (MyAnimeList) pour récupérer le nombre d'épisodes d'une saison d'anime.
     */
    private function fetchSeasonEpisodesFromJikan(string $titre, int $saison): ?int
    {
        $client = Services::curlrequest([
            'timeout' => 5,
            'http_errors' => false,
        ]);

        try {
            $jikanUrl = 'https://api.jikan.moe/v4/anime?q=' . urlencode($titre . ' season ' . $saison) . '&limit=1';
            $response = $client->get($jikanUrl);

            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);
                if (!empty($body['data']) && !empty($body['data'][0]['episodes'])) {
                    return (int) $body['data'][0]['episodes'];
                }
            }
        } catch (\Exception $e) {
            // Silence l'erreur API
        }

        return null;
    }

    private function canManageItem(?Item $item): bool
    {
        if (!$item || !auth()->loggedIn()) {
            return false;
        }

        $user = auth()->user();
        return (int) $item->id_user === (int) auth()->id() ||
            $user->inGroup('admin', 'superadmin');
    }

    /** Normalize any card URL to its lowercase hostname. */
    private function reportedLinkHost(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if (str_starts_with($url, '//')) $url = 'https:' . $url;
        elseif (! preg_match('~^https?://~i', $url)) $url = 'https://' . $url;

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') return null;
        $host = strtolower(rtrim($host, '.'));
        return (string) preg_replace('/^www\./i', '', $host);
    }

    /** Check both historical reports and cards already marked dead for this host. */
    private function domainAlreadyReported(string $host): bool
    {
        $db = db_connect();
        $logs = $db->table('cron_logs')
            ->select('url_testee')
            ->whereIn('task_name', [CronLogModel::TASK_CONFIRMED_DEAD, CronLogModel::TASK_PENDING_USER_REPORT])
            ->like('url_testee', $host)
            ->get()->getResultArray();
        foreach ($logs as $log) {
            if ($this->reportedLinkHost((string) ($log['url_testee'] ?? '')) === $host) return true;
        }

        $deadItems = $db->table('item')
            ->select('lien')
            ->where('deleted_at', null)
            ->whereIn('link_status', ['reported', 'dead'])
            ->like('lien', $host)
            ->get()->getResultArray();
        foreach ($deadItems as $deadItem) {
            if ($this->reportedLinkHost((string) ($deadItem['lien'] ?? '')) === $host) return true;
        }

        return false;
    }

    /** Whitelisted hosts, with or without the conventional www prefix, cannot be reported. */
    private function isWhitelistedReportHost(string $host): bool
    {
        $normalize = function (string $domain): ?string {
            $domain = $this->reportedLinkHost($domain);
            return $domain === null ? null : (string) preg_replace('/^www\./i', '', $domain);
        };
        $normalizedHost = $normalize($host);
        if ($normalizedHost === null) return false;

        $cutoff = time() - (WhitelistModel::PROTECTION_DAYS * 86400);
        foreach ((new WhitelistModel())->findAll() as $entry) {
            if ($normalize((string) ($entry['domain'] ?? '')) !== $normalizedHost) continue;
            $addedAt = strtotime((string) ($entry['created_at'] ?? ''));
            if ($addedAt !== false && $addedAt >= $cutoff) return true;
        }
        return false;
    }

    /** Refresh an expired whitelist row when its domain is reported again. */
    private function refreshExistingReportWhitelistEntry(string $host, string $updatedAt): void
    {
        $whitelist = new WhitelistModel();
        foreach ($whitelist->findAll() as $entry) {
            $entryHost = $this->reportedLinkHost((string) ($entry['domain'] ?? ''));
            if ($entryHost === null) continue;
            $entryHost = (string) preg_replace('/^www\\./i', '', $entryHost);
            if ($entryHost !== $host) continue;

            if (! $whitelist->update($entry['id'], ['created_at' => $updatedAt])) {
                throw new \RuntimeException('Impossible de renouveler la date de protection du domaine.');
            }
            return;
        }
    }

    /** Enforce rolling quotas per authenticated account and source IP. */
    private function reportRateLimitMessage(): ?string
    {
        $db = db_connect();
        $userId = (int) auth()->id();
        $ipAddress = $this->request->getIPAddress();
        $hourAgo = date('Y-m-d H:i:s', time() - 3600);
        $dayAgo = date('Y-m-d H:i:s', time() - 86400);
        $userHour = $db->table('audit_logs')->where('action', 'Signalement Utilisateur')->where('user_id', $userId)->where('created_at >=', $hourAgo)->countAllResults();
        $userDay = $db->table('audit_logs')->where('action', 'Signalement Utilisateur')->where('user_id', $userId)->where('created_at >=', $dayAgo)->countAllResults();
        $ipHour = $db->table('audit_logs')->where('action', 'Signalement Utilisateur')->where('ip_address', $ipAddress)->where('created_at >=', $hourAgo)->countAllResults();
        $ipDay = $db->table('audit_logs')->where('action', 'Signalement Utilisateur')->where('ip_address', $ipAddress)->where('created_at >=', $dayAgo)->countAllResults();

        if ($userHour >= self::USER_REPORT_LIMIT_PER_HOUR || $ipHour >= self::IP_REPORT_LIMIT_PER_HOUR) {
            return 'Limite atteinte : réessayez dans une heure.';
        }
        if ($userDay >= self::USER_REPORT_LIMIT_PER_DAY || $ipDay >= self::IP_REPORT_LIMIT_PER_DAY) {
            return 'Limite quotidienne de signalements atteinte.';
        }
        return null;
    }

    /** Find active cards whose parsed hostname exactly matches the reported domain. */
    private function activeItemsForReportHost(string $host): array
    {
        $candidates = db_connect()->table('item')
            ->select('id, titre, lien, link_status')
            ->where('deleted_at', null)
            ->where('id_division !=', ItemModel::PERSONAL_DIVISION_ID)
            ->where('lien IS NOT NULL', null, false)
            ->like('lien', $host)
            ->get()->getResult();

        $matches = [];
        foreach ($candidates as $candidate) {
            if ($this->reportedLinkHost((string) $candidate->lien) === $host) $matches[] = $candidate;
        }
        return $matches;
    }

    /** Record user reports without making unreliable network checks. */
    public function reportLink()
    {
        if (! auth()->loggedIn()) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Vous devez être connecté pour signaler un lien.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $json = $this->request->getJSON();
        $itemId = filter_var($json->id ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($itemId === false) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 'message' => 'ID de carte invalide.', 'csrf_token' => csrf_hash(),
            ]);
        }

        $item = $this->model->find($itemId);
        if (! $item || empty($item->lien)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false, 'message' => 'Carte introuvable ou lien vide.', 'csrf_token' => csrf_hash(),
            ]);
        }
        if ((int) $item->id_division === ItemModel::PERSONAL_DIVISION_ID) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Les cartes personnelles sont privées et ne peuvent pas être signalées.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $host = $this->reportedLinkHost((string) $item->lien);
        if ($host === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 'message' => 'Le lien de cette carte ne contient pas de domaine valide.', 'csrf_token' => csrf_hash(),
            ]);
        }
        if ($rateLimitMessage = $this->reportRateLimitMessage()) {
            return $this->response->setStatusCode(429)->setJSON([
                'success' => false, 'message' => $rateLimitMessage, 'csrf_token' => csrf_hash(),
            ]);
        }
        if ($this->isWhitelistedReportHost($host)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ce domaine a été confirmé fonctionnel récemment. Il pourra être signalé à nouveau après 3 jours.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $db = db_connect();
        $lockName = 'deadlink_' . substr(hash('sha256', $host), 0, 48);
        $lockedHost = $host;
        $lock = $db->query('SELECT GET_LOCK(?, 8) AS acquired', [$lockName])->getRowArray();
        if ((int) ($lock['acquired'] ?? 0) !== 1) {
            return $this->response->setStatusCode(503)->setJSON([
                'success' => false, 'message' => 'Le signalement est temporairement occupé. Réessayez.', 'csrf_token' => csrf_hash(),
            ]);
        }

        try {
            $db->transBegin();
            $item = $this->model->find($itemId);
            if (! $item || empty($item->lien)) {
                $db->transRollback();
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false, 'message' => 'Carte introuvable ou lien vide.', 'csrf_token' => csrf_hash(),
                ]);
            }
            if ((int) $item->id_division === ItemModel::PERSONAL_DIVISION_ID) {
                $db->transRollback();
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Les cartes personnelles sont privées et ne peuvent pas être signalées.',
                    'csrf_token' => csrf_hash(),
                ]);
            }
            $host = $this->reportedLinkHost((string) $item->lien);
            if ($host === null || $host !== $lockedHost) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Le lien de cette carte a changé. Rechargez la page avant de le signaler.',
                    'csrf_token' => csrf_hash(),
                ]);
            }
            if ($this->isWhitelistedReportHost($host)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ce domaine a été confirmé fonctionnel récemment. Il pourra être signalé à nouveau après 7 jours.',
                    'csrf_token' => csrf_hash(),
                ]);
            }
            if ($this->domainAlreadyReported($host)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ce domaine a déjà un signalement en attente ou traité.',
                    'csrf_token' => csrf_hash(),
                ]);
            }

            $domainItems = $this->activeItemsForReportHost($host);
            if ($domainItems === []) {
                throw new \RuntimeException('Aucune carte active ne correspond à ce domaine.');
            }
            foreach ($domainItems as $domainItem) {
                if (! $this->model->update($domainItem->id, ['link_status' => 'reported'])) {
                    throw new \RuntimeException('Impossible de mettre à jour le statut des cartes du domaine.');
                }
            }

            $now = date('Y-m-d H:i:s');
            $this->refreshExistingReportWhitelistEntry($host, $now);
            $cronLogId = (new CronLogModel())->insert([
                'task_name' => CronLogModel::TASK_PENDING_USER_REPORT,
                'last_run' => $now,
                'item_id' => $itemId,
                'titre' => $item->titre,
                'url_testee' => 'https://' . $host . '/',
                'code_erreur' => 0,
            ]);
            if (! $cronLogId) {
                throw new \RuntimeException('Impossible d’enregistrer l’historique du signalement.');
            }

            $auditId = (new AuditLogModel())->logAction(
                'Signalement Utilisateur',
                'L’utilisateur ' . auth()->user()->username . " a signalé manuellement le domaine {$host} comme mort (carte ID {$itemId})."
            );
            if (! $auditId || ! $db->transStatus()) {
                throw new \RuntimeException('Impossible d’enregistrer le journal d’audit.');
            }

            $db->transCommit();
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Signalement enregistré. Merci pour votre aide !',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ReportLink] Échec du signalement du domaine {host}: {message}', [
                'host' => $host,
                'message' => $e->getMessage(),
            ]);
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Le signalement n’a pas pu être enregistré. Réessayez plus tard.',
                'csrf_token' => csrf_hash(),
            ]);
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }
}