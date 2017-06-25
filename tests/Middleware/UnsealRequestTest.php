<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Error;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MCordingley\LaravelSapient\Middleware\UnsealRequest;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Simple;

final class UnsealRequestTest extends TestCase
{
    public function testGoodKey()
    {
        $pair = sodium_crypto_box_keypair();
        $public = new SealingPublicKey(sodium_crypto_box_publickey($pair));
        $private = new SealingSecretKey(sodium_crypto_box_secretkey($pair));

        $middleware = new UnsealRequest($private);

        $unsealed = 'foo=1&joy=2&test=bar';
        $request = Request::create('/foo', 'POST', [], [], [], [], Simple::seal($unsealed, $public));

        $response = $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });

        static::assertEquals($unsealed, $response->getContent());
    }

    public function testBadKey()
    {
        $public = new SealingPublicKey(sodium_crypto_box_publickey(sodium_crypto_box_keypair()));
        $private = new SealingSecretKey(sodium_crypto_box_secretkey(sodium_crypto_box_keypair()));

        $middleware = new UnsealRequest($private);

        $unsealed = 'foo=1&joy=2&test=bar';
        $request = Request::create('/foo', 'POST', [], [], [], [], Simple::seal($unsealed, $public));

        static::expectException(Error::class);

        $middleware->handle($request, function (Request $request) {
            return new Response($request->getContent());
        });
    }
}
