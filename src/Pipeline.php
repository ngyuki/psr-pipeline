<?php
namespace ngyuki\PsrPipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Pipeline implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var list<MiddlewareInterface>
     */
    private array $pipeline = [];

    /**
     * @param MiddlewareInterface|callable $middleware
     */
    public function pipe($middleware): void
    {
        if ($middleware instanceof MiddlewareInterface === false) {
            if (!is_callable($middleware)) {
                throw new InvalidArgumentException("Invalid middleware type");
            }
            $middleware = new CallableMiddleware($middleware);
        }
        $this->pipeline[] = $middleware;
    }

    /**
     * @param MiddlewareInterface|callable $middleware
     */
    public function path(string $path, $middleware): void
    {
        if ($middleware instanceof MiddlewareInterface === false) {
            if (!is_callable($middleware)) {
                throw new InvalidArgumentException("Invalid middleware type");
            }
            $middleware = new CallableMiddleware($middleware);
        }
        $middleware = new PathSpecificMiddleware($path, $middleware);
        $this->pipeline[] = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach (array_reverse($this->pipeline) as $middleware) {
            $handler = new Next($middleware, $handler);
        }
        return $handler->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, new FinalHandler());
    }
}
