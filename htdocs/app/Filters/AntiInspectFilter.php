<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AntiInspectFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Filtre neutralisé pour respecter les bonnes pratiques de cybersécurité (OWASP).
        // L'obscurcissement du code client (blocage clic droit / F12) est inefficace 
        // face aux attaquants et pénalise l'accessibilité des utilisateurs légitimes.
    }
}