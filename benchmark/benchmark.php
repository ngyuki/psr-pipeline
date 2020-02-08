<?php
/** @noinspection PhpUnused */
/** @noinspection PhpUnusedParameterInspection */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Stratigility\MiddlewarePipe;
use ngyuki\PsrPipeline\Pipeline;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function Laminas\Stratigility\middleware;
use function Laminas\Stratigility\path;


function stratigility(ServerRequestInterface $request)
{
    $pipeline = new MiddlewarePipe();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->pipe(middleware(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        }));
    }

    $pipeline->pipe(middleware(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return new TextResponse('x');
    }));

    return $pipeline->handle($request);
}

function my_pipeline(ServerRequestInterface $request)
{
    $pipeline = new Pipeline();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        });
    }

    $pipeline->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return new TextResponse('x');
    });

    return $pipeline->handle($request);
}

function stratigility_with_path(ServerRequestInterface $request)
{
    $pipeline = new MiddlewarePipe();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->pipe(path('/aaa', middleware((function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        }))));
    }

    $pipeline->pipe(path('/aaa', middleware((function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return new TextResponse('x');
    }))));

    return $pipeline->handle($request);
}

function my_pipeline_with_path(ServerRequestInterface $request)
{
    $pipeline = new Pipeline();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->path('/aaa', function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        });
    }

    $pipeline->path('/aaa', function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return new TextResponse('x');
    });

    return $pipeline->handle($request);
}

function benchmark($func)
{
    $request = ServerRequestFactory::fromGlobals();
    $request = $request->withUri($request->getUri()->withPath('/aaa'));

    $func($request);

    $time = microtime(true) + 3;

    for($i=0; microtime(true) < $time; $i++) {
        $func($request);
    }

    printf("%-24s: %d #/sec\n", $func, $i / 3);
}

function main()
{
    global $argv;

    if (extension_loaded('xdebug')) {
        echo "xdebug is loaded. benchmark must not load xdebug extension." . PHP_EOL;
        echo PHP_EOL;
        echo "e.g." . PHP_EOL;
        echo "  php -n {$argv[0]}" . PHP_EOL;
        exit();
    }
    benchmark('stratigility');
    benchmark('my_pipeline');
    benchmark('stratigility_with_path');
    benchmark('my_pipeline_with_path');
}

if (!debug_backtrace(false)) {
    main();
}
