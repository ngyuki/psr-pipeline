<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class Next implements DelegateInterface
{
    private $middleware;
    private $delegate;

    public function __construct(MiddlewareInterface $middleware, DelegateInterface $delegate)
    {
        $this->middleware = $middleware;
        $this->delegate = $delegate;
    }

    public function process(ServerRequestInterface $request)
    {
        return $this->middleware->process($request, $this->delegate);
    }
}
