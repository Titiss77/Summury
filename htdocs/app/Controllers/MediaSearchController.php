<?php

declare(strict_types=1);

namespace App\Controllers;

use Config\Services;

/**
 * Recherche multi-sources pour l'auto-remplissage des cartes.
 *
 * Sources utilisées :
 * - TMDB : films, séries, anime, streaming légal
 * - AniList : anime et mangas
 * - MangaDex : mangas
 * - YouTube Data API : vidéos (si YOUTUBE_API_KEY est défini dans .env)
 */
class MediaSearchController extends BaseController
{
    private $client;
    private string $tmdbApiKey = '';

    public function __construct()
    {
        $this->client = Services::curlrequest([
            'timeout' => 7,
            'connect_timeout' => 4,
            'http_errors' => false,
            'verify' => false,
            'allow_redirects' => true,
            'user_agent' => 'Summury/1.0 CodeIgniter4 Media Search',
        ]);

        // On conserve la clé actuellement utilisée par Summury pour éviter
        // de casser le fonctionnement si le .env ne contient pas encore TMDB_API_KEY.
        $this->tmdbApiKey = trim((string) (env('TMDB_API_KEY') ?: 'ba55da0439797150ed58c4e524584823'));
    }

    public function search()
    {
        $query = trim((string) $this->request->getGet('q'));
        $type = strtolower(trim((string) $this->request->getGet('type')));

        if ($query === '') {
            return $this->response->setJSON(['unified' => []]);
        }

        // Une URL reste traitée comme avant : récupération des métadonnées OpenGraph.
        if (filter_var($query, FILTER_VALIDATE_URL)) {
            $metaData = $this->scrapeOpenGraph($query);
            return $this->response->setJSON($metaData ? [$metaData] : ['error' => 'Impossible de lire le lien.']);
        }

        $type = $this->normalizeType($type);
        $results = [];

        try {
            switch ($type) {
                case 'film':
                    $results = $this->searchTmdbMovies($query);
                    break;

                case 'serie':
                    $results = $this->searchTmdbSeries($query);
                    break;

                case 'anime':
                    $results = array_merge(
                        $this->searchTmdbAnime($query),
                        $this->searchAniList($query, 'ANIME'),
                        $this->searchMangaDex($query, true)
                    );
                    break;

                case 'manga':
                    $results = array_merge(
                        $this->searchAniList($query, 'MANGA'),
                        $this->searchMangaDex($query, false)
                    );
                    break;

                case 'video':
                    $results = $this->searchYouTube($query);
                    break;

                case 'streaming':
                    $results = $this->searchStreaming($query);
                    break;

                default:
                    // Pour une catégorie générique, on cherche dans les films/séries/anime/manga.
                    $results = array_merge(
                        $this->searchTmdbMovies($query, 4),
                        $this->searchTmdbSeries($query, 4),
                        $this->searchAniList($query, 'ANIME', 4),
                        $this->searchAniList($query, 'MANGA', 4)
                    );
                    break;
            }
        } catch (\Throwable $e) {
            log_message('error', 'MediaSearchController: '.$e->getMessage());
        }

        $results = $this->prepareResults($results, $query, $type);

        return $this->response->setJSON([
            'unified' => $results,
            'type' => $type,
            'count' => count($results),
        ]);
    }

    private function normalizeType(string $type): string
    {
        $type = str_replace(['é', 'è', 'ê', 'à'], ['e', 'e', 'e', 'a'], $type);

        if (str_contains($type, 'anime')) {
            return 'anime';
        }
        if (str_contains($type, 'manga')) {
            return 'manga';
        }
        if (str_contains($type, 'serie') || str_contains($type, 'tv')) {
            return 'serie';
        }
        if (str_contains($type, 'film') || str_contains($type, 'movie')) {
            return 'film';
        }
        if (str_contains($type, 'video') || str_contains($type, 'youtube')) {
            return 'video';
        }
        if (str_contains($type, 'stream')) {
            return 'streaming';
        }

        return 'autre';
    }

    private function searchTmdbMovies(string $query, int $limit = 8): array
    {
        $data = $this->tmdbGet('/search/movie', [
            'query' => $query,
            'language' => 'fr-FR',
            'include_adult' => 'false',
            'page' => 1,
        ]);

        $results = [];
        foreach (array_slice($data['results'] ?? [], 0, $limit) as $result) {
            $poster = $result['poster_path'] ?? '';
            if ($poster === '') {
                continue;
            }

            $title = (string) ($result['title'] ?? $result['original_title'] ?? 'Inconnu');
            $year = substr((string) ($result['release_date'] ?? ''), 0, 4);

            $results[] = [
                'titre' => $title,
                'imageThumb' => 'https://image.tmdb.org/t/p/w200'.$poster,
                'imageLarge' => 'https://image.tmdb.org/t/p/w500'.$poster,
                'description' => (string) ($result['overview'] ?? ''),
                'info' => ($year ? $year.' - ' : '').'FILM',
                'lien' => '',
                'total_episodes' => '',
                'total_saisons' => '',
                'seasons_data' => null,
                'source' => 'TMDB',
                'sourcePriority' => 100,
                'externalId' => $result['id'] ?? null,
                'mediaType' => 'movie',
                'providers' => [],
                'score' => 0,
            ];
        }

        return $results;
    }

    private function searchTmdbSeries(string $query, int $limit = 8): array
    {
        $data = $this->tmdbGet('/search/tv', [
            'query' => $query,
            'language' => 'fr-FR',
            'include_adult' => 'false',
            'page' => 1,
        ]);

        $results = [];
        foreach (array_slice($data['results'] ?? [], 0, $limit) as $result) {
            $poster = $result['poster_path'] ?? '';
            if ($poster === '') {
                continue;
            }

            $title = (string) ($result['name'] ?? $result['original_name'] ?? 'Inconnu');
            $year = substr((string) ($result['first_air_date'] ?? ''), 0, 4);
            $details = $this->tmdbGet('/tv/'.(int) ($result['id'] ?? 0), [
                'language' => 'fr-FR',
            ]);

            $seasonsData = [];
            foreach ($details['seasons'] ?? [] as $season) {
                $seasonNumber = (int) ($season['season_number'] ?? -1);
                if ($seasonNumber >= 0) {
                    $seasonsData[$seasonNumber] = (int) ($season['episode_count'] ?? 0);
                }
            }

            $results[] = [
                'titre' => $title,
                'imageThumb' => 'https://image.tmdb.org/t/p/w200'.$poster,
                'imageLarge' => 'https://image.tmdb.org/t/p/w500'.$poster,
                'description' => (string) ($result['overview'] ?? ''),
                'info' => ($year ? $year.' - ' : '').'SÉRIE',
                'lien' => '',
                'total_episodes' => (int) ($details['number_of_episodes'] ?? 0),
                'total_saisons' => (int) ($details['number_of_seasons'] ?? 0),
                'seasons_data' => $seasonsData,
                'source' => 'TMDB',
                'sourcePriority' => 100,
                'externalId' => $result['id'] ?? null,
                'mediaType' => 'tv',
                'providers' => [],
                'score' => 0,
            ];
        }

        return $results;
    }

    private function searchTmdbAnime(string $query, int $limit = 8): array
    {
        $data = $this->tmdbGet('/search/multi', [
            'query' => $query,
            'language' => 'fr-FR',
            'include_adult' => 'false',
            'page' => 1,
        ]);

        $results = [];
        foreach ($data['results'] ?? [] as $result) {
            $mediaType = $result['media_type'] ?? '';
            if (!in_array($mediaType, ['tv', 'movie'], true)) {
                continue;
            }

            $genres = $result['genre_ids'] ?? [];
            $countries = $result['origin_country'] ?? [];
            $isJapaneseAnimation = in_array(16, $genres, true) && in_array('JP', $countries, true);

            if (!$isJapaneseAnimation) {
                continue;
            }

            $poster = $result['poster_path'] ?? '';
            if ($poster === '') {
                continue;
            }

            $title = (string) ($result['title'] ?? $result['name'] ?? $result['original_name'] ?? 'Inconnu');
            $year = substr((string) ($result['release_date'] ?? $result['first_air_date'] ?? ''), 0, 4);
            $details = $mediaType === 'tv'
                ? $this->tmdbGet('/tv/'.(int) ($result['id'] ?? 0), ['language' => 'fr-FR'])
                : [];

            $seasonsData = [];
            foreach ($details['seasons'] ?? [] as $season) {
                $seasonNumber = (int) ($season['season_number'] ?? -1);
                if ($seasonNumber >= 0) {
                    $seasonsData[$seasonNumber] = (int) ($season['episode_count'] ?? 0);
                }
            }

            $results[] = [
                'titre' => $title,
                'imageThumb' => 'https://image.tmdb.org/t/p/w200'.$poster,
                'imageLarge' => 'https://image.tmdb.org/t/p/w500'.$poster,
                'description' => (string) ($result['overview'] ?? ''),
                'info' => ($year ? $year.' - ' : '').'ANIME',
                'lien' => '',
                'total_episodes' => (int) ($details['number_of_episodes'] ?? 0),
                'total_saisons' => (int) ($details['number_of_seasons'] ?? 0),
                'seasons_data' => $seasonsData,
                'source' => 'TMDB',
                'sourcePriority' => 100,
                'externalId' => $result['id'] ?? null,
                'mediaType' => $mediaType,
                'providers' => [],
                'score' => 0,
            ];

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    private function searchAniList(string $query, string $mediaType, int $limit = 8): array
    {
        $graphql = <<<'GRAPHQL'
query ($search: String!, $type: MediaType!, $perPage: Int) {
  Page(page: 1, perPage: $perPage) {
    media(search: $search, type: $type, isAdult: false) {
      id
      title { romaji english native }
      description(asHtml: false)
      startDate { year }
      episodes
      chapters
      volumes
      coverImage { extraLarge large medium }
      format
      countryOfOrigin
    }
  }
}
GRAPHQL;

        try {
            $response = $this->client->post('https://graphql.anilist.co', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'query' => $graphql,
                    'variables' => [
                        'search' => $query,
                        'type' => $mediaType,
                        'perPage' => $limit,
                    ],
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $body = json_decode((string) $response->getBody(), true);
            $media = $body['data']['Page']['media'] ?? [];
            $results = [];

            foreach ($media as $item) {
                $title = (string) (
                    $item['title']['english']
                    ?? $item['title']['romaji']
                    ?? $item['title']['native']
                    ?? 'Inconnu'
                );

                $image = (string) (
                    $item['coverImage']['extraLarge']
                    ?? $item['coverImage']['large']
                    ?? $item['coverImage']['medium']
                    ?? ''
                );

                if ($image === '') {
                    continue;
                }

                $year = (string) ($item['startDate']['year'] ?? '');
                $label = $mediaType === 'ANIME' ? 'ANIME' : 'MANGA';

                $results[] = [
                    'titre' => $title,
                    'imageThumb' => $image,
                    'imageLarge' => $image,
                    'description' => trim(strip_tags((string) ($item['description'] ?? ''))),
                    'info' => ($year ? $year.' - ' : '').$label,
                    'lien' => 'https://anilist.co/'.strtolower($mediaType === 'ANIME' ? 'anime' : 'manga').'/'.(int) ($item['id'] ?? 0),
                    'total_episodes' => $mediaType === 'ANIME' ? (int) ($item['episodes'] ?? 0) : (int) ($item['chapters'] ?? 0),
                    'total_saisons' => $mediaType === 'MANGA' ? (int) ($item['volumes'] ?? 0) : '',
                    'seasons_data' => null,
                    'source' => 'AniList',
                    'sourcePriority' => $mediaType === 'MANGA' ? 110 : 95,
                    'externalId' => $item['id'] ?? null,
                    'mediaType' => strtolower($mediaType),
                    'providers' => [],
                    'score' => 0,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            log_message('warning', 'AniList indisponible: '.$e->getMessage());
            return [];
        }
    }

    private function searchMangaDex(string $query, bool $animeMode = false, int $limit = 5): array
    {
        try {
            $response = $this->client->get('https://api.mangadex.org/manga', [
                'query' => [
                    'title' => $query,
                    'limit' => $limit,
                    'includes[]' => 'cover_art',
                    'order[relevance]' => 'desc',
                    'contentRating[]' => ['safe', 'suggestive'],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Summury/1.0',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $body = json_decode((string) $response->getBody(), true);
            $results = [];

            foreach ($body['data'] ?? [] as $manga) {
                $attr = $manga['attributes'] ?? [];
                $titles = $attr['title'] ?? [];
                $title = (string) (
                    $titles['fr']
                    ?? $titles['en']
                    ?? $titles['ja-ro']
                    ?? reset($titles)
                    ?? 'Inconnu'
                );

                if (is_array($title)) {
                    $title = (string) reset($title);
                }

                $fileName = '';
                foreach ($manga['relationships'] ?? [] as $relationship) {
                    if (($relationship['type'] ?? '') === 'cover_art' && !empty($relationship['attributes']['fileName'])) {
                        $fileName = (string) $relationship['attributes']['fileName'];
                        break;
                    }
                }

                // Aucun fallback générique : une couverture absente ne doit pas polluer les résultats.
                if ($fileName === '') {
                    continue;
                }

                $id = (string) ($manga['id'] ?? '');
                $imageThumb = 'https://uploads.mangadex.org/covers/'.$id.'/'.$fileName.'.256.jpg';
                $imageLarge = 'https://uploads.mangadex.org/covers/'.$id.'/'.$fileName.'.512.jpg';
                $description = $attr['description']['fr'] ?? $attr['description']['en'] ?? '';
                $year = (string) ($attr['year'] ?? '');

                $results[] = [
                    'titre' => $title,
                    'imageThumb' => $imageThumb,
                    'imageLarge' => $imageLarge,
                    'description' => trim(strip_tags((string) $description)),
                    'info' => ($year ? $year.' - ' : '').($animeMode ? 'MANGA / ANIME' : 'MANGA'),
                    'lien' => 'https://mangadex.org/title/'.$id,
                    'total_episodes' => $attr['lastChapter'] ?? '',
                    'total_saisons' => $attr['lastVolume'] ?? '',
                    'seasons_data' => null,
                    'source' => 'MangaDex',
                    'sourcePriority' => $animeMode ? 65 : 90,
                    'externalId' => $id,
                    'mediaType' => 'manga',
                    'providers' => [],
                    'score' => 0,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            log_message('warning', 'MangaDex indisponible: '.$e->getMessage());
            return [];
        }
    }

    private function searchYouTube(string $query, int $limit = 8): array
    {
        $apiKey = trim((string) env('YOUTUBE_API_KEY'));
        if ($apiKey === '') {
            return [];
        }

        try {
            $response = $this->client->get('https://www.googleapis.com/youtube/v3/search', [
                'query' => [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => $limit,
                    'regionCode' => 'FR',
                    'relevanceLanguage' => 'fr',
                    'safeSearch' => 'moderate',
                    'videoEmbeddable' => 'true',
                    'key' => $apiKey,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $body = json_decode((string) $response->getBody(), true);
            $results = [];

            foreach ($body['items'] ?? [] as $item) {
                $videoId = (string) ($item['id']['videoId'] ?? '');
                $snippet = $item['snippet'] ?? [];
                if ($videoId === '' || empty($snippet['title'])) {
                    continue;
                }

                $thumbs = $snippet['thumbnails'] ?? [];
                $image = $thumbs['high']['url'] ?? $thumbs['medium']['url'] ?? $thumbs['default']['url'] ?? '';

                $results[] = [
                    'titre' => html_entity_decode((string) $snippet['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'imageThumb' => $image,
                    'imageLarge' => $image,
                    'description' => html_entity_decode((string) ($snippet['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'info' => 'VIDÉO - '.((string) ($snippet['channelTitle'] ?? 'YouTube')),
                    'lien' => 'https://www.youtube.com/watch?v='.$videoId,
                    'total_episodes' => '',
                    'total_saisons' => '',
                    'seasons_data' => null,
                    'source' => 'YouTube',
                    'sourcePriority' => 100,
                    'externalId' => $videoId,
                    'mediaType' => 'video',
                    'providers' => [],
                    'score' => 0,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            log_message('warning', 'YouTube indisponible: '.$e->getMessage());
            return [];
        }
    }

    private function searchStreaming(string $query, int $limit = 8): array
    {
        $movies = $this->searchTmdbMovies($query, 5);
        $series = $this->searchTmdbSeries($query, 5);
        $results = array_merge($movies, $series);

        foreach ($results as &$result) {
            $id = (int) ($result['externalId'] ?? 0);
            $mediaType = $result['mediaType'] ?? '';
            if ($id <= 0 || !in_array($mediaType, ['movie', 'tv'], true)) {
                continue;
            }

            $path = $mediaType === 'movie'
                ? '/movie/'.$id.'/watch/providers'
                : '/tv/'.$id.'/watch/providers';

            $providersBody = $this->tmdbGet($path, []);
            $fr = $providersBody['results']['FR'] ?? [];
            $providers = [];

            foreach (['flatrate', 'free', 'ads', 'rent', 'buy'] as $group) {
                foreach ($fr[$group] ?? [] as $provider) {
                    $name = trim((string) ($provider['provider_name'] ?? ''));
                    if ($name !== '' && !in_array($name, $providers, true)) {
                        $providers[] = $name;
                    }
                }
            }

            $result['providers'] = $providers;
            $result['lien'] = (string) ($fr['link'] ?? '');
            if ($providers) {
                $result['info'] .= ' • '.implode(', ', array_slice($providers, 0, 4));
            }
        }
        unset($result);

        return array_values(array_filter($results, static fn ($r) => !empty($r['providers'])));
    }

    private function prepareResults(array $results, string $query, string $type): array
    {
        foreach ($results as &$result) {
            $result['score'] = $this->scoreTitle($query, (string) ($result['titre'] ?? ''));

            // Bonus pour une source adaptée à la catégorie demandée.
            $result['score'] += match ($type) {
                'film', 'serie' => (($result['source'] ?? '') === 'TMDB' ? 10 : 0),
                'anime' => (($result['source'] ?? '') === 'TMDB' ? 12 : (($result['source'] ?? '') === 'AniList' ? 10 : 0)),
                'manga' => (($result['source'] ?? '') === 'AniList' ? 12 : (($result['source'] ?? '') === 'MangaDex' ? 9 : 0)),
                'video' => (($result['source'] ?? '') === 'YouTube' ? 10 : 0),
                'streaming' => (($result['source'] ?? '') === 'TMDB' ? 10 : 0),
                default => 0,
            };

            if (!empty($result['imageLarge'])) {
                $result['score'] += 5;
            }

            $result['score'] = max(0, min(100, (int) round($result['score'])));
        }
        unset($result);

        // Déduplication : le même titre provenant de plusieurs APIs devient une seule carte.
        $deduped = [];
        foreach ($results as $result) {
            if (empty($result['titre'])) {
                continue;
            }

            // On retire les titres très courts qui donnent beaucoup de faux positifs.
            if (mb_strlen(trim((string) $result['titre'])) < 2) {
                continue;
            }

            $key = $this->normalizeTitle((string) $result['titre']);
            $year = preg_match('/\b(19|20)\d{2}\b/', (string) ($result['info'] ?? ''), $matches) ? $matches[0] : '';
            $dedupeKey = $key.'|'.$year;

            if (!isset($deduped[$dedupeKey])) {
                $result['sources'] = [$result['source'] ?? 'API'];
                $deduped[$dedupeKey] = $result;
                continue;
            }

            $existing = $deduped[$dedupeKey];
            $existingSources = $existing['sources'] ?? [$existing['source'] ?? 'API'];
            $newSource = $result['source'] ?? 'API';
            if (!in_array($newSource, $existingSources, true)) {
                $existingSources[] = $newSource;
            }

            // On garde la meilleure image selon la priorité de source, pas simplement le dernier résultat.
            if (($result['sourcePriority'] ?? 0) > ($existing['sourcePriority'] ?? 0)) {
                $result['sources'] = $existingSources;
                if (empty($result['description']) && !empty($existing['description'])) {
                    $result['description'] = $existing['description'];
                }
                if (empty($result['seasons_data']) && !empty($existing['seasons_data'])) {
                    $result['seasons_data'] = $existing['seasons_data'];
                }
                $deduped[$dedupeKey] = $result;
            } else {
                if (empty($existing['description']) && !empty($result['description'])) {
                    $existing['description'] = $result['description'];
                }
                if (empty($existing['seasons_data']) && !empty($result['seasons_data'])) {
                    $existing['seasons_data'] = $result['seasons_data'];
                }
                if (empty($existing['lien']) && !empty($result['lien'])) {
                    $existing['lien'] = $result['lien'];
                }
                $existing['score'] = max((int) $existing['score'], (int) $result['score']);
                $existing['sources'] = $existingSources;
                $deduped[$dedupeKey] = $existing;
            }
        }

        $results = array_values($deduped);

        usort($results, static function (array $a, array $b): int {
            $scoreCompare = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }
            return (($b['sourcePriority'] ?? 0) <=> ($a['sourcePriority'] ?? 0));
        });

        // Pas de résultat faible : on préfère peu de bons résultats à une liste de faux positifs.
        $results = array_values(array_filter($results, static fn (array $r): bool => ($r['score'] ?? 0) >= 42));

        foreach ($results as &$result) {
            $result['sourcesLabel'] = implode(' + ', $result['sources'] ?? [$result['source'] ?? 'API']);
            unset($result['sourcePriority'], $result['externalId'], $result['sources']);
        }
        unset($result);

        return array_slice($results, 0, 8);
    }

    private function scoreTitle(string $query, string $title): int
    {
        $q = $this->normalizeTitle($query);
        $t = $this->normalizeTitle($title);

        if ($q === '' || $t === '') {
            return 0;
        }
        if ($q === $t) {
            return 90;
        }
        if (str_contains($t, $q)) {
            return 78;
        }
        if (str_contains($q, $t)) {
            return 72;
        }

        similar_text($q, $t, $percent);
        $score = (int) round($percent * 0.72);

        $qTokens = array_values(array_filter(explode(' ', $q), static fn ($v) => mb_strlen($v) >= 2));
        $tTokens = array_values(array_filter(explode(' ', $t), static fn ($v) => mb_strlen($v) >= 2));
        if ($qTokens && $tTokens) {
            $intersection = count(array_intersect($qTokens, $tTokens));
            $score += (int) round(($intersection / count($qTokens)) * 25);
        }

        return min(90, $score);
    }

    private function normalizeTitle(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? $value;
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function tmdbGet(string $path, array $query = []): array
    {
        if ($this->tmdbApiKey === '') {
            return [];
        }

        $query['api_key'] = $this->tmdbApiKey;
        $query['language'] = $query['language'] ?? 'fr-FR';

        try {
            $response = $this->client->get('https://api.themoviedb.org/3'.$path, ['query' => $query]);
            if ($response->getStatusCode() !== 200) {
                return [];
            }
            return json_decode((string) $response->getBody(), true) ?: [];
        } catch (\Throwable $e) {
            log_message('warning', 'TMDB indisponible: '.$e->getMessage());
            return [];
        }
    }

    private function scrapeOpenGraph(string $url): ?array
    {
        try {
            $response = $this->client->get($url, [
                'headers' => ['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8'],
            ]);
            if ($response->getStatusCode() >= 400) {
                return null;
            }

            $html = (string) $response->getBody();
            if ($html === '') {
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
                $property = $tag->getAttribute('property');
                $name = $tag->getAttribute('name');
                $content = $tag->getAttribute('content');

                if ($property === 'og:title' || $name === 'twitter:title') {
                    $data['titre'] = $content;
                } elseif ($property === 'og:description' || $name === 'description') {
                    $data['description'] = $content;
                } elseif ($property === 'og:image' || $name === 'twitter:image') {
                    $data['image'] = $content;
                }
            }

            if ($data['titre'] === '') {
                $titles = $doc->getElementsByTagName('title');
                if ($titles->length > 0) {
                    $data['titre'] = trim((string) $titles->item(0)->nodeValue);
                }
            }

            return $data;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
