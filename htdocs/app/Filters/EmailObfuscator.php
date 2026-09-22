<?php declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EmailObfuscator implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        if (str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            $html = (string) $response->getBody();

            // Recherche automatique d'emails via Regex
            $html = preg_replace_callback(
                '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
                function ($matches) {
                    $email = $matches[0];
                    $obfuscated = '';
                    // Convertit chaque caractère en entité HTML (ex: a -> &#97;)
                    for ($i = 0; $i < strlen($email); $i++) {
                        $obfuscated .= '&#' . ord($email[$i]) . ';';
                    }
                    return $obfuscated;
                },
                $html
            );

            $response->setBody($html);
        }
    }
}