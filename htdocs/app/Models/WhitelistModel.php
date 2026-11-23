<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class WhitelistModel extends Model
{
    public const PROTECTION_DAYS = 3;

    protected $table            = 'whitelist';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['domain', 'created_at'];
    protected $useTimestamps    = false;

    /**
     * Retourne tous les domaines déclarés comme valides, du plus récent au plus ancien.
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Ajoute ou renouvelle la date de validation d'un domaine dans la whitelist.
     */
    public function upsertDomain(string $host): bool
    {
        $now      = date('Y-m-d H:i:s');
        $existing = $this->where('domain', $host)->first();

        if ($existing) {
            return (bool) $this->update($existing['id'], ['created_at' => $now]);
        }

        return (bool) $this->insert([
            'domain'     => $host,
            'created_at' => $now,
        ]);
    }

    /**
     * Indique si le délai de protection de 7 jours est toujours actif.
     */
    public function isProtectionActive(?string $createdAt): bool
    {
        if (empty($createdAt)) {
            return false;
        }

        $timestamp = strtotime($createdAt);
        if ($timestamp === false) {
            return false;
        }

        return ($timestamp + (self::PROTECTION_DAYS * 86400)) > time();
    }

    /**
     * Calcule la date de fin de protection (created_at + 7 jours).
     */
    public function getProtectionExpiresAt(?string $createdAt): ?string
    {
        if (empty($createdAt)) {
            return null;
        }

        $timestamp = strtotime($createdAt);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp + (self::PROTECTION_DAYS * 86400));
    }
}