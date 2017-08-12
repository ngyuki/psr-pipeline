<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;

class Pipeline implements MiddlewareInterface
{
    private $pipeline = [];

    public function pipe($path, $middleware = null)
    {
        if ($middleware === null) {
            $middleware = $path;
            $path = null;
        }

        if ($middleware instanceof MiddlewareInterface) {
            ;
        } elseif (is_callable($middleware)) {
            $middleware = new CallableMiddleware($middleware);
        } else {
            throw new InvalidArgumentException("Invalid middleware type");
        }

        if ($path !== null) {
            $middleware = new PathSpecificMiddleware($path, $middleware);
        }

        $this->pipeline[] = $middleware;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        foreach (array_reverse($this->pipeline) as $handler) {
            $delegate = new Next($handler, $delegate);
        }

        return $delegate->process($request);
    }

    public function run(ServerRequestInterface $request)
    {
        return $this->process($request, new FinalHandler());
    }
}
