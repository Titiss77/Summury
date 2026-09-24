<?php declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur principal dont héritent tous les autres contrôleurs.
 * Permet d'initialiser les composants globaux de l'application.
 */
abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = [];

    /**
     * Initialisation du contrôleur (exécuté à chaque requête)
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }
}