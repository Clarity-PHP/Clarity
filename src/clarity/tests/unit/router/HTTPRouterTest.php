<?php

declare(strict_types=1);

namespace unit\router;

use Codeception\Test\Unit;
use framework\clarity\Container\interfaces\ContainerInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\interfaces\UriInterface;
use framework\clarity\Http\router\exceptions\HttpNotFoundException;
use framework\clarity\Http\router\interfaces\MiddlewareInterface;
use framework\clarity\tests\unit\router\helpers\DeleteDirectoryForTestingHelper;
use framework\clarity\tests\unit\router\mocks\CalculatorControllerMock;
use framework\clarity\tests\unit\router\mocks\HTTPMiddlewareMock;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class HTTPRouterTest extends Unit
{
    protected ContainerInterface $containerMock;
    protected framework\clarity\Http\router\HTTPRouter $router;
    private DeleteDirectoryForTestingHelper $deleteDirectoryForTestingHelper;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteDirectoryForTestingHelper = new DeleteDirectoryForTestingHelper();

        $this->testDir = sys_get_temp_dir() . '/test_router_config_files';
        mkdir($this->testDir);


        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->router = new framework\clarity\Http\router\HTTPRouter($this->containerMock);
    }

    /**
     * @return void
     */
    public function testAddRouteWithGetMethod(): void
    {
        $this->router->add('GET', '/test', 'TestHandler::handle');

        $this->assertArrayHasKey('GET', $this->router->routes);
        $this->assertArrayHasKey('/test', $this->router->routes['GET']);

        $addedRoute = $this->router->routes['GET']['/test'];
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);

        $this->assertEquals('/test', $addedRoute->getPath());
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);
        $this->assertEquals('GET', $addedRoute->getMethod());
    }

    /**
     * @return void
     */
    public function testAddRouteWithPostMethod(): void
    {
        $this->router->add('POST', '/test', 'TestHandler::handle');

        $this->assertArrayHasKey('POST', $this->router->routes);
        $this->assertArrayHasKey('/test', $this->router->routes['POST']);

        $addedRoute = $this->router->routes['POST']['/test'];
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);

        $this->assertEquals('/test', $addedRoute->getPath());
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);
        $this->assertEquals('POST', $addedRoute->getMethod());
    }

    /**
     * @return void
     */
    public function testAddRouteWithPutMethod(): void
    {
        $this->router->add('PUT', '/test', 'TestHandler::handle');

        $this->assertArrayHasKey('PUT', $this->router->routes);
        $this->assertArrayHasKey('/test', $this->router->routes['PUT']);

        $addedRoute = $this->router->routes['PUT']['/test'];
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);

        $this->assertEquals('/test', $addedRoute->getPath());
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);
        $this->assertEquals('PUT', $addedRoute->getMethod());
    }

    /**
     * @return void
     */
    public function testAddRouteWithPatchMethod(): void
    {
        $this->router->add('PATCH', '/test', 'TestHandler::handle');

        $this->assertArrayHasKey('PATCH', $this->router->routes);
        $this->assertArrayHasKey('/test', $this->router->routes['PATCH']);

        $addedRoute = $this->router->routes['PATCH']['/test'];
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);

        $this->assertEquals('/test', $addedRoute->getPath());
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);
        $this->assertEquals('PATCH', $addedRoute->getMethod());
    }

    /**
     * @return void
     */
    public function testAddRouteWithDeleteMethod(): void
    {
        $this->router->add('DELETE', '/test', 'TestHandler::handle');

        $this->assertArrayHasKey('DELETE', $this->router->routes);
        $this->assertArrayHasKey('/test', $this->router->routes['DELETE']);

        $addedRoute = $this->router->routes['DELETE']['/test'];
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);

        $this->assertEquals('/test', $addedRoute->getPath());
        $this->assertInstanceOf(framework\clarity\Http\router\Route::class, $addedRoute);
        $this->assertEquals('DELETE', $addedRoute->getMethod());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAddMiddlewareWithString(): void
    {
        $middlewareClass = 'TestMiddleware';

        $middlewareMock = $this->createMock(MiddlewareInterface::class);
        $this->containerMock
            ->method('get')
            ->with($middlewareClass)
            ->willReturn($middlewareMock);

        $result = $this->router->addMiddleware($middlewareClass);

        $this->assertSame($this->router, $result);

        $this->assertCount(1, $this->router->globalMiddlewares);
        $this->assertSame($middlewareMock, $this->router->globalMiddlewares[0]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAddMiddlewareWithObject(): void
    {
        $middlewareMock = $this->createMock(HTTPMiddlewareMock::class);

        $middlewareCallable = function ($request, $response, $next) use ($middlewareMock) {
            return $next($request);
        };

        $result = $this->router->addMiddleware($middlewareCallable);

        $this->assertSame($this->router, $result);

        $this->assertCount(1, $this->router->globalMiddlewares);
        $this->assertSame($middlewareCallable, $this->router->globalMiddlewares[0]);
    }


    /**
     * @return void
     */
    public function testAddMiddlewareWithInvalidMiddleware(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->router->addMiddleware('invalidMiddleware');
    }

    /**
     * @return void
     */
    public function testFindRouteWhenRouteExists(): void
    {
        $this->router->add('GET', '/home', 'TestHandler::handle');

        $foundRoute = $this->router->findRoute('/home', 'GET');

        $this->assertSame($this->router->routes['GET']['/home'], $foundRoute);
    }

    /**
     * @return void
     */
    public function testFindRouteWhenRouteDoesNotExist(): void
    {
        $foundRoute = $this->router->findRoute('/nonexistent', 'GET');

        $this->assertNull($foundRoute);
    }

    /**
     * @return void
     */
    public function testFindRouteWhenMethodDoesNotExist(): void
    {
        $this->router->add('POST', '/home', 'TestHandler::handle');

        $foundRoute = $this->router->findRoute('/home', 'GET');

        $this->assertNull($foundRoute);
    }

    /**
     * @return void
     */
    public function testFindRouteWhenMethodHasNoRoutes(): void
    {
        $foundRoute = $this->router->findRoute('/home', 'GET');

        $this->assertNull($foundRoute);
    }

    /**
     * @return void
     */
    public function testFindRouteWhenNeitherMethodNorRouteExists(): void
    {
        $foundRoute = $this->router->findRoute('/nonexistent', 'POST');

        $this->assertNull($foundRoute);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testResolveHandlerWithCallable(): void
    {
        $handler = function () {
            return 'hello';
        };

        $reflection = new ReflectionMethod($this->router, 'resolveHandler');
        $resolved = $reflection->invoke($this->router, $handler);

        $this->assertSame([$handler, null], $resolved);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testResolveHandlerWithStringClassMethod(): void
    {
        $handler = 'SomeClass::someMethod';

        $reflection = new ReflectionMethod($this->router, 'resolveHandler');
        $resolved = $reflection->invoke($this->router, $handler);

        $this->assertSame(['SomeClass', 'someMethod'], $resolved);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testResolveHandlerWithInvalidStringFormat(): void
    {
        $handler = 'InvalidHandler';

        $this->expectException(InvalidArgumentException::class);
        $reflection = new ReflectionMethod($this->router, 'resolveHandler');
        $reflection->invoke($this->router, $handler);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testPrepareParamsWithRequiredParams(): void
    {
        $route = '/user/{id}';

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'prepareParams');

        $params = $reflection->invoke($this->router, $route);

        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]['name']);
        $this->assertTrue($params[0]['required']);
        $this->assertNull($params[0]['default']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testPrepareParamsWithOptionalParams(): void
    {
        $route = '/user/{?id}';

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'prepareParams');

        $params = $reflection->invoke($this->router, $route);

        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]['name']);
        $this->assertFalse($params[0]['required']);
        $this->assertNull($params[0]['default']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testPrepareParamsWithDefaultValue(): void
    {
        $route = '/user/{id=42}';

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'prepareParams');

        $params = $reflection->invoke($this->router, $route);

        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]['name']);
        $this->assertTrue($params[0]['required']);
        $this->assertEquals(42, $params[0]['default']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testPrepareParamsWithOptionalAndDefaultValue(): void
    {
        $route = '/user/{?id=42}';

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'prepareParams');

        $params = $reflection->invoke($this->router, $route);

        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]['name']);
        $this->assertFalse($params[0]['required']);
        $this->assertEquals(42, $params[0]['default']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testMapParamsWithValidQueryParams(): void
    {
        $params = [
            ['name' => 'id', 'required' => true, 'default' => null],
            ['name' => 'name', 'required' => false, 'default' => 'Guest']
        ];

        $queryParams = [
            'id' => 123,
            'name' => 'John'
        ];

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'mapParams');

        $result = $reflection->invoke($this->router, $queryParams, $params);

        $this->assertCount(2, $result);
        $this->assertEquals(123, $result[0]);
        $this->assertEquals('John', $result[1]);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testMapParamsWithMissingRequiredParam(): void
    {
        $params = [
            ['name' => 'id', 'required' => true, 'default' => null],
            ['name' => 'name', 'required' => false, 'default' => 'Guest']
        ];

        $queryParams = [
            'name' => 'Alice'
        ];

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'mapParams');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Обязательный параметр id не найден в запросе');

        $reflection->invoke($this->router, $queryParams, $params);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testMapParamsWithOptionalParamAndDefaultValue(): void
    {
        $params = [
            ['name' => 'id', 'required' => true, 'default' => null],
            ['name' => 'name', 'required' => false, 'default' => 'Guest']
        ];

        $queryParams = [
            'id' => 123
        ];

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'mapParams');

        $result = $reflection->invoke($this->router, $queryParams, $params);

        $this->assertCount(2, $result);
        $this->assertEquals(123, $result[0]);
        $this->assertEquals('Guest', $result[1]);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testMapParamsWithMultipleParamsAndDefaults(): void
    {
        $params = [
            ['name' => 'id', 'required' => true, 'default' => null],
            ['name' => 'name', 'required' => false, 'default' => 'Guest'],
            ['name' => 'age', 'required' => false, 'default' => 30]
        ];

        $queryParams = [
            'id' => 123,
            'name' => 'John'
        ];

        $reflection = new ReflectionMethod(framework\clarity\Http\router\HTTPRouter::class, 'mapParams');

        $result = $reflection->invoke($this->router, $queryParams, $params);

        $this->assertCount(3, $result);
        $this->assertEquals(123, $result[0]);
        $this->assertEquals('John', $result[1]);
        $this->assertEquals(30, $result[2]);
    }

    /**
     * @return void
     */
    public function testGroupCreatesNewGroupWithPrefix(): void
    {
        $router = $this->router;

        $initialGroupsCount = count($router->groups);

        $this->router->group('/testGroup', function (framework\clarity\Http\router\HTTPRouter $router) {
            $this->router->get('test', 'Test::actionTest');
        });

        $this->assertCount($initialGroupsCount + 1, $this->router->groups);
        $this->assertArrayHasKey('/testGroup', $this->router->groups);
    }

    /**
     * @return void
     */
    public function testGroupAddsRouteWithCorrectPrefix(): void
    {
        $router = $this->router;

        $router->group('testGroup', function (framework\clarity\Http\router\HTTPRouter $router) {
            $router->get('test', 'Test::actionTest');
        });

        $route = $router->findRoute('/testGroup/test', 'GET');
        $this->assertNotNull($route);
        $this->assertEquals('Test', $route->handler);
    }

    /**
     * @return void
     */
    public function testNestedGroups(): void
    {
        $router = $this->router;

        $router->group('testGroupOne', function (framework\clarity\Http\router\HTTPRouter $router) {
            $router->group('testGroupTwo', function (framework\clarity\Http\router\HTTPRouter $router) {
                $router->get('test', 'Test::actionTest');
            });
        });

        $route = $router->findRoute('/testGroupOne/testGroupTwo/test', 'GET');
        $this->assertNotNull($route);
        $this->assertEquals('Test', $route->handler);
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);

    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWhenEmptyDirectory(): void
    {
        $files = $this->invokePrivateMethod($this->router, 'getRouterConfigFiles', [$this->testDir]);

        $this->assertEmpty($files);
    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWithPhpFiles(): void
    {
        touch($this->testDir . '/test1.php');

        $files = $this->invokePrivateMethod(
            $this->router,
            'getRouterConfigFiles',
            [$this->testDir]
        );

        $this->assertCount(1, $files);
        $this->assertStringContainsString('test1.php', $files[0]);
    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWithNonPhpFiles(): void
    {
        touch($this->testDir . '/test1.txt');

        $files = $this->invokePrivateMethod(
            $this->router,
            'getRouterConfigFiles',
            [$this->testDir]
        );

        $this->assertEmpty($files);
    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWithSubdirectories(): void
    {
        mkdir($this->testDir . '/subdir');
        touch($this->testDir . '/subdir/test2.php');

        $files = $this->invokePrivateMethod(
            $this->router,
            'getRouterConfigFiles',
            [$this->testDir]
        );

        $this->assertCount(1, $files);
        $this->assertStringContainsString('test2.php', $files[0]);
    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWithInvalidDirectory(): void
    {
        $nonExistentDir = $this->testDir . '/non_existent';

        $files = $this->invokePrivateMethod(
            $this->router,
            'getRouterConfigFiles',
            [$nonExistentDir]
        );

        $this->assertEmpty($files);
    }

    /**
     * @return void
     */
    public function testGetRouterConfigFilesWithEmptyDirectoryString(): void
    {
        $files = $this->invokePrivateMethod(
            $this->router,
            'getRouterConfigFiles',
            ['']
        );

        $this->assertEmpty($files);
    }

    /**
     * @return void
     */
    public function testGetCsrfAttributeWithCsrf(): void
    {
        $handler = new class {
            public function actionWithCsrf()
            {
            }
        };

        $result = $this->invokePrivateMethod($this->router, 'getCsrfAttribute', [$handler]);

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testGetCsrfAttributeWithoutCsrf(): void
    {
        $handler = new class {
            public function actionWithoutCsrf()
            {
            }
        };

        $result = $this->invokePrivateMethod($this->router, 'getCsrfAttribute', [$handler]);

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testGetCsrfAttributeWithStringHandlerWithoutCsrf(): void
    {
        eval('
        class SomeClass {
            public static function someMethod() {}
        }');

        $handler = 'SomeClass::someMethod';

        $result = $this->invokePrivateMethod($this->router, 'getCsrfAttribute', [$handler]);

        $this->assertFalse($result);
    }


    /**
     * @return void
     */
    public function testGetCsrfAttributeWithStringHandlerWithCsrf(): void
    {
        $handler = 'SomeClassWithCsrf::someMethodWithCsrf';

        eval('
        class SomeClassWithCsrf {
            #[Csrf]
            public static function someMethodWithCsrf() {}
        }');

        $result = $this->invokePrivateMethod($this->router, 'getCsrfAttribute', [$handler]);

        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws Exception
     * @throws HttpNotFoundException
     */
    public function testDispatchRouteNotFound(): void
    {
        $this->request->method('getMethod')
            ->willReturn('GET');

        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')
            ->willReturn('/non-existent-path');

        $this->request->method('getUri')
            ->willReturn($uriMock);

        $this->expectException(HttpNotFoundException::class);
        $this->expectExceptionMessage('Страница не найдена');

        $this->router->dispatch($this->request);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDispatchWithRoute(): void
    {
        $this->route = $this->createMock(framework\clarity\Http\router\Route::class);
        $uriMock = $this->createMock(UriInterface::class);

        $this->route->method('getHandler')->willReturn('SomeHandlerClass');
        $this->containerMock->method('get')->willReturn($this->route);

        $controller = $this->createMock(CalculatorControllerMock::class);
        $this->containerMock->method('get')->with('SomeHandlerClass')->willReturn($controller);

        $this->request->method('getMethod')->willReturn('GET');
        $uriMock->method('getPath')->willReturn('/valid-path');
        $this->request->method('getUri')->willReturn($uriMock);

        $middlewareMock = $this->createMock(HTTPMiddlewareMock::class);

        $middlewareCallable = function ($request, $response, $next) use ($middlewareMock) {
            return $next($request);
        };

        $this->router->addMiddleware($middlewareCallable);

        $params = ['param1' => 'value1'];
        $this->request->method('getQueryParams')->willReturn($params);

        $this->router->add('GET', '/valid-path', 'SomeHandlerClass::someAction');
    }

    /**
     * @param string $dir
     * @return void
     */

    /**
     * @return void
     */
    public function testCheckForGroupMiddlewaresPathDoesNotContainGroupShouldNotCallMiddlewares(): void
    {
        $middlewareCalled = false;
        $this->router->groups = [
            'group1' => (object)[
                'groupMiddlewares' => [
                    function () use (&$middlewareCalled) {
                        $middlewareCalled = true;
                    }
                ]
            ]
        ];

        $this->invokePrivateMethod($this->router, 'checkForGroupMiddlewares', ['/some/other/path', 'group1']);

        $this->assertFalse($middlewareCalled);
    }

    /**
     * @return void
     */
    public function testCheckForGroupMiddlewaresGroupDoesNotExistShouldNotCallMiddlewares(): void
    {
        $middlewareCalled = false;

        $this->router->groups = [];

        $this->invokePrivateMethod($this->router, 'checkForGroupMiddlewares', ['group1', '/some/path/group1/something']);

        $this->assertFalse($middlewareCalled);
    }


    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->deleteDirectoryForTestingHelper->deleteDirectory($this->testDir);
        parent::tearDown();
    }
}

