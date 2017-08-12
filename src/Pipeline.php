<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;

class Pipeline implements MiddlewareInterface
{
    private $pipeline = [];

    public function pipe($middleware)
    {
        if ($middleware instanceof MiddlewareInterface) {
            $this->pipeline[] = $middleware;
        } elseif (is_callable($middleware)) {
            $this->pipeline[] = new CallableMiddleware($middleware);
        } else {
            throw new InvalidArgumentException("Invalid middleware type");
        }
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
