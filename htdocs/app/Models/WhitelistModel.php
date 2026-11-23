<?php

namespace App\Models;

use CodeIgniter\Model;

class WhitelistModel extends Model
{
    protected $table            = 'whitelist';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['domain', 'created_at'];
    protected $useTimestamps    = false;

    /**
     * Récupère tous les domaines en liste blanche avec le nombre de cartes actives associées.
     */
    public function getAllWithItemCount(): array
    {
        $entries = $this->orderBy('created_at', 'DESC')->findAll();
        $db      = \Config\Database::connect();

        foreach ($entries as &$entry) {
            $cleanDomain = preg_replace('#^https?://#i', '', trim((string) $entry['domain']));
            $cleanDomain = rtrim($cleanDomain, '/');

            $entry['clean_domain'] = $cleanDomain;
            $entry['items_count']  = $db->table('item')
                ->where('deleted_at', null)
                ->like('lien', $cleanDomain, 'both')
                ->countAllResults();
        }

        return $entries;
    }

    /**
     * Supprime une entrée par domaine normalisé (avec ou sans schéma).
     */
    public function removeByDomain(string $domain): bool
    {
        $clean = preg_replace('#^https?://#i', '', trim($domain));
        $clean = rtrim($clean, '/');

        return (bool) $this->groupStart()
                ->where('domain', $domain)
                ->orWhere('domain', $clean)
                ->orWhere('domain', 'https://' . $clean)
                ->orWhere('domain', 'http://' . $clean)
            ->groupEnd()
            ->delete();
    }
}