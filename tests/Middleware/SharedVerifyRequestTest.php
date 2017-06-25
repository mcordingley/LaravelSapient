<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\Middleware\SharedVerifyRequest;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\Sapient\CryptographyKeys\SharedAuthenticationKey;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class SharedVerifyRequestTest extends TestCase
{
    public function testGoodSignature()
    {
        $key = random_bytes(SODIUM_CRYPTO_AUTH_KEYBYTES);
        $wrapped = new SharedAuthenticationKey($key);

        $middleware = new SharedVerifyRequest($wrapped);

        $request = static::createRequest();

        $request->headers->set(
            'Body-HMAC-SHA512256',
            Base64UrlSafe::encode(sodium_crypto_auth($request->getContent(), $key))
        );

        $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        // Getting here means no exception was thrown, which is the definition of success.
        static::assertTrue(true);
    }

    public function testBadSignature()
    {
        $wrapped = new SharedAuthenticationKey(random_bytes(SODIUM_CRYPTO_AUTH_KEYBYTES));

        $middleware = new SharedVerifyRequest($wrapped);

        $request = static::createRequest();

        $request->headers->set(
            'Body-HMAC-SHA512256',
            Base64UrlSafe::encode(sodium_crypto_auth($request->getContent(), random_bytes(SODIUM_CRYPTO_AUTH_KEYBYTES)))
        );

        static::expectException(HttpException::class);

        $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });
    }
}
