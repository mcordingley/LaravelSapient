<?php

namespace MCordingley\LaravelSapient;

use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Http\Request;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Zend\Diactoros\ServerRequest as DiactorosRequest;

trait ReplacesRequests
{
    /**
     * @param Request $request
     * @param string $newBody
     * @return Request
     */
    final protected function replaceRequest(Request $request, string $newBody): Request
    {
        $psrFactory = new DiactorosFactory;
        $symfonyFactory = new HttpFoundationFactory;

        /** @var DiactorosRequest $psrRequest */
        $psrRequest = $psrFactory->createRequest($request);
        $psrRequest = $psrRequest->withBody(stream_for($newBody));

        /** @var SymfonyRequest $symfonyRequest */
        $symfonyRequest = $symfonyFactory->createRequest($psrRequest);
        $symfonyRequest->headers->set('Content-Length', strlen($newBody));

        if (0 === strpos($symfonyRequest->headers->get('Content-Type'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($symfonyRequest->server->get('REQUEST_METHOD', 'GET')), ['DELETE', 'PATCH', 'POST', 'PUT'])
        ) {
            $parsed = [];
            parse_str($newBody, $parsed);
            $symfonyRequest->request = new ParameterBag($parsed);
        }

        $request = Request::createFromBase($symfonyRequest);

        app()->instance('request', $request);

        return $request;
    }
}