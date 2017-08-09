<?php
namespace ngyuki\PsrPipelineTests;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\TextResponse;

use ngyuki\PsrPipeline\Pipeline;

class PipelineTest extends TestCase
{
    public function test()
    {
        $pipeline = new Pipeline();

        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'A');
            $response = $delegate->process($request);
            return new TextResponse($response->getBody()->getContents() . 'a');
        });

        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'B');
            $response = $delegate->process($request);
            return new TextResponse($response->getBody()->getContents() . 'b');
        });

        /** @noinspection PhpUnusedParameterInspection */
        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'C');
            $val = $request->getAttribute('val');
            $response = new TextResponse($val);
            return new TextResponse($response->getBody()->getContents() . 'c');
        });

        $request = ServerRequestFactory::fromGlobals();
        $response = $pipeline->run($request);

        $this->assertEquals('ABCcba', $response->getBody()->getContents());
    }
}
