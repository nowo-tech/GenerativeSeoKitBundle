<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
use Nowo\GenerativeSeoKitBundle\Tests\Kernel\HostControllersRoutesKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\AttributeServicesLoader;
use Symfony\Component\Routing\RouterInterface;

use function sprintf;

/**
 * REQ-SF-004 rule 7: the llms.txt routes are opt-in (the `nowo_generative_seo_kit` route import);
 * the host's default `routing.controllers` resource must not register them.
 */
final class HostControllersRoutesTest extends TestCase
{
    public function testBundleRoutesAreNotRegisteredWithoutTheRouteImport(): void
    {
        if (!class_exists(AttributeServicesLoader::class)) {
            self::markTestSkipped('The routing.controllers resource needs symfony/routing >= 7.3.');
        }

        $kernel = new HostControllersRoutesKernel('test', true);
        $kernel->boot();

        try {
            $router = $kernel->getContainer()->get('router');
            self::assertInstanceOf(RouterInterface::class, $router);
            $routes = $router->getRouteCollection();

            foreach ([
                LlmsTxtController::ROUTE_INDEX,
                LlmsTxtController::ROUTE_INDEX_WELL_KNOWN,
                LlmsTxtController::ROUTE_FULL,
                LlmsTxtController::ROUTE_FULL_WELL_KNOWN,
            ] as $name) {
                self::assertNull($routes->get($name), sprintf('Route "%s" must only exist when the host imports the nowo_generative_seo_kit route type.', $name));
            }

            foreach ($routes as $name => $route) {
                self::assertStringStartsNotWith('nowo_generative_seo_kit', $name);
            }
        } finally {
            $kernel->shutdown();
        }
    }

    public function testImportedRouteTypeServesLlmsTxt(): void
    {
        if (!class_exists(AttributeServicesLoader::class)) {
            self::markTestSkipped('The routing.controllers resource needs symfony/routing >= 7.3.');
        }

        $kernel = new HostControllersRoutesKernel('test', true, true);
        $kernel->boot();

        try {
            $router = $kernel->getContainer()->get('router');
            self::assertInstanceOf(RouterInterface::class, $router);
            self::assertNotNull($router->getRouteCollection()->get(LlmsTxtController::ROUTE_INDEX));

            $response = $kernel->handle(Request::create('/llms.txt'));
            self::assertSame(200, $response->getStatusCode());
            self::assertStringStartsWith('text/plain', (string) $response->headers->get('Content-Type'));
        } finally {
            $kernel->shutdown();
        }
    }
}
