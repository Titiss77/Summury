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

            // Protège les blocs sensibles aux retours à la ligne et espaces
            $preserved = [];
            $html = preg_replace_callback(
                '#<(script|style|textarea|pre)\b[^>]*>.*?</\1>#is',
                static function (array $matches) use (&$preserved): string {
                    $key = '___MINIFY_PRESERVE_' . count($preserved) . '___';
                    $preserved[$key] = $matches[0];

                    return $key;
                },
                $html
            ) ?? $html;

            $search = [
                '/\>[^\S ]+/s',
                '/[^\S ]+\</s',
                '/(\s)+/s',
                '/<!--(.*?)-->/s',
            ];

            $replace = [
                '>',
                '<',
                '\1',
                '',
            ];

            $minifiedHtml = preg_replace($search, $replace, $html) ?? $html;

            if ($preserved !== []) {
                $minifiedHtml = strtr($minifiedHtml, $preserved);
            }

            $response->setBody($minifiedHtml);
        }
    }
}
