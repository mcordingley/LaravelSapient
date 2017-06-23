<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use Illuminate\Http\Request;
use MCordingley\LaravelSapient\Contracts\KeyResolver;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\HttpFoundation\Response;

final class VerifyRequest
{
    /** @var KeyResolver */
    private $resolver;

    /**
     * @param KeyResolver $resolver
     */
    public function __construct(KeyResolver $resolver)
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
        $signature = $request->headers->get('Body-Signature-Ed25519');
        $key = new SigningPublicKey(Base64UrlSafe::decode($this->resolver->resolveKey()));

        if (!sodium_crypto_sign_verify_detached($this->decode($signature), $request->getContent(), $key->getString(true))) {
            abort(403, 'Invalid Sapient signature detected.');
        }

        return $next($request);
    }
}
