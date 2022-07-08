<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;

class AddContentLength
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // to be sure nothing was already output (by an echo statement or something)
        if (headers_sent() || ob_get_contents() !== '') {
            return $response;
        }

        // For now, we don't compress anything
        ini_set('zlib.output_compression', '0');

        $content = $response->content();
        $contentLength = strlen($content);
//        $useCompressedOutput = ($contentLength && isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
//            str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'));
//
//        if ($useCompressedOutput) {
//            // In order to accurately set Content-Length, we have to compress the data ourselves
//            // rather than letting PHP do it automatically.
//            $compressedContent = gzencode($content, 9, FORCE_GZIP);
//            $compressedContentLength = strlen($compressedContent);
//            if ($compressedContentLength / $contentLength < 0.9) {
//                if (ini_get('zlib.output_compression')) {
//                    ini_set('zlib.output_compression', 'false');
//                }
//                $response->header('Content-Encoding', 'gzip');
//                $response->setContent($compressedContent);
//                $contentLength = $compressedContentLength;
//            }
//        }

        // compressed or not, sets the Content-Length
        $response->header('Content-Length', $contentLength);

        return $response;
    }
}
