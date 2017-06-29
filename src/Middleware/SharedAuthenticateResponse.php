<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SharedAuthenticationKey;
use ParagonIE\Sapient\Sapient;
use Symfony\Component\HttpFoundation\Response;

final class SharedAuthenticateResponse
{
    /** @var SharedAuthenticationKey */
    private $key;

    /**
     * @param SharedAuthenticationKey $key
     */
    public function __construct(SharedAuthenticationKey $key)
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
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set(
            Sapient::HEADER_AUTH_NAME,
            Base64UrlSafe::encode(sodium_crypto_auth($response->getContent(), $this->key->getString(true)))
        );

        return $response;
    }
}
