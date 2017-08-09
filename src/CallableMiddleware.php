<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableMiddleware implements MiddlewareInterface
{
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return ($this->handler)($request, $delegate);
    }
}
