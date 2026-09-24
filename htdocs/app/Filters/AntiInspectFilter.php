<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AntiInspectFilter implements FilterInterface
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

            $antiDebugJs = "<script>
                // 1. Bloquer le clic droit
                document.addEventListener('contextmenu', e => e.preventDefault());

                // 2. Bloquer les raccourcis clavier (avec preventDefault)
                document.addEventListener('keydown', function(e) {
                    if (
                        e.key === 'F12' || e.keyCode === 123 ||
                        (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
                        (e.ctrlKey && e.key === 'U')
                    ) {
                        e.preventDefault();
                        return false;
                    }
                });

                // 3. Boucle anti-débogage agressive avec autodestruction
                setInterval(function() {
                    const start = performance.now();
                    debugger; // Met le navigateur en pause SI les DevTools sont ouverts
                    const end = performance.now();

                    // Si l'exécution a pris plus de 100ms, c'est que le debugger a figé la page
                    if (end - start > 100) {
                        document.body.innerHTML = '<h1 style=\"text-align:center; margin-top:20vh;\">Inspection non autorisée.</h1>';
                        window.location.replace('about:blank');
                    }
                }, 500);

                // 4. Nettoyage de la console
                setInterval(function() {
                    console.clear();
                    console.log('%cArrêtez-vous là.', 'color: red; font-size: 40px; font-weight: bold;');
                }, 1000);
            </script></body>";

            $obfuscatedHtml = str_ireplace('</body>', $antiDebugJs, $html);

            $response->setBody($html);
        }
    }
}
