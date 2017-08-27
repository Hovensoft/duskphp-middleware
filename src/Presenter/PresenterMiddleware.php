<?php

namespace DuskPHP\Middleware\Presenter;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PresenterMiddleware.
 */
abstract class PresenterMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $action;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @throws PresenterException
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $delegate->process($request);
        $response = call_user_func([self, $this->action]);
        if (!$response instanceof ResponseInterface) {
            throw new PresenterException('The action ' . $this->action . ' have to return a response');
        }
        return $response;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }
}
