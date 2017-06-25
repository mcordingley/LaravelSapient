<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use MCordingley\LaravelSapient\ReplacesRequests;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Simple;
use Symfony\Component\HttpFoundation\Response;

final class UnsealRequest
{
    use ReplacesRequests;

    /** @var SealingSecretKey */
    private $key;

    /**
     * @param SealingSecretKey $key
     */
    public function __construct(SealingSecretKey $key)
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
            $request = $this->replaceRequest($request, Simple::unseal($request->getContent(), $this->key));
        }

        return $next($request);
    }
}
