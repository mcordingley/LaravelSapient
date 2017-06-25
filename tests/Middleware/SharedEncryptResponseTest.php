<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\Middleware\SharedEncryptResponse;
use ParagonIE\Sapient\CryptographyKeys\SharedEncryptionKey;
use ParagonIE\Sapient\Simple;

final class SharedEncryptResponseTest extends TestCase
{
    public function testGoodKey()
    {
        $key = new SharedEncryptionKey(random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES));
        $middleware = new SharedEncryptResponse($key);

        $request = static::createRequest();
        $unsealed = $request->getContent();

        $response = $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        static::assertEquals($unsealed, Simple::decrypt($response->getContent(), $key));
    }
}
