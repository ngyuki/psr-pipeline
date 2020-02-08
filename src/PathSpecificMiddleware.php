<?php
namespace ngyuki\PsrPipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PathSpecificMiddleware implements MiddlewareInterface
{
    private string $path;

    private MiddlewareInterface $middleware;

    public function __construct($path, MiddlewareInterface $middleware)
    {
        $this->path = rtrim($path, '/');
        $this->middleware = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origPath = $request->getUri()->getPath();

        if (
            ($this->path !== $origPath) &&
            (strncmp($origPath, $this->path . '/', strlen($this->path) + 1) !== 0)
        ) {
            return $handler->handle($request);
        }

        $handler = new Next(new CallableMiddleware(
            function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($origPath) {
                $request = $request->withUri($request->getUri()->withPath($origPath));
                return $handler->handle($request);
            }),
            $handler
        );

        $newPath = substr($origPath, strlen($this->path));
        if ($newPath === '') {
            $newPath = '/';
        }
        $request = $request->withUri($request->getUri()->withPath($newPath));

        return $this->middleware->process($request, $handler);
    }
}
