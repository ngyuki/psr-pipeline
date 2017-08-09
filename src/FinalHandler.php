<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

class FinalHandler implements DelegateInterface
{
    public function process(ServerRequestInterface $request)
    {
        throw new LogicException("Middleware was not return Response object");
    }
}
