<?php
require __DIR__ . '/../vendor/autoload.php';

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequestFactory;

use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\NoopFinalHandler;

use ngyuki\PsrPipeline\Pipeline;

function stratigility(ServerRequestInterface $request)
{
    $pipeline = new MiddlewarePipe();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            return $delegate->process($request);
        });
    }

    /** @noinspection PhpUnusedParameterInspection */
    $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
        return new TextResponse('x');
    });

    return $pipeline($request, new Response(), new NoopFinalHandler());
}

function my_pipeline(ServerRequestInterface $request)
{
    $pipeline = new Pipeline();

    for ($i = 0; $i < 100; $i++) {
        $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            return $delegate->process($request);
        });
    }

    /** @noinspection PhpUnusedParameterInspection */
    $pipeline->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
        return new TextResponse('x');
    });

    return $pipeline->run($request);
}

function benchmark($func)
{
    $request = ServerRequestFactory::fromGlobals();

    $func($request);

    $time = microtime(true) + 3;

    for($i=0; microtime(true) < $time; $i++) {
        $func($request);
    }

    printf("%-16s: %d #/sec\n", $func, $i / 3);
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
}

/*
 * stratigility    : 1947 #/sec
 * my_pipeline     : 8602 #/sec
 */

if (!debug_backtrace(false)) {
    main();
}
