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
        self::assertSame([], $processed['llms']['sections']);
        self::assertSame([], $processed['llms']['optional_links']);
        self::assertFalse($processed['llms']['full_enabled']);
        self::assertSame('/llms-full.txt', $processed['llms']['full_path']);
        self::assertSame('', $processed['llms']['full_well_known_path']);
        self::assertSame('', $processed['llms']['full_body']);
        self::assertSame(DefaultCrawlerCatalog::defaults(), $processed['crawlers']);
        self::assertSame([], $processed['citations']);
        self::assertSame([], $processed['citation_routes']);
        self::assertTrue($processed['geo']['ai_robots_enabled']);
        self::assertSame([], $processed['geo']['extra_ai_user_agents']);
        self::assertSame('', $processed['geo']['llms_extra_markdown']);
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
