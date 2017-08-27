<?php

namespace DuskPHP\Middleware\CSRF;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CsrfAuthenticatorMiddleware.
 */
class CsrfAuthenticatorMiddleware implements MiddlewareInterface
{
    /**
     * @var array|\ArrayAccess
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey;
    /**
     * @var string
     */
    private $fromKey;
    /**
     * @var int
     */
    private $limit;

    /**
     * CsrfAuthenticatorMiddleware constructor.
     *
     * @param array|\ArrayAccess $session
     * @param int                $limit
     * @param string             $sessionKey
     * @param string             $fromKey
     */
    public function __construct(
        &$session,
        int $limit = 50,
        string $sessionKey = 'csrf.tokens',
        string $fromKey = 'csrf'
    ) {
        $this->testSession($session);
        $this->session = &$session;
        $this->sessionKey = $sessionKey;
        $this->fromKey = $fromKey;
        $this->limit = $limit;
    }

    /**
     * Generate the input field.
     *
     * @return string
     */
    public function input(): string
    {
        return "<input type=\"hidden\" name=\"{$this->getFromKey()}\" value=\"{$this->generateToken()}\" />";
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @throws InvalidCerfException
     * @throws NoCsrfException
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if (in_array($request->getMethod(), ['PUT', 'POST', 'DELETE'], true)) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->fromKey, $params)) {
                throw new NoCsrfException();
            }

            if (!in_array($params[$this->fromKey], $this->session[$this->sessionKey] ?? [], true)) {
                throw new InvalidCerfException();
            }
            $this->removeToken($params[$this->fromKey]);
        }
        $response = $delegate->process($request);
        $body = $response->getBody()->getContents();
        $body = str_replace('<:csrf_token_field:>', $this->input(), $body);

        return $response->withBody($body);
    }

    /**
     * Generate and store a random token.
     *
     * @return string The token
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $tokens = $this->session[$this->sessionKey] ?? [];
        $tokens[] = $token;
        $this->session[$this->sessionKey] = $this->limitTokens($tokens);

        return $token;
    }

    /**
     * Test if the session acts as an array.
     *
     * @param $session
     *
     * @throws \TypeError
     */
    private function testSession($session): void
    {
        if (!is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError('Session is not an array');
        }
    }

    /**
     * Remove a token from session.
     *
     * @param string $token
     */
    private function removeToken(string $token): void
    {
        $this->session[$this->sessionKey] = array_filter(
            $this->session[$this->sessionKey] ?? [],
            function ($t) use ($token) {
                return $token !== $t;
            }
        );
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return string
     */
    public function getFromKey(): string
    {
        return $this->fromKey;
    }

    private function limitTokens(array $tokens)
    {
        if (count($tokens) > $this->limit) {
            array_shift($tokens);
        }

        return $tokens;
    }
}
