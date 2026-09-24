<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class HtmlMinifier implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
        // Rien à faire avant l'exécution du contrôleur
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // 1. On récupère le header s'il a été forcé, sinon on interroge la méthode native de CI4, sinon on assume du HTML par défaut
        $contentType = $response->getHeaderLine('Content-Type');
        if (empty($contentType) && method_exists($response, 'getContentType')) {
            $contentType = $response->getContentType();
        }

        // 2. On effectue la vérification sur la variable corrigée
        if (str_contains($contentType ?: 'text/html', 'text/html')) {
            $html = (string) $response->getBody();

            // Expressions régulières pour nettoyer le code
            $search = [
                '/\>[^\S ]+/s',      // Supprime les espaces après les balises
                '/[^\S ]+\</s',      // Supprime les espaces avant les balises
                '/(\s)+/s',          // Réduit les multiples espaces en un seul
                '//s',    // Supprime les commentaires HTML (sauf conditions IE si besoin)
            ];

            $replace = [
                '>',
                '<',
                '\1',
                '',
            ];

            $minifiedHtml = preg_replace($search, $replace, $html);

            // On remplace le corps de la réponse par le HTML minifié
            $response->setBody($html);
        }
    }
}
