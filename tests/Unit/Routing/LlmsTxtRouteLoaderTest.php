<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Routing;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
use Nowo\GenerativeSeoKitBundle\Routing\LlmsTxtRouteLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\RouteCollection;

final class LlmsTxtRouteLoaderTest extends TestCase
{
    public function testSupportsOnlyBundleType(): void
    {
        $loader = new LlmsTxtRouteLoader([]);
        self::assertTrue($loader->supports('.', 'nowo_generative_seo_kit'));
        self::assertFalse($loader->supports('.', 'yaml'));
    }

    public function testNoRoutesWhenDisabled(): void
    {
        $loader = new LlmsTxtRouteLoader(['enabled' => false, 'llms' => ['enabled' => true]]);
        $routes = $loader->load('.');
        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertCount(0, $routes);
    }

    public function testRegistersPathAndWellKnown(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'         => true,
                'path'            => '/llms.txt',
                'well_known_path' => '/.well-known/llms.txt',
            ],
        ]);
        $routes = $loader->load('.');
        self::assertNotNull($routes->get('nowo_generative_seo_kit_llms'));
        self::assertNotNull($routes->get('nowo_generative_seo_kit_llms_well_known'));
        self::assertSame('/llms.txt', $routes->get('nowo_generative_seo_kit_llms')->getPath());
        self::assertSame('index', $routes->get('nowo_generative_seo_kit_llms')->getDefault('_llms_variant'));
    }

    public function testRegistersFullRoutesWhenEnabled(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'              => true,
                'path'                 => '/llms.txt',
                'well_known_path'      => '/.well-known/llms.txt',
                'full_enabled'         => true,
                'full_path'            => '/llms-full.txt',
                'full_well_known_path' => '/.well-known/llms-full.txt',
            ],
        ]);
        $routes = $loader->load('.');
        self::assertNotNull($routes->get('nowo_generative_seo_kit_llms_full'));
        self::assertNotNull($routes->get('nowo_generative_seo_kit_llms_full_well_known'));
        self::assertSame('full', $routes->get('nowo_generative_seo_kit_llms_full')->getDefault('_llms_variant'));
    }

    public function testSkipsFullRoutesThatCollideWithIndex(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'              => true,
                'path'                 => '/llms.txt',
                'well_known_path'      => '/.well-known/llms.txt',
                'full_enabled'         => true,
                'full_path'            => '/llms.txt',
                'full_well_known_path' => '/.well-known/llms.txt',
            ],
        ]);
        self::assertCount(2, $loader->load('.'));
    }

    public function testFullPathDefaultsWhenEmpty(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'      => true,
                'path'         => '',
                'full_enabled' => true,
                'full_path'    => '',
            ],
        ]);
        $routes = $loader->load('.');
        self::assertSame('/llms.txt', $routes->get('nowo_generative_seo_kit_llms')->getPath());
        self::assertSame('/llms-full.txt', $routes->get('nowo_generative_seo_kit_llms_full')->getPath());
    }

    public function testFullWellKnownSkippedWhenNotString(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'              => true,
                'path'                 => '/llms.txt',
                'well_known_path'      => 1,
                'full_enabled'         => true,
                'full_path'            => 1,
                'full_well_known_path' => 1,
            ],
        ]);
        $routes = $loader->load('.');
        self::assertSame('/llms.txt', $routes->get('nowo_generative_seo_kit_llms')->getPath());
        self::assertSame('/llms-full.txt', $routes->get('nowo_generative_seo_kit_llms_full')->getPath());
        self::assertNull($routes->get('nowo_generative_seo_kit_llms_well_known'));
        self::assertNull($routes->get('nowo_generative_seo_kit_llms_full_well_known'));
    }

    public function testSkipsDuplicateWellKnown(): void
    {
        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'         => true,
                'path'            => '/llms.txt',
                'well_known_path' => '/llms.txt',
            ],
        ]);
        self::assertCount(1, $loader->load('.'));
    }

    public function testRoutesComeFromControllerAttributes(): void
    {
        $declared = [];
        foreach ((new ReflectionClass(LlmsTxtController::class))->getAttributes(RouteAttribute::class) as $attribute) {
            $route                           = $attribute->newInstance();
            $declared[(string) $route->name] = $route;
        }

        $loader = new LlmsTxtRouteLoader([
            'enabled' => true,
            'llms'    => [
                'enabled'              => true,
                'path'                 => '/ai/llms.txt',
                'well_known_path'      => '/.well-known/llms.txt',
                'full_enabled'         => true,
                'full_path'            => '/llms-full.txt',
                'full_well_known_path' => '/.well-known/llms-full.txt',
            ],
        ]);
        $routes = $loader->load('.');

        self::assertSame(array_keys($declared), array_keys($routes->all()));
        foreach ($routes->all() as $name => $route) {
            self::assertSame(LlmsTxtController::class, $route->getDefault('_controller'));
            self::assertSame($declared[$name]->methods, $route->getMethods());
            self::assertSame($declared[$name]->defaults['_llms_variant'], $route->getDefault('_llms_variant'));
        }

        // Configuration moves a route away from its attribute default path.
        self::assertSame('/llms.txt', $declared[LlmsTxtController::ROUTE_INDEX]->path);
        self::assertSame('/ai/llms.txt', $routes->get(LlmsTxtController::ROUTE_INDEX)?->getPath());
    }
}
