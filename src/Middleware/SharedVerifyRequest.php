<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SharedAuthenticationKey;
use Symfony\Component\HttpFoundation\Response;

final class SharedVerifyRequest
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
        if (in_array($request->method(), ['HEAD', 'GET', 'OPTIONS'])) {
            return $next($request);
        }

        foreach ($request->headers->get('Body-HMAC-SHA512256', null, false) as $signature) {
            if (sodium_crypto_auth_verify(Base64UrlSafe::decode($signature), $request->getContent(), $this->key->getString(true))) {
                return $next($request);
            }
        }

        abort(403, 'Invalid Sapient signature detected.');
    }
}
