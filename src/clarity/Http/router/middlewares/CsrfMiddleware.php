<?php

namespace framework\clarity\Http\router\middlewares;

use framework\clarity\Http\CsrfToken;
use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\exceptions\HttpForbiddenException;
use framework\clarity\Http\router\exceptions\HttpInternalServerErrorException;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;

readonly class CsrfMiddleware implements MiddlewareInterface
{

    /**
     * @throws HttpForbiddenException
     * @throws HttpInternalServerErrorException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, ?callable $next = null): void
    {
        $csrfToken = $request->getHeader('X-CSRF-TOKEN')
            ?? $request->getParsedBody()['_token']
            ?? null;

        $csrfService = new CsrfToken();

        if ($csrfToken === null || $csrfService->isValidCsrfToken($csrfToken, $_COOKIE['csrf_token'] ?? null) === false) {
            throw new HttpForbiddenException('Forbidden: Invalid CSRF token');
        }

        if ($next !== null) {
            $next($request, $response);
        }
    }
}