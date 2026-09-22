<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class HeaderCloaker implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Supprime l'en-tête natif de PHP s'il est présent
        $response->removeHeader('X-Powered-By');
        
        // Fausse la signature du serveur
        $response->setHeader('Server', 'Enigma/1.0');
        
        // Bloque l'inclusion de ton site dans des iframes externes (Clickjacking)
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        
        // Empêche le navigateur de deviner le type de contenu (MIME Sniffing)
        $response->setHeader('X-Content-Type-Options', 'nosniff');
    }
}