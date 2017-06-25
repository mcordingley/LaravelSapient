<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\KeyResolver\StaticResolver;
use MCordingley\LaravelSapient\Middleware\VerifyRequest;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class VerifyRequestTest extends TestCase
{
    public function testGoodSignature()
    {
        $pair = sodium_crypto_sign_keypair();
        $public = Base64UrlSafe::encode(sodium_crypto_sign_publickey($pair));
        $private = sodium_crypto_sign_secretkey($pair);

        $middleware = new VerifyRequest(new StaticResolver($public));

        $request = static::createRequest();

        $request->headers->set(
            'Body-Signature-Ed25519',
            Base64UrlSafe::encode(sodium_crypto_sign_detached($request->getContent(), $private))
        );

        $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        // Getting here means no exception was thrown, which is the definition of success.
        static::assertTrue(true);
    }

    public function testBadSignature()
    {
        $pair = sodium_crypto_sign_keypair();
        $public = Base64UrlSafe::encode(sodium_crypto_sign_publickey($pair));

        $otherPair = sodium_crypto_sign_keypair();
        $private = sodium_crypto_sign_secretkey($otherPair);

        $middleware = new VerifyRequest(new StaticResolver($public));

        $request = static::createRequest();

        $request->headers->set(
            'Body-Signature-Ed25519',
            Base64UrlSafe::encode(sodium_crypto_sign_detached($request->getContent(), $private))
        );

        static::expectException(HttpException::class);

        $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });
    }
}
