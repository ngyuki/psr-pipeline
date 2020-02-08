<?php /** @noinspection PhpUnusedParameterInspection */

namespace ngyuki\PsrPipelineTests;

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequestFactory;
use ngyuki\PsrPipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PipelineTest extends TestCase
{
    public function test()
    {
        $pipeline = new Pipeline();

        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'A');
            $response = $handler->handle($request);
            return new TextResponse($response->getBody()->getContents() . 'a');
        });

        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'B');
            $response = $handler->handle($request);
            return new TextResponse($response->getBody()->getContents() . 'b');
        });

        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withAttribute('val', $request->getAttribute('val') . 'C');
            $val = $request->getAttribute('val');
            $response = new TextResponse($val);
            return new TextResponse($response->getBody()->getContents() . 'c');
        });

        $request = ServerRequestFactory::fromGlobals();
        $response = $pipeline->handle($request);

        $this->assertEquals('ABCcba', $response->getBody()->getContents());
    }

    /**
     * @test
     */
    public function pipeline_chain()
    {
        $create = function ($name){
            return function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($name) {
                $request = $request->withAttribute('val', $request->getAttribute('val') . $name);
                return $handler->handle($request);
            };
        };

        $pipeline = new Pipeline();

        $pipeline->pipe($create('A'));

        // ミドルウェアの中でパイプラインを生成してチェイン
        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($create) {
            $pipeline = new Pipeline();
            $pipeline->pipe($create('B'));
            $pipeline->pipe($create('C'));
            return $pipeline->process($request, $handler);
        });

        // パイプラインをミドルウェアとして追加
        $pipeline->pipe((function () use ($create) {
            $pipeline = new Pipeline();
            $pipeline->pipe($create('D'));
            $pipeline->pipe($create('E'));
            return $pipeline;
        })());

        $pipeline->pipe($create('F'));

        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return new TextResponse($request->getAttribute('val'));
        });

        $request = ServerRequestFactory::fromGlobals();
        $response = $pipeline->handle($request);

        $this->assertEquals('ABCDEF', $response->getBody()->getContents());
    }
}
