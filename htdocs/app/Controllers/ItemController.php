<?php declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Item;
use App\Models\AuditLogModel;
use App\Models\ItemModel;
use App\Models\ItemRevisionModel;
use App\Models\SiteConfigModel;
use Config\Services;

class ItemController extends BaseController
{
    private $model;

    public function __construct()
    {
        $this->model = new ItemModel();
    }

    public function form($id = null)
    {
        $userId = auth()->loggedIn() ? auth()->id() : null;
        $subCategories = [];
        if ($userId) {
            $subCategories = $this->model->where('id_user', $userId)->where('sous_categorie IS NOT NULL')
                ->where('sous_categorie !=', '')->distinct()->findColumn('sous_categorie') ?? [];
        }
        $data = [
            'headers' => $this->model->getHeaders(),
            'divisions' => $this->model->getDivisions(),
            'subCategories' => $subCategories,
            'item' => null,
            'view' => 'item_form',
            'redirect_url' => $this->request->getUserAgent()->getReferrer() ?? site_url('/'),
        ];
        if (null !== $id) {
            $data['item'] = $this->model->find($id);
        }
        return view('item_form', $data);
    }

    public function save()
    {
        if ($this->request->is('post')) {
            if (!auth()->loggedIn()) return redirect()->to('login');
            
            $rules = [
                'titre' => 'required|max_length[100]',
                'id_division' => 'required|numeric',
                'status' => 'in_list[Aucun, À voir,En cours,En pause,Terminé]'
            ];
            
            if (!$this->validate($rules)) return redirect()->back()->withInput()->with('error', 'Erreur dans le formulaire.');
            
            $data = $this->request->getPost();
            $id = $this->request->getPost('id');
            $isAdmin = auth()->user()->inGroup('admin', 'superadmin');
            $isSuperAdmin = auth()->user()->inGroup('superadmin');
            $audit = new AuditLogModel();
            
            $wantsPublic = $this->request->getPost('is_public');
            $data['is_public'] = $wantsPublic ? ($isSuperAdmin ? 1 : 2) : 0;
            $data['date_sortie'] = empty($this->request->getPost('date_sortie')) ? null : $this->request->getPost('date_sortie');
            
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
                if ($existing) $data['id_user'] = $existing->id_user;
            } else {
                $data['id_user'] = auth()->id();
            }
            
            $backUrl = $this->request->getPost('redirect_url') ?: site_url('/');
            $separator = (str_contains($backUrl, '?')) ? '&' : '?';
            
            if ($id) {
                $canEdit = $existing && ((int) $existing->id_user === (int) auth()->id() || $isAdmin);
                if (!$canEdit) {
                    $audit->logAction('Violation Accès', "Tentative non autorisée de modification sur la carte ID {$id}.");
                    return redirect()->back()->with('error', "Vous n'avez pas les droits pour modifier cette carte.");
                }
                
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
                    if ($existingRevision) $revisionData['id'] = $existingRevision['id'];
                    $revisionModel->save($revisionData);
                    
                    $actionLog = $existingRevision ? 'Mise à jour Draft' : 'Soumission Draft';
                    $audit->logAction($actionLog, "L'utilisateur a proposé une modification pour la carte publique ID {$id} ('{$existing->titre}').");
                    return redirect()->to($backUrl.$separator.'open='.$existing->id_division.'#div-'.$existing->id_division)->with('message', 'Votre modification a été soumise au SuperAdmin pour validation.');
                }
                
                $item = new Item($data);
                $this->model->save($item);
                $statutVisibility = 1 == $data['is_public'] ? 'Publique' : 'Privée';
                $audit->logAction('Mise à jour Carte', "Modification de la carte ID {$id} ('{$data['titre']}'). Visibilité : {$statutVisibility}.");
                
                if (1 == $existing->is_public && 0 == $data['is_public']) {
                    (new ItemRevisionModel())->where('original_item_id', $id)->where('revision_status', 'pending')->delete();
                    $audit->logAction('Nettoyage Draft', "Passage en privée de la carte ID {$id} : Suppression automatique des drafts en attente.");
                }
            } else {
                $maxPosition = $this->model->where('id_division', $data['id_division'])->where('id_user', $data['id_user'])->selectMax('position')->get()->getRow()->position;
                $data['position'] = (null !== $maxPosition) ? ((int) $maxPosition + 1) : 0;
                $item = new Item($data);
                $this->model->save($item);
                $newId = $this->model->getInsertID();
                $statutVisibility = 2 == $data['is_public'] ? 'En attente' : (1 == $data['is_public'] ? 'Publique' : 'Privée');
                $audit->logAction('Création Carte', "Création de la carte ID {$newId} ('{$data['titre']}'). Visibilité initiale: {$statutVisibility}.");
            }
            return redirect()->to($backUrl.$separator.'open='.$data['id_division'].'#div-'.$data['id_division']);
        }
    }

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

    public function incrementEpisode($id)
    {
        $item = $this->model->find($id);
        if ($item) {
            $newEpisode = (int) $item->episode + 1;
            $this->model->update($id, ['episode' => $newEpisode]);
            (new AuditLogModel())->logAction('Incrémentation Rapide', "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode}.");
            if ($this->request->isAJAX()) return $this->response->setJSON(['success' => true, 'new_episode' => $newEpisode, 'csrf_token' => csrf_hash()]);
        }
        return redirect()->back();
    }

    public function incrementSaison($id)
    {
        $item = $this->model->find($id);
        if ($item) {
            $newSaison = (int) $item->saison + 1;
            $this->model->update($id, ['saison' => $newSaison]);
            (new AuditLogModel())->logAction('Incrémentation Rapide', "Mise à jour de la carte ID {$id} ('{$item->titre}') : Saison passé à {$newSaison}.");
            if ($this->request->isAJAX()) return $this->response->setJSON(['success' => true, 'new_saison' => $newSaison, 'csrf_token' => csrf_hash()]);
        }
        return redirect()->back();
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        $type = $this->request->getGet('type');

        if (empty($query)) return $this->response->setJSON([]);
        
        $cache = Services::cache();
        // Cache v5 pour purger les anciens résultats en anglais
        $cacheKey = 'api_search_v5_'.md5($query.'_'.$type);
        if ($cachedResult = $cache->get($cacheKey)) return $this->response->setJSON($cachedResult);
        
        $client = Services::curlrequest([
            'timeout' => 8,
            'connect_timeout' => 5,
            'http_errors' => false,
            'verify' => false,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/AMFS',
        ]);

        try {
            // 1. GESTION DES LIENS DIRECTS
            if (filter_var($query, FILTER_VALIDATE_URL)) {
                $metaData = $this->scrapeOpenGraph($query);
                $body = $metaData ? [$metaData] : ['error' => 'Impossible de lire le lien.'];
                if (!isset($body['error'])) $cache->save($cacheKey, $body, 3600);
                return $this->response->setJSON($body);
            }

            $unifiedResults = [];
            $apiKey = env('TMDB_API_KEY') ?? 'ba55da0439797150ed58c4e524584823';

            // 2. RECHERCHE TMDB (Films / Séries TV / Animes)
            $url = 'https://api.themoviedb.org/3/search/multi?query='.urlencode($query)."&api_key={$apiKey}&language=fr-FR";
            $response = $client->get($url);
            
            if (200 === $response->getStatusCode()) {
                $body = json_decode($response->getBody(), true);
                if (isset($body['results']) && is_array($body['results'])) {
                    foreach ($body['results'] as $result) {
                        if (isset($result['media_type']) && $result['media_type'] === 'person') continue;

                        // Filtre Intelligent : Différencier les ANIME des TV Séries classiques
                        $isAnime = false;
                        if (isset($result['genre_ids']) && is_array($result['genre_ids']) && in_array(16, $result['genre_ids'])) {
                            if (isset($result['origin_country']) && is_array($result['origin_country']) && in_array('JP', $result['origin_country'])) {
                                $isAnime = true;
                            }
                        }

                        $mediaLabel = strtoupper($result['media_type'] ?? 'TMDB');
                        if ($mediaLabel === 'TV') {
                            $mediaLabel = $isAnime ? 'ANIME' : 'TV';
                        } elseif ($mediaLabel === 'MOVIE') {
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
                            'seasons_data' => null
                        ];

                        if (isset($result['media_type']) && $result['media_type'] === 'tv' && isset($result['id'])) {
                            try {
                                $tvUrl = "https://api.themoviedb.org/3/tv/{$result['id']}?api_key={$apiKey}&language=fr-FR";
                                $tvResponse = $client->get($tvUrl);
                                if (200 === $tvResponse->getStatusCode()) {
                                    $tvBody = json_decode($tvResponse->getBody(), true);
                                    if (isset($tvBody['number_of_episodes'])) $item['total_episodes'] = $tvBody['number_of_episodes'];
                                    if (isset($tvBody['number_of_seasons'])) $item['total_saisons'] = $tvBody['number_of_seasons'];
                                    
                                    if (isset($tvBody['seasons'])) {
                                        $seasons = [];
                                        foreach ($tvBody['seasons'] as $season) {
                                            $seasons[$season['season_number']] = $season['episode_count'];
                                        }
                                        $item['seasons_data'] = $seasons;
                                    }
                                }
                            } catch (\Exception $e) { } 
                        }
                        $unifiedResults[] = $item;
                    }
                }
            }

            // 3. RECHERCHE MANGADEX (Mangas & Scans) - Support du Français
            try {
                // order[relevance]=desc permet de remonter les mangas les plus connus en premier
                $mdUrl = "https://api.mangadex.org/manga?title=".urlencode($query)."&limit=5&includes[]=cover_art&order[relevance]=desc";
                
                $mdResponse = $client->get($mdUrl, [
                    'headers' => [
                        'User-Agent' => 'AMFS-App/1.0',
                        'Accept' => 'application/json'
                    ]
                ]);
                
                if (200 === $mdResponse->getStatusCode()) {
                    $mdBody = json_decode($mdResponse->getBody(), true);
                    if (isset($mdBody['data']) && is_array($mdBody['data'])) {
                        foreach ($mdBody['data'] as $m) {
                            $attr = $m['attributes'] ?? [];
                            
                            // On prend le titre principal (Souvent en anglais ou romaji pour un bon rendu)
                            $titre = $attr['title']['en'] ?? $attr['title']['ja-ro'] ?? $attr['title']['fr'] ?? 'Inconnu';
                            if (is_array($titre)) $titre = 'Inconnu'; // Fallback sécurité

                            // On cible spécifiquement la description en Français (fallback sur anglais si introuvable)
                            $description = $attr['description']['fr'] ?? $attr['description']['en'] ?? '';
                            
                            $year = $attr['year'] ?? '';
                            
                            // Récupération de l'image de couverture
                            $fileName = '';
                            if (isset($m['relationships'])) {
                                foreach ($m['relationships'] as $rel) {
                                    if ($rel['type'] === 'cover_art' && isset($rel['attributes']['fileName'])) {
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
                                'description' => strip_tags((string)$description),
                                'info' => ($year ? $year . ' - ' : '') . 'MANGA',
                                'lien' => '',
                                'total_episodes' => $attr['lastChapter'] ?? '',
                                'total_saisons' => $attr['lastVolume'] ?? '',
                                'seasons_data' => null
                            ];
                        }
                    }
                }
            } catch (\Exception $e) { } // Ignore silencieusement si MangaDex échoue

            $finalBody = ['unified' => $unifiedResults];
            $cache->save($cacheKey, $finalBody, 3600);
            return $this->response->setJSON($finalBody);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Erreur de recherche : '.$e->getMessage()]);
        }
    }

    public function checkToGlobal() { return view('global_items', ['items' => $this->model->checkToGlobal()]); }

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

    public function updateOrder()
    {
        if ($this->request->is('ajax')) {
            $json = $this->request->getJSON();
            if (isset($json->order) && is_array($json->order)) {
                if (!auth()->loggedIn()) return $this->response->setJSON(['success' => false, 'error' => 'Session expirée.']);
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
                if ($count > 0) (new AuditLogModel())->logAction('Reorganisation', "Ordre d'affichage de {$count} carte(s) modifié.");
                return $this->response->setJSON(['success' => true, 'message' => 'Ordre mis à jour.', 'csrf_token' => csrf_hash()]);
            }
        }
        return $this->response->setJSON(['success' => false, 'error' => 'Requête invalide.']);
    }

    public function checkDispo()
    {
        $urlCible = $this->request->getGet('urlCible');
        if (empty($urlCible) || !filter_var($urlCible, FILTER_VALIDATE_URL)) return $this->response->setJSON(['success' => false, 'error' => 'URL invalide.']);
        
        $siteConfigModel = new SiteConfigModel();
        $sites = $siteConfigModel->where('is_active', 1)->findAll();
        $currentConfig = null;
        
        foreach ($sites as $config) {
            if (false !== stripos($urlCible, $config['domain'])) {
                $currentConfig = $config;
                break;
            }
        }
        if (!$currentConfig) return $this->response->setJSON(['success' => false, 'error' => 'Domaine non supporté.']);
        
        preg_match($currentConfig['regex_episode'], $urlCible, $matches);
        $episodeExtrait = $matches[1] ?? null;
        
        $indicateursPageInvalide = json_decode($currentConfig['indicateurs_page_invalide'], true) ?? [];
        $indicateursLecteur = json_decode($currentConfig['indicateurs_lecteur'], true) ?? [];
        
        try {
            $client = Services::curlrequest([
                'timeout' => 8, 'connect_timeout' => 5, 'http_errors' => false,
                'allow_redirects' => true, 'verify' => false,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/Checker',
            ]);
            $response = $client->get($urlCible);
            
            if (404 === $response->getStatusCode()) return $this->response->setJSON(['success' => true, 'disponible' => false, 'details' => ['erreur' => 'Page 404']]);
            
            $html = (string) $response->getBody();
            $estSurFicheAnime = false;
            foreach ($indicateursPageInvalide as $indicator) {
                if (false !== stripos($html, $indicator)) { $estSurFicheAnime = true; break; }
            }
            
            $lecteurPresent = false;
            foreach ($indicateursLecteur as $indicator) {
                $indicatorFinal = $episodeExtrait ? str_replace('{ep}', (string) $episodeExtrait, $indicator) : $indicator;
                if (false !== stripos($html, $indicatorFinal)) { $lecteurPresent = true; break; }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'disponible' => !$estSurFicheAnime && $lecteurPresent,
                'details' => ['estSurFicheAnime' => $estSurFicheAnime, 'lecteurPresent' => $lecteurPresent, 'episodeDetecte' => $episodeExtrait],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Erreur Interne : '.$e->getMessage()]);
        }
    }

    private function scrapeOpenGraph(string $url): ?array
    {
        $html = @file_get_contents($url);
        if (!$html) return null;
        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $tags = $doc->getElementsByTagName('meta');
        
        $data = ['titre' => '', 'description' => '', 'image' => '', 'lien' => $url, 'is_link' => true];
        foreach ($tags as $tag) {
            if ($tag->hasAttribute('property')) {
                $property = $tag->getAttribute('property');
                if ('og:title' === $property) $data['titre'] = $tag->getAttribute('content');
                if ('og:description' === $property) $data['description'] = $tag->getAttribute('content');
                if ('og:image' === $property) $data['image'] = $tag->getAttribute('content');
            }
        }
        if (empty($data['titre'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) $data['titre'] = $titles->item(0)->nodeValue;
        }
        return $data;
    }
}