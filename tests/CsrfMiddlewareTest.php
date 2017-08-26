<?php

namespace DuskPHP\Middlewares\Tests\CSRF;

use DuskPHP\Middlewares\CSRF\CsrfMiddleware;
use DuskPHP\Middlewares\CSRF\InvalidCerfException;
use DuskPHP\Middlewares\CSRF\NoCsrfException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CsrfMiddlewareTest extends TestCase
{
    private function makeMiddleware(&$session = [])
    {
        return new CsrfMiddleware($session);
    }

    private function makeRequest(string $method = 'GET', ?array $params = null): ServerRequestInterface
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getMethod')->willReturn($method);
        $request->method('getParsedBody')->willReturn($params);

        return $request;
    }

    private function makeDelegate()
    {
        $delegate = $this->getMockBuilder(DelegateInterface::class)->getMock();
        $delegate->method('process')->willReturn($this->makeResponse());

        return $delegate;
    }

    private function makeResponse()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        return $response;
    }

    public function testAcceptValideSession()
    {
        $a = [];
        $b = $this->getMockBuilder(\ArrayAccess::class)->getMock();
        $middlewarea = $this->makeMiddleware($a);
        $middlewareb = $this->makeMiddleware($b);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewarea);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewareb);
    }

    public function testRejectInvalideSession()
    {
        $this->expectException(\TypeError::class);
        $a = new \stdClass();
        $middlewarea = $this->makeMiddleware($a);
    }

    public function testGetPass()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('process');
        $middleware->process(
            $this->makeRequest('GET'),
            $delegate
        );
    }

    public function testPreventPost()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->never())->method('process');
        $this->expectException(NoCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST'),
            $delegate
        );
    }

    public function testPostWithValidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())
            ->method('process')
            ->willReturn($this->makeResponse());

        $middleware->process(
            $this->makeRequest('POST', ['csrf' => $token]),
            $delegate
        );
    }

    public function testPostWithInvalidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->never())
            ->method('process');

        $this->expectException(InvalidCerfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['csrf' => 'aze']),
            $delegate
        );
    }

    public function testPostWithDoubleToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())
            ->method('process')
            ->willReturn($this->makeResponse());

        $middleware->process(
            $this->makeRequest('POST', ['csrf' => $token]),
            $delegate
        );
        $this->expectException(InvalidCerfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['csrf' => $token]),
            $delegate
        );
    }

    public function testLimitTokens()
    {
        $session = [];
        $middleware = $this->makeMiddleware($session);

        for ($i = 0; $i < 100; ++$i) {
            $token = $middleware->generateToken();
        }

        $this->assertCount(50, $session[$middleware->getSessionKey()]);
        $this->assertSame($token, $session[$middleware->getSessionKey()][49]);
    }
}
