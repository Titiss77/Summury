<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EmailObfuscator implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void {}

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

            // Recherche automatique d'emails via Regex
            $html = preg_replace_callback(
                '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
                function ($matches) {
                    $email = $matches[0];
                    $obfuscated = '';
                    // Convertit chaque caractère en entité HTML (ex: a -> &#97;)
                    for ($i = 0; $i < strlen($email); ++$i) {
                        $obfuscated .= '&#'.ord($email[$i]).';';
                    }

                    return $obfuscated;
                },
                $html
            );

            $response->setBody($html);
        }
    }
}
