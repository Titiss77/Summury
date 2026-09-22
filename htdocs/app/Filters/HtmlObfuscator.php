<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class HtmlObfuscator implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Rien à faire avant
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // On s'assure de ne traiter que le HTML (pas les requêtes JSON ou les fichiers)
        if (str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            $html = (string) $response->getBody();
            
            // Encode tout le HTML généré en Base64
            $encodedHtml = base64_encode($html);
            
            // Remplace le corps de la réponse par un script de décodage
            $obfuscatedBody = '<!DOCTYPE html><html><head><title>Chargement...</title></head><body>';
            $obfuscatedBody .= '<script>';
            $obfuscatedBody .= 'document.write(decodeURIComponent(escape(window.atob("' . $encodedHtml . '"))));';
            $obfuscatedBody .= '</script>';
            $obfuscatedBody .= '<noscript>Veuillez activer JavaScript pour voir ce site.</noscript>';
            $obfuscatedBody .= '</body></html>';
            
            $response->setBody($obfuscatedBody);
        }
    }
}