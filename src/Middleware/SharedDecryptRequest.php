<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use MCordingley\LaravelSapient\ReplacesRequests;
use ParagonIE\Sapient\CryptographyKeys\SharedEncryptionKey;
use ParagonIE\Sapient\Simple;
use Symfony\Component\HttpFoundation\Response;

final class SharedDecryptRequest
{
    use ReplacesRequests;

    /** @var SharedEncryptionKey */
    private $key;

    /**
     * @param SharedEncryptionKey $key
     */
    public function __construct(SharedEncryptionKey $key)
    {
        $this->key = $key;
    }


    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['HEAD', 'GET', 'OPTIONS'])) {
            $request = $this->replaceRequest($request, Simple::decrypt($request->getContent(), $this->key));
        }

        return $next($request);
    }
}
