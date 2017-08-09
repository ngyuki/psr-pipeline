<?php
namespace ngyuki\PsrPipeline;

use Psr\Http\Message\ServerRequestInterface;

class Pipeline
{
    private $pipeline = [];

    public function pipe(callable $handler)
    {
        $this->pipeline[] = new CallableMiddleware($handler);
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
