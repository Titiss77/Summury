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
use Config\Services;

class ItemController extends BaseController
{
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
     */
    public function form($id = null)
    {
        $item = null;
        if (null !== $id) {
            $item = $this->model->find($id);
            if (!$this->canManageItem($item)) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        }

        $userId = auth()->loggedIn() ? auth()->id() : null;
        $subCategories = [];

        if ($userId) {
            $subCategories = $this->model->where('id_user', $userId)->where('sous_categorie IS NOT NULL')
                ->where('sous_categorie !=', '')->distinct()->findColumn('sous_categorie') ?? []
            ;
        }

        $data = [
            'headers' => [],
            'divisions' => $this->model->getDivisions(),
            'subCategories' => $subCategories,
            'statuts' => $this->statutModel->where('nom !=', 'Public')->orderBy('ordre', 'ASC')->findAll(),
            'item' => $item,
            'view' => 'items/item_form',
            'redirect_url' => $this->request->getUserAgent()->getReferrer() ?? site_url('/'),
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
                'date_sortie' => 'permit_empty|valid_date[Y-m-d\\TH:i]',
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

            // Formatage des données optionnelles
            $releaseDate = $this->request->getPost('date_sortie');
            $data['date_sortie'] = empty($releaseDate) ? null : str_replace('T', ' ', (string) $releaseDate).':00';
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

                    return redirect()->to($backUrl.$separator.'open='.$existing->id_division.'#div-'.$existing->id_division)->with('message', 'Votre modification a été soumise au SuperAdmin pour validation.');
                }

                // Force le statut à "Aucun" si on retire la carte du domaine public
                if (in_array($existing->is_public, [1]) && 0 == $data['is_public']) {
                    $data['status'] = 'Aucun';
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

            $subParam = !empty($data['sous_categorie']) ? '&subopen='.urlencode($data['sous_categorie']) : '';
            if ($this->request->getPost('save_add_another')) {
                return redirect()->to('m')->with('success', 'Carte enregistrée. Vous pouvez en ajouter une autre.');
            }
            return redirect()->to($backUrl.$separator.'open='.$data['id_division'].$subParam.'#div-'.$data['id_division'])->with('success', 'Carte enregistrée.');
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

                return redirect()->to($backUrl.$separator.'open='.$id_div.'#div-'.$id_div);
            }
        }
        return redirect()->back();
    }

    /**
     * Incrémente rapidement l'épisode depuis le dashboard et gère la complétion via Jikan/MyAnimeList.
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
            $updateData = [];

            // Détection automatique du passage à la saison suivante ou de la fin
            if ($totalEpisodes > 0 && $newEpisode > $totalEpisodes) {
                if ($totalSaisons > 0 && $newSaison >= $totalSaisons) {
                    $updateData['status'] = 'Terminé';
                    $newEpisode = $totalEpisodes;
                    $updateData['episode'] = $newEpisode;
                    $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Statut passé à Terminé";
                } else {
                    $newEpisode = 1;
                    ++$newSaison;
                    $updateData['saison'] = $newSaison;
                    $updateData['episode'] = $newEpisode;
                    $updateData['total_episodes'] = null; // Vide l'ancien total par sécurité
                    
                    $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode} (Saison {$newSaison})";

                    // Recherche automatique de la nouvelle saison sur Jikan (MyAnimeList)
                    $client = Services::curlrequest([
                        'timeout' => 5,
                        'http_errors' => false,
                    ]);

                    try {
                        $jikanUrl = 'https://api.jikan.moe/v4/anime?q=' . urlencode($item->titre . ' season ' . $newSaison) . '&limit=1';
                        $response = $client->get($jikanUrl);

                        if ($response->getStatusCode() === 200) {
                            $body = json_decode($response->getBody(), true);
                            
                            // On récupère le total d'épisodes de la nouvelle fiche trouvée
                            if (!empty($body['data']) && isset($body['data'][0]['episodes'])) {
                                $updateData['total_episodes'] = $body['data'][0]['episodes'];
                            }
                        }
                    } catch (\Exception $e) {
                        // Silence l'erreur API
                    }
                }
            } else {
                $updateData['episode'] = $newEpisode;
                $logMessage = "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode}";
            }

            $this->model->update($id, $updateData);

            $wasComplete = "Termin\u{00E9}" === $item->status;
            if (!empty($updateData['saison'])) {
                $updatedItem = $this->model->find($id);
                if ($updatedItem) {
                    $this->model->update($id, $this->syncGlobalEpisodesWithTMDB($updatedItem));
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

            (new AuditLogModel())->logAction('Incrémentation Rapide', "Mise à jour de la carte ID {$id} ('{$item->titre}') : Saison passé à {$newSaison}.");

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
            $url = 'https://api.themoviedb.org/3/search/multi?query='.urlencode($query)."&api_key={$apiKey}&language=fr-FR";
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
                            'info' => substr($result['release_date'] ?? $result['first_air_date'] ?? '', 0, 4).' - '.$mediaLabel,
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
                $mdUrl = 'https://api.mangadex.org/manga?title='.urlencode($query).'&limit=5&includes[]=cover_art&order[relevance]=desc';
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
                                'info' => ($year ? $year.' - ' : '').'MANGA',
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
            return $this->response->setJSON(['error' => 'Erreur de recherche : '.$e->getMessage()]);
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

        $supportedDomains = $siteConfigModel->where('is_active', 1)
            ->findColumn('domain') ?? []
        ;

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
     * Vérifie en direct via AJAX la disponibilité d'une vidéo/lecteur sur une URL ciblée
     */
    public function checkDispo()
    {
        $urlCible = $this->request->getGet('urlCible');

        if (!is_string($urlCible) || !ExternalUrlGuard::isPublicHttpUrl($urlCible)) {
            return $this->response->setJSON(['success' => false, 'error' => 'URL invalide.']);
        }

        $urlParts = parse_url((string) $urlCible);
        $host = strtolower(rtrim((string) ($urlParts['host'] ?? ''), '.'));
        if ($host === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'URL invalide.']);
        }

        $siteConfigModel = new SiteConfigModel();
        $sites = $siteConfigModel->where('is_active', 1)->findAll();
        $currentConfig = null;

        foreach ($sites as $config) {
            $configuredDomain = trim((string) $config['domain']);
            $configuredHost = parse_url(str_contains($configuredDomain, '://') ? $configuredDomain : 'https://'.$configuredDomain, PHP_URL_HOST);
            $configuredHost = strtolower(rtrim((string) $configuredHost, '.'));

            if ($configuredHost !== '' && ($host === $configuredHost || str_ends_with($host, '.'.$configuredHost))) {
                $currentConfig = $config;
                break;
            }
        }

        if (!$currentConfig) {
            return $this->response->setJSON(['success' => false, 'error' => 'Domaine non supporté.']);
        }

        $matches = [];
        @preg_match($currentConfig['regex_episode'], (string) $urlCible, $matches);
        $episodeExtrait = $matches[1] ?? null;

        $indicateursPageInvalide = json_decode($currentConfig['indicateurs_page_invalide'], true) ?? [];
        $indicateursLecteur = json_decode($currentConfig['indicateurs_lecteur'], true) ?? [];

        try {
            $client = Services::curlrequest([
                'timeout' => 3, 'connect_timeout' => 2,
                'http_errors' => false, 'allow_redirects' => false,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/Checker',
            ]);

            $response = $client->get($urlCible);
            $statusCode = $response->getStatusCode();

            if (404 === $statusCode) {
                return $this->response->setJSON(['success' => true, 'disponible' => false, 'details' => ['erreur' => 'Page 404']]);
            }

            $html = (string) $response->getBody();
            $estSurFicheAnime = false;

            foreach ($indicateursPageInvalide as $indicator) {
                if (false !== stripos($html, $indicator)) {
                    $estSurFicheAnime = true;
                    break;
                }
            }

            $lecteurPresent = false;
            foreach ($indicateursLecteur as $indicator) {
                $indicatorFinal = $episodeExtrait ? str_replace('{ep}', (string) $episodeExtrait, $indicator) : $indicator;
                if (false !== stripos($html, $indicatorFinal)) {
                    $lecteurPresent = true;
                    break;
                }
            }

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
            $itemIds = array_map(static fn (Item $item): int => (int) $item->id, $itemsToDelete);
            (new CronLogModel())->whereIn('item_id', $itemIds)->delete();
            $this->model->builder()->whereIn('id', $itemIds)->delete();
            (new AuditLogModel())->logAction('Vidage Corbeille', 'La corbeille a été vidée définitivement ('.count($itemIds).' cartes détruites).');
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
     * Interroge TMDB pour calculer les compteurs globaux (épisodes vus au total et total de la série).
     */
    private function syncGlobalEpisodesWithTMDB(Item $item): array
    {
        $apiKey = env('TMDB_API_KEY') ?? 'ba55da0439797150ed58c4e524584823';

        $client = \Config\Services::curlrequest([
            'timeout' => 5,
            'http_errors' => false,
        ]);

        $episodeGlobal = (int) $item->episode;
        $totalEpisodesGlobal = null;
        $currentSaison = (int) $item->saison;

        try {
            $url = 'https://api.themoviedb.org/3/search/multi?query='.urlencode($item->titre)."&api_key={$apiKey}&language=fr-FR";
            $response = $client->get($url);
            
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                
                if (!empty($body['results'])) {
                    foreach ($body['results'] as $result) {
                        if (isset($result['media_type']) && $result['media_type'] === 'tv' && isset($result['id'])) {
                            $tvUrl = "https://api.themoviedb.org/3/tv/{$result['id']}?api_key={$apiKey}&language=fr-FR";
                            $tvResponse = $client->get($tvUrl);
                            
                            if ($tvResponse->getStatusCode() === 200) {
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
                                    // 2. Fallback automatique via Jikan (MyAnimeList) pour retrouver les épisodes de la saison précédente
                                    else {
                                        try {
                                            $jikanUrl = 'https://api.jikan.moe/v4/anime?q=' . urlencode($item->titre . ' season ' . $currentSaison) . '&limit=1';
                                            $jResp = $client->get($jikanUrl);
                                            if ($jResp->getStatusCode() === 200) {
                                                $jBody = json_decode($jResp->getBody(), true);
                                                if (!empty($jBody['data']) && isset($jBody['data'][0]['episodes'])) {
                                                    $jikanEps = (int) $jBody['data'][0]['episodes'];
                                                    if ($jikanEps > 0 && $totalEpisodesGlobal >= $jikanEps) {
                                                        $previousEpisodes = $totalEpisodesGlobal - $jikanEps;
                                                        $episodeGlobal = $previousEpisodes + (int) $item->episode;
                                                        
                                                        // Auto-correction des champs locaux de la base de données
                                                        $item->total_episodes = $jikanEps;
                                                    }
                                                }
                                            }
                                        } catch (\Exception $e) {}
                                    }
                                    
                                    // Assure que le compteur de saisons respecte au moins la saison actuelle de l'utilisateur
                                    if ((int)$item->total_saisons < $currentSaison) {
                                        $item->total_saisons = $currentSaison;
                                    }

                                } else {
                                    // Comportement standard (TMDB a correctement séparé les saisons)
                                    if (isset($tvBody['seasons']) && $currentSaison > 1) {
                                        $previousEpisodes = 0;
                                        foreach ($tvBody['seasons'] as $season) {
                                            if ($season['season_number'] > 0 && $season['season_number'] < $currentSaison) {
                                                $previousEpisodes += $season['episode_count'];
                                            }
                                        }
                                        $episodeGlobal = $previousEpisodes + (int) $item->episode;
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

        return [
            'episode_global' => $episodeGlobal,
            'total_episodes_global' => $totalEpisodesGlobal
        ];
    }

    private function canManageItem(?Item $item): bool
    {
        if (!$item || !auth()->loggedIn()) {
            return false;
        }

        $user = auth()->user();

        return (int) $item->id_user === (int) auth()->id()
            || $user->inGroup('admin', 'superadmin');
    }
}
