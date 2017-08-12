<?php
namespace ngyuki\PsrPipelineTests;

use PHPUnit\Framework\TestCase;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

use ngyuki\PsrPipeline\Pipeline;

class PathSpecificMiddlewareTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $path
     * @param array $expected
     */
    public function test($path, $expected)
    {
        $paths = [];

        $create = function ($name) use (&$paths) {
            return function (ServerRequestInterface $request, DelegateInterface $delegate) use ($name, &$paths) {
                $paths[] = [$name, $request->getUri()->getPath()];
                return $delegate->process($request);
            };
        };

        $pipeline = new Pipeline();

        $pipeline->pipe($create('top'));

        $pipeline->pipe('/aaa', $create('aaa'));
        $pipeline->pipe('/aaa/bbb', $create('aaa:bbb'));
        $pipeline->pipe($create('end'));

        /** @noinspection PhpUnusedParameterInspection */
        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            return new Response();
        });

        $request = new ServerRequest([], [], $path, 'GET');
        $pipeline->run($request);
        $this->assertEquals($expected, $paths);
    }

    public function dataProvider()
    {
        $data = [
            '/' => [
                ['top', '/'],
                ['end', '/'],
            ],
            '/aaa' => [
                ['top', '/aaa'],
                ['aaa', '/'],
                ['end', '/aaa'],
            ],
            '/aaa/' => [
                ['top', '/aaa/'],
                ['aaa', '/'],
                ['end', '/aaa/'],
            ],
            '/aaa/bbb' => [
                ['top', '/aaa/bbb'],
                ['aaa', '/bbb'],
                ['aaa:bbb', '/'],
                ['end', '/aaa/bbb'],
            ],
            '/aaaa' => [
                ['top', '/aaaa'],
                ['end', '/aaaa'],
            ],
        ];

        return array_reduce(array_keys($data), function ($r, $key) use ($data) {
            $r[$key] = [$key, $data[$key]];
            return $r;
        }, []);
    }
}
