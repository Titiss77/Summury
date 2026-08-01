<?php

declare(strict_types=1);

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
        $data = [
            'headers' => $this->model->getHeaders(),
            'divisions' => $this->model->getDivisions(),
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
            if (!auth()->loggedIn()) {
                return redirect()->to('login');
            }

            $rules = [
                'titre' => 'required|max_length[100]',
                'id_division' => 'required|numeric',
                'status' => 'in_list[Aucun,À voir,En cours,En pause,Terminé]',
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
            if ($wantsPublic) {
                $data['is_public'] = $isSuperAdmin ? 1 : 2;
            } else {
                $data['is_public'] = 0;
            }

            $data['date_sortie'] = empty($this->request->getPost('date_sortie')) ? null : $this->request->getPost('date_sortie');
            $data['saison'] = ('' === $this->request->getPost('saison')) ? null : $this->request->getPost('saison');
            $data['episode'] = ('' === $this->request->getPost('episode')) ? null : $this->request->getPost('episode');

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

            if ($id) {
                $canEdit = $existing && ((int) $existing->id_user === (int) auth()->id() || $isAdmin);
                if (!$canEdit) {
                    $audit->logAction('Violation Accès', "Tentative non autorisée de modification sur la carte ID {$id}.");
                    return redirect()->back()->with('error', "Vous n'avez pas les droits pour modifier cette carte.");
                }

                if (1 == $existing->is_public && 0 != $data['is_public'] && !$isSuperAdmin) {
                    $revisionModel = new ItemRevisionModel();
                    $existingRevision = $revisionModel
                        ->where('original_item_id', $id)
                        ->where('revision_status', 'pending')
                        ->first();

                    $revisionData = [
                        'original_item_id' => $id,
                        'id_user' => auth()->id(),
                        'titre' => $data['titre'],
                        'status' => $data['status'],
                        'image' => $data['image'] ?? $existing->image,
                        'lien' => $data['lien'] ?? null,
                        'description' => $data['description'] ?? null,
                        'episode' => $data['episode'] ?? null,
                        'saison' => $data['saison'] ?? null,
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

                    return redirect()
                        ->to($backUrl . $separator . 'open=' . $existing->id_division . '#div-' . $existing->id_division)
                        ->with('message', 'Votre modification a été soumise au SuperAdmin pour validation.');
                }

                $item = new Item($data);
                $this->model->save($item);
                $statutVisibility = 1 == $data['is_public'] ? 'Publique' : 'Privée';
                $audit->logAction('Mise à jour Carte', "Modification de la carte ID {$id} ('{$data['titre']}'). Visibilité : {$statutVisibility}.");

                if (1 == $existing->is_public && 0 == $data['is_public']) {
                    $revisionModel = new ItemRevisionModel();
                    $revisionModel
                        ->where('original_item_id', $id)
                        ->where('revision_status', 'pending')
                        ->delete();
                    $audit->logAction('Nettoyage Draft', "Passage en privé de la carte ID {$id} : Suppression automatique des drafts en attente.");
                }
            } else {
                $maxPosition = $this
                    ->model
                    ->where('id_division', $data['id_division'])
                    ->where('id_user', $data['id_user'])
                    ->selectMax('position')
                    ->get()
                    ->getRow()
                    ->position;

                $data['position'] = ($maxPosition !== null) ? ((int) $maxPosition + 1) : 0;
                $item = new Item($data);
                $this->model->save($item);
                $newId = $this->model->getInsertID();

                $statutVisibility = 2 == $data['is_public'] ? 'En attente' : (1 == $data['is_public'] ? 'Publique' : 'Privée');
                $audit->logAction('Création Carte', "Création de la carte ID {$newId} ('{$data['titre']}'). Visibilité initiale: {$statutVisibility}.");
            }

            return redirect()->to($backUrl . $separator . 'open=' . $data['id_division'] . '#div-' . $data['id_division']);
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

                $audit = new AuditLogModel();
                $audit->logAction('Suppression Carte', "Suppression de la carte ID {$id} ('{$titre}').");

                $backUrl = $this->request->getUserAgent()->getReferrer() ?: site_url('/');
                $separator = (str_contains($backUrl, '?')) ? '&' : '?';

                return redirect()->to($backUrl . $separator . 'open=' . $id_div . '#div-' . $id_div);
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

            $audit = new AuditLogModel();
            $audit->logAction('Incrémentation Rapide', "Mise à jour de la carte ID {$id} ('{$item->titre}') : Épisode passé à {$newEpisode}.");

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'new_episode' => $newEpisode,
                    'csrf_token' => csrf_hash(),
                ]);
            }
        }

        return redirect()->back();
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        $type = $this->request->getGet('type');

        if (empty($query)) {
            return $this->response->setJSON([]);
        }

        $client = Services::curlrequest([
            'timeout'         => 10,
            'connect_timeout' => 5,
            'http_errors'     => false,
            'verify'          => false,
            'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ]);

        try {
            if (filter_var($query, FILTER_VALIDATE_URL)) {
                $metaData = $this->scrapeOpenGraph($query);
                return $this->response->setJSON($metaData ? [$metaData] : ['error' => 'Impossible de lire le lien.']);
            }

            if ('manga' === $type || 'anime' === $type) {
                $url = "https://api.jikan.moe/v4/{$type}?q=" . urlencode($query) . '&limit=5';
                $response = $client->get($url);
                $body = json_decode($response->getBody(), true);

                if ($response->getStatusCode() !== 200) {
                    $erreur = $body['message'] ?? $body['error'] ?? "Erreur API Jikan ({$response->getStatusCode()})";
                    return $this->response->setJSON(['error' => $erreur]);
                }

                return $this->response->setJSON($body);
            }

            $apiKey = env('TMDB_API_KEY') ?? 'ba55da0439797150ed58c4e524584823';
            $url = 'https://api.themoviedb.org/3/search/multi?query=' . urlencode($query) . "&api_key={$apiKey}&language=fr-FR";
            $response = $client->get($url);
            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                 $erreur = $body['status_message'] ?? "Erreur API TMDB ({$response->getStatusCode()})";
                 return $this->response->setJSON(['error' => $erreur]);
            }

            return $this->response->setJSON($body);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Erreur de recherche : ' . $e->getMessage()]);
        }
    }

    public function checkToGlobal()
    {
        $data = [
            'items' => $this->model->checkToGlobal(),
        ];
        return view('global_items', $data);
    }

    public function turnToAdmin($id)
    {
        $item = $this->model->find($id);
        $isAdmin = auth()->user()->inGroup('admin', 'superadmin');

        if ($item && ((int) $item->id_user === (int) auth()->id() || $isAdmin)) {
            $this->model->update($id, ['id_user' => 1]);
            $audit = new AuditLogModel();
            $audit->logAction('Transfert Carte', "La carte ID {$id} ('{$item->titre}') a été transférée à l'admin.");
            return redirect()->back()->with('message', "La carte a été transférée à l'admin avec succès.");
        }

        return redirect()->back()->with('error', "Vous n'avez pas les droits pour effectuer cette action.");
    }

    public function updateOrder()
    {
        if ($this->request->is('ajax')) {
            $json = $this->request->getJSON();

            if (isset($json->order) && is_array($json->order)) {
                if (!auth()->loggedIn()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error'   => 'Session expirée. Veuillez recharger la page.'
                    ]);
                }

                $userId = auth()->id();
                $isAdmin = auth()->user()->inGroup('admin', 'superadmin');
                $count = 0;

                foreach ($json->order as $index => $itemId) {
                    $item = $this->model->find($itemId);
                    if ($item && ((int) $item->id_user === (int) $userId || $isAdmin)) {
                        $this->model->update($itemId, ['position' => $index]);
                        ++$count;
                    }
                }

                if ($count > 0) {
                    $audit = new AuditLogModel();
                    $audit->logAction('Reorganisation', "L'utilisateur a modifié l'ordre d'affichage de {$count} carte(s).");
                }

                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => 'Ordre mis à jour avec succès.',
                    'csrf_token' => csrf_hash(),
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'error'   => 'Requête invalide ou données manquantes.',
        ]);
    }

    private function scrapeOpenGraph(string $url): ?array
    {
        $html = @file_get_contents($url);
        if (!$html) {
            return null;
        }

        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $tags = $doc->getElementsByTagName('meta');

        $data = [
            'titre' => '',
            'description' => '',
            'image' => '',
            'lien' => $url,
            'is_link' => true,
        ];

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

    public function checkDispo()
    {
        $urlCible = $this->request->getGet('urlCible');
        
        if (empty($urlCible) || !filter_var($urlCible, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['success' => false, 'error' => 'URL invalide.']);
        }

        $siteConfigModel = new SiteConfigModel();
        $sites = $siteConfigModel->where('is_active', 1)->findAll();

        $currentConfig = null;
        foreach ($sites as $config) {
            if (stripos($urlCible, $config['domain']) !== false) {
                $currentConfig = $config;
                break;
            }
        }

        if (!$currentConfig) {
            return $this->response->setJSON([
                'success' => false, 
                'error' => 'Domaine non supporté par le script de vérification.'
            ]);
        }

        preg_match($currentConfig['regex_episode'], $urlCible, $matches);
        $episodeExtrait = $matches[1] ?? null;

        $indicateursPageInvalide = json_decode($currentConfig['indicateurs_page_invalide'], true) ?? [];
        $indicateursLecteur = json_decode($currentConfig['indicateurs_lecteur'], true) ?? [];

        try {
            $client = \Config\Services::curlrequest([
                'timeout'         => 8,
                'connect_timeout' => 5,
                'http_errors'     => false,
                'allow_redirects' => true,
                'verify'          => false,
                'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CodeIgniter4/Checker'
            ]);

            $response = $client->get($urlCible);
            
            if ($response->getStatusCode() === 404) {
                return $this->response->setJSON([
                    'success'    => true,
                    'disponible' => false,
                    'details'    => ['erreur' => 'Page 404 retournée']
                ]);
            }

            $html = (string) $response->getBody();

            $estSurFicheAnime = false;
            foreach ($indicateursPageInvalide as $indicator) {
                if (stripos($html, $indicator) !== false) {
                    $estSurFicheAnime = true;
                    break;
                }
            }

            // NOUVEAU: Si le scraper atterrit sur une fiche générique, on stoppe la vérification silencieusement.
            if ($estSurFicheAnime) {
                return $this->response->setJSON([
                    'success' => false,
                    'error'   => 'Page générique (fiche) détectée. Vérification ignorée.'
                ]);
            }

            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatches);
            $titrePage = isset($titleMatches[1]) ? trim($titleMatches[1]) : '';
            
            $titreContientEpisode = false;
            if ($episodeExtrait) {
                $epNum = (int)$episodeExtrait;
                $titreContientEpisode = (stripos($titrePage, $episodeExtrait) !== false) || 
                                        (stripos($titrePage, "Episode {$epNum}") !== false) ||
                                        (stripos($titrePage, "Chapitre {$epNum}") !== false);
            }

            $lecteurPresent = false;
            foreach ($indicateursLecteur as $indicator) {
                if (stripos($html, $indicator) !== false) {
                    $lecteurPresent = true;
                    break;
                }
            }
            
            $estDisponible = ($titreContientEpisode || $lecteurPresent);

            return $this->response->setJSON([
                'success'    => true,
                'disponible' => $estDisponible,
                'details'    => [
                    'estSurFicheAnime' => $estSurFicheAnime,
                    'titrePage'        => $titrePage,
                    'lecteurPresent'   => $lecteurPresent,
                    'episodeDetecte'   => $episodeExtrait
                ]
            ]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false, 
                'error'   => 'Erreur Interne : ' . $e->getMessage()
            ]);
        }
    }
}