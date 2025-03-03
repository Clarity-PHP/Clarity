<?php

declare(strict_types=1);

namespace framework\clarity\Http\router\middlewares;

use framework\clarity\Http\interfaces\ResponseInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;

class HTTPBasicMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ResponseInterface      $response,
    ) {}

    /**
     * Обработка запроса и выполнение аутентификации
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, ?callable $next = null): void
    {
        $authorization = $request->getHeaderLine('Authorization');

        if (empty($authorization) === true || $this->validateBasicAuth($authorization) === false) {
            $response->setStatusCode(401);

            $response->setHeader('WWW-Authenticate', 'Basic realm="Protected"');

            $response->getBody()->write('Unauthorized');

            $response->send();

            return;
        }

        if ($next !== null) {
            $next($request, $response);
        }
    }

    /**
     * Проверка и декодирование заголовка Authorization
     */
    private function validateBasicAuth(string $authorization): bool
    {
        if (str_starts_with($authorization, 'Basic ') === true) {
            $encodedCredentials = substr($authorization, 6);

            $decodedCredentials = base64_decode($encodedCredentials, true);

            if ($decodedCredentials === false) {
                return false;
            }

            [$username, $password] = explode(':', $decodedCredentials) + [NULL, NULL];

            return $username === getenv('CLIENT_ID') && $password === getenv('CLIENT_SECRET');
        }

        return false;
    }

    /**
     * @return void
     */
    public function __invoke(): void
    {
        $this->process($this->request, $this->response);
    }
}
