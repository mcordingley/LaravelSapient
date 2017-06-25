<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Http\Request;
use ParagonIE\Sapient\CryptographyKeys\SharedEncryptionKey;
use ParagonIE\Sapient\Simple;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\Response as DiactorosResponse;

final class SharedEncryptResponse
{
    /** @var DiactorosFactory */
    private $psrFactory;

    /** @var HttpFoundationFactory */
    private $symfonyFactory;

    /** @var SharedEncryptionKey */
    private $key;

    /**
     * @param SharedEncryptionKey $key
     */
    public function __construct(SharedEncryptionKey $key)
    {
        $this->psrFactory = new DiactorosFactory;
        $this->symfonyFactory = new HttpFoundationFactory;
        $this->key = $key;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        /** @var DiactorosResponse $psrResponse */
        $psrResponse = $this->psrFactory->createResponse($response);
        $cipherText = Simple::encrypt($psrResponse->getBody(), $this->key);

        $symfonyResponse = $this->symfonyFactory->createResponse($psrResponse->withBody(stream_for($cipherText)));
        $symfonyResponse->headers->set('Content-Length', strlen($cipherText));

        return $symfonyResponse;
    }
}
