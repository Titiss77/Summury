<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AntiInspectFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // On cible uniquement les pages HTML
        if (str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            $html = (string) $response->getBody();
            
            // Script d'obfuscation et de blocage
            $antiDebugJs = "<script>
                document.addEventListener('contextmenu', e => e.preventDefault());
                document.onkeydown = function(e) {
                    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) || (e.ctrlKey && e.key === 'U')) {
                        return false;
                    }
                };
                setInterval(function() { debugger; }, 100);
            </script></body>";

            // Injection silencieuse juste avant la fermeture du body
            $obfuscatedHtml = str_ireplace('</body>', $antiDebugJs, $html);
            $response->setBody($obfuscatedHtml);
        }
    }
}