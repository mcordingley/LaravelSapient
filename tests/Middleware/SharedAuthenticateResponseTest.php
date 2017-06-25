<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\Middleware\SharedAuthenticateResponse;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SharedAuthenticationKey;

final class SharedAuthenticateResponseTest extends TestCase
{
    public function testSignature()
    {
        $key = random_bytes(SODIUM_CRYPTO_AUTH_KEYBYTES);
        $wrapped = new SharedAuthenticationKey($key);

        $middleware = new SharedAuthenticateResponse($wrapped);

        $request = static::createRequest();

        $response = $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        static::assertTrue(
            sodium_crypto_auth_verify(
                Base64UrlSafe::decode($response->headers->get('Body-HMAC-SHA512256')),
                $response->getContent(),
                $key
            )
        );
    }
}
