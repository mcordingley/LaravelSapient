<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\Middleware\SignResponse;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SigningSecretKey;

final class SignResponseTest extends TestCase
{
    public function testSignature()
    {
        $pair = sodium_crypto_sign_keypair();
        $public = sodium_crypto_sign_publickey($pair);
        $private = new SigningSecretKey(sodium_crypto_sign_secretkey($pair));

        $middleware = new SignResponse($private);

        $request = static::createRequest();

        $response = $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        static::assertTrue(
            sodium_crypto_sign_verify_detached(
                Base64UrlSafe::decode($response->headers->get('Body-Signature-Ed25519')),
                $response->getContent(),
                $public
            )
        );
    }
}
