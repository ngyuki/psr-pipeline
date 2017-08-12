<?php
namespace ngyuki\PsrPipeline;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class PathSpecificMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    public function __construct($path, MiddlewareInterface $middleware)
    {
        $this->path = rtrim($path, '/');
        $this->middleware = $middleware;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $origPath = $request->getUri()->getPath();

        if (
            ($this->path !== $origPath) &&
            (strncmp($origPath, $this->path . '/', strlen($this->path) + 1) !== 0)
        ) {
            return $delegate->process($request);
        }

        $delegate = new Next(new CallableMiddleware(
            function(ServerRequestInterface $request, DelegateInterface $delegate) use ($origPath) {
                $request = $request->withUri($request->getUri()->withPath($origPath));
                return $delegate->process($request);
            }),
            $delegate
        );

        $newPath = substr($origPath, strlen($this->path));
        if ($newPath === '') {
            $newPath = '/';
        }
        $request = $request->withUri($request->getUri()->withPath($newPath));

        return $this->middleware->process($request, $delegate);
    }
}
