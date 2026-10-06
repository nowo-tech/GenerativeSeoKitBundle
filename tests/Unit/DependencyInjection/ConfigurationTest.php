<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\DependencyInjection;

use Nowo\GenerativeSeoKitBundle\DependencyInjection\Configuration;
use Nowo\GenerativeSeoKitBundle\DependencyInjection\GenerativeSeoKitExtension;
use Nowo\GenerativeSeoKitBundle\GenerativeSeoKitBundle;
use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $processed = (new Processor())->processConfiguration(new Configuration(), [[]]);
        self::assertTrue($processed['enabled']);
        self::assertTrue($processed['robots_bridge']);
        self::assertTrue($processed['llms']['enabled']);
        self::assertSame('/llms.txt', $processed['llms']['path']);
        self::assertSame('/.well-known/llms.txt', $processed['llms']['well_known_path']);
        self::assertSame(DefaultCrawlerCatalog::defaults(), $processed['crawlers']);
        self::assertSame([], $processed['citations']);
        self::assertSame([], $processed['citation_routes']);
        self::assertInstanceOf(GenerativeSeoKitExtension::class, (new GenerativeSeoKitBundle())->getContainerExtension());
    }

    public function testCitationRoutesDefaultsAndParameters(): void
    {
        $processed = (new Processor())->processConfiguration(new Configuration(), [[
            'citation_routes' => [
                ['route' => 'app_home', 'title' => 'Home', 'parameters' => ['slug' => 'geo']],
            ],
        ]]);
        self::assertSame(
            [
                [
                    'route'      => 'app_home',
                    'title'      => 'Home',
                    'parameters' => ['slug' => 'geo'],
                    'notes'      => '',
                ],
            ],
            $processed['citation_routes'],
        );
    }
}
