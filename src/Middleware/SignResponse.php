<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;
use Symfony\Component\HttpFoundation\Response;

final class SignResponse
{
    /** @var SigningSecretKey */
    private $key;

    /**
     * @param SigningSecretKey $key
     */
    public function __construct(SigningSecretKey $key)
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
            'Body-Signature-Ed25519',
            $this->encode(sodium_crypto_sign_detached($response->getContent(), $this->key->getString(true)))
        );

        return $response;
    }
}
