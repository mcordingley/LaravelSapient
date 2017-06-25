<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\KeyResolver\StaticResolver;
use MCordingley\LaravelSapient\Middleware\SealResponse;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Simple;

final class SealResponseTest extends TestCase
{
    public function testGoodKey()
    {
        $pair = sodium_crypto_box_keypair();
        $public = sodium_crypto_box_publickey($pair);
        $private = new SealingSecretKey(sodium_crypto_box_secretkey($pair));

        $middleware = new SealResponse(new StaticResolver($public));

        $request = static::createRequest();
        $unsealed = $request->getContent();

        $response = $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        static::assertEquals($unsealed, Simple::unseal($response->getContent(), $private));
    }
}
