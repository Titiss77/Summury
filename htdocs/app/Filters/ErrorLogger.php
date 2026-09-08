<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ErrorLogger implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Rien à faire avant l'exécution du contrôleur
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 1. Intercepter les erreurs de session simples (ex: with('error', '...'))
        if (session()->has('error')) {
            log_message('error', '[Erreur Application] ' . session('error') . ' | URL: ' . (string) $request->getUri());
        }

        // 2. Intercepter les erreurs de validation de formulaires (ex: with('errors', ...))
        if (session()->has('errors')) {
            $errors = session('errors');
            $errorString = is_array($errors) ? implode(' | ', $errors) : $errors;
            log_message('error', '[Erreur Validation] ' . $errorString . ' | URL: ' . (string) $request->getUri());
        }

        // 3. Intercepter les erreurs JSON (Requêtes Fetch / AJAX / check-dispo)
        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $body = json_decode((string) $response->getBody(), true);
            
            // Cherche la clé "error" dans le JSON
            if (is_array($body) && isset($body['error'])) {
                $errorMsg = is_array($body['error']) ? json_encode($body['error']) : $body['error'];
                log_message('error', '[Erreur JSON] ' . $errorMsg . ' | URL: ' . (string) $request->getUri());
            }
        }
    }
}