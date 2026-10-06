<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Routing;

use Nowo\GenerativeSeoKitBundle\Routing\LlmsTxtRouteLoader;
use PHPUnit\Framework\TestCase;
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
}
