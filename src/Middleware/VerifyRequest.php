<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use MCordingley\LaravelSapient\KeyResolver\Resolver;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningPublicKey;
use Symfony\Component\HttpFoundation\Response;

final class VerifyRequest
{
    /** @var Resolver */
    private $resolver;

    /**
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
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

        $key = new SigningPublicKey(Base64UrlSafe::decode($this->resolver->resolveKey()));

        foreach ($request->headers->get('Body-Signature-Ed25519', null, false) as $signature) {
            if (sodium_crypto_sign_verify_detached(Base64UrlSafe::decode($signature), $request->getContent(), $key->getString(true))) {
                return $next($request);
            }
        }

        abort(403, 'Invalid Sapient signature detected.');
    }
}
