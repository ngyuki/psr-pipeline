<?php /** @noinspection PhpUnusedParameterInspection */

namespace ngyuki\PsrPipelineTests;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use ngyuki\PsrPipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
            return function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($name, &$paths) {
                $paths[] = [$name, $request->getUri()->getPath()];
                return $handler->handle($request);
            };
        };

        $pipeline = new Pipeline();

        $pipeline->pipe($create('top'));

        $pipeline->path('/aaa', $create('aaa'));
        $pipeline->path('/aaa/bbb', $create('aaa:bbb'));
        $pipeline->pipe($create('end'));

        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return new Response();
        });

        $request = new ServerRequest([], [], $path, 'GET');
        $pipeline->handle($request);
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
