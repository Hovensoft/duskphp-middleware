<?php

namespace DuskPHP\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PageLoaderMiddleware.
 */
class PageLoaderMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $layoutPath;
    /**
     * @var string
     */
    private $contentPath;
    /**
     * @var array
     */
    private $part;

    /**
     * PageLoaderMiddleware constructor.
     *
     * @param string $layoutPath
     * @param string $contentPath
     * @param array  $part        ["part identifier" => "path"]
     */
    public function __construct(string $layoutPath, string $contentPath, array $part = [])
    {
        $this->layoutPath = $layoutPath;
        $this->contentPath = $contentPath;
        $this->part = $part;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $content = file_get_contents($this->layoutPath);
        $content = str_replace('<:content:>', file_get_contents($this->contentPath), $content);

        foreach ($this->part as $v => $k) {
            $content = str_replace("<:$v:>", file_get_contents($k), $content);
        }

        $response = $delegate->process($request);
        $response->getBody()->write($content);

        return $response;
    }
}
