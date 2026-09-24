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
    $contentType = $response->getHeaderLine('Content-Type');
    if (empty($contentType) && method_exists($response, 'getContentType')) {
        $contentType = $response->getContentType();
    }

    if (str_contains($contentType ?: 'text/html', 'text/html')) {
        $html = (string) $response->getBody();

        $search = [
            '/\>[^\S ]+/s',      
            '/[^\S ]+\</s',      
            '/(\s)+/s',          
            '/<!--(.*?)-->/s',   // Corrigé : remplace l'ancienne regex invalide '//s'
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            '',
        ];

        $minifiedHtml = preg_replace($search, $replace, $html);

        // Corrigé : on injecte le résultat minifié (avec un fallback de sécurité)
        $response->setBody($minifiedHtml ?? $html);
    }
}
}