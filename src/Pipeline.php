<?php
namespace ngyuki\PsrPipeline;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;

class Pipeline
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

    public function run(ServerRequestInterface $request)
    {
        $next = new FinalHandler();

        foreach (array_reverse($this->pipeline) as $handler) {
            $next = new Next($handler, $next);
        }

        return $next->process($request);
    }
}
