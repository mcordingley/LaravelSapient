<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Http\Request;
use ParagonIE\Sapient\CryptographyKeys\SealingSecretKey;
use ParagonIE\Sapient\Simple;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\ServerRequest as DiactorosRequest;

final class UnsealRequest
{
    /** @var DiactorosFactory */
    private $psrFactory;

    /** @var HttpFoundationFactory */
    private $symfonyFactory;

    /** @var SealingSecretKey */
    private $key;

    /**
     * @param SealingSecretKey $key
     */
    public function __construct(SealingSecretKey $key)
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
        if (!in_array($request->method(), ['HEAD', 'GET', 'OPTIONS'])) {
            $data = Simple::unseal($request->getContent(), $this->key);

            /** @var DiactorosRequest $psrRequest */
            $psrRequest = $this->psrFactory->createRequest($request);
            $psrRequest = $psrRequest->withBody(stream_for($data));

            $symfonyRequest = $this->symfonyFactory->createRequest($psrRequest);

            if (0 === strpos($symfonyRequest->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                && in_array(strtoupper($symfonyRequest->server->get('REQUEST_METHOD', 'GET')), ['DELETE', 'PATCH', 'POST', 'PUT'])
            ) {
                $parsed = [];
                parse_str($data, $parsed);
                $symfonyRequest->request = new ParameterBag($parsed);
            }

            $request = Request::createFromBase($symfonyRequest);

            app()->instance('request', $request);
        }

        return $next($request);
    }
}
