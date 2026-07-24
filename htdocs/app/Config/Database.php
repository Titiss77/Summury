<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Configuration de la Base de Données.
 */
class Database extends Config
{
    /**
     * Répertoire des migrations/seeds.
     */
    public string $filesPath = APPPATH.'Database'.DIRECTORY_SEPARATOR;

    /**
     * Groupe par défaut.
     * Sera défini dynamiquement dans le constructeur.
     */
    public string $defaultGroup = 'development';

    /**
     * Configuration DEVELOPMENT (vide, remplie par .env).
     */
    public array $development = [
        'DSN' => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    /**
     * Configuration PRODUCTION (vide, remplie par .env).
     */
    public array $production = [
        'DSN' => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // MAGIE ICI :
        // On force le groupe par défaut à être égal au nom de l'environnement.
        // Si CI_ENVIRONMENT = production, ça utilisera le groupe $production.
        // Si CI_ENVIRONMENT = development, ça utilisera le groupe $development.
        $this->defaultGroup = ENVIRONMENT;
    }
}
