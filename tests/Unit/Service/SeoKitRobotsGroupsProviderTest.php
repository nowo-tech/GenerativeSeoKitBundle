<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\ConfigGeoRuntimeConfig;
use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\TestCase;

final class SeoKitRobotsGroupsProviderTest extends TestCase
{
    public function testDefaultsCatalogIsNonEmpty(): void
    {
        self::assertNotSame([], DefaultCrawlerCatalog::defaults());
        self::assertSame('GPTBot', DefaultCrawlerCatalog::defaults()[0]['user_agent']);
    }

    public function testEmptyWhenDisabledOrBridgeOff(): void
    {
        $off = new SeoKitRobotsGroupsProvider(['enabled' => false, 'crawlers' => DefaultCrawlerCatalog::defaults()]);
        self::assertSame([], $off->groups());

        $bridge = new SeoKitRobotsGroupsProvider([
            'enabled'       => true,
            'robots_bridge' => false,
            'crawlers'      => DefaultCrawlerCatalog::defaults(),
        ]);
        self::assertSame([], $bridge->groups());
    }

    public function testSkipsInvalidCrawlerRows(): void
    {
        $provider = new SeoKitRobotsGroupsProvider([
            'enabled'  => true,
            'crawlers' => [
                'nope',
                ['user_agent' => ''],
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => 'bad'],
                ['user_agent' => 'CCBot', 'allow' => ['/', '', 1], 'disallow' => ['/private']],
            ],
        ]);
        self::assertSame(
            [
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'CCBot', 'allow' => ['/'], 'disallow' => ['/private']],
            ],
            $provider->groups(),
        );
    }

    public function testEmptyWhenCrawlersNotArray(): void
    {
        $provider = new SeoKitRobotsGroupsProvider(['enabled' => true, 'crawlers' => 'nope']);
        self::assertSame([], $provider->groups());
    }

    public function testRuntimeToggleOffReturnsNoGroups(): void
    {
        $provider = new SeoKitRobotsGroupsProvider(
            ['enabled' => true, 'crawlers' => DefaultCrawlerCatalog::defaults()],
            $this->runtime(false, ['MyBot']),
        );
        self::assertSame([], $provider->groups());
    }

    public function testRuntimeExtraAgentsAreAppendedWithoutDuplicates(): void
    {
        $provider = new SeoKitRobotsGroupsProvider(
            ['enabled' => true, 'crawlers' => [['user_agent' => 'GPTBot', 'allow' => ['/']]]],
            $this->runtime(true, ['gptbot', ' MyBot ', 'mybot', '', 'OtherBot']),
        );
        self::assertSame(
            [
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'MyBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'OtherBot', 'allow' => ['/'], 'disallow' => []],
            ],
            $provider->groups(),
        );
    }

    public function testExtraAgentsStillSuppressedWhenBridgeOff(): void
    {
        $provider = new SeoKitRobotsGroupsProvider(
            ['enabled' => true, 'robots_bridge' => false, 'crawlers' => []],
            $this->runtime(true, ['MyBot']),
        );
        self::assertSame([], $provider->groups());
    }

    public function testDefaultsToYamlGeoNode(): void
    {
        $config = [
            'enabled'  => true,
            'crawlers' => [],
            'geo'      => ['ai_robots_enabled' => true, 'extra_ai_user_agents' => ['YamlBot']],
        ];
        self::assertSame(
            [['user_agent' => 'YamlBot', 'allow' => ['/'], 'disallow' => []]],
            (new SeoKitRobotsGroupsProvider($config, new ConfigGeoRuntimeConfig($config)))->groups(),
        );
        self::assertSame(
            [['user_agent' => 'YamlBot', 'allow' => ['/'], 'disallow' => []]],
            (new SeoKitRobotsGroupsProvider($config))->groups(),
        );

        $off = ['enabled' => true, 'crawlers' => DefaultCrawlerCatalog::defaults(), 'geo' => ['ai_robots_enabled' => false]];
        self::assertSame([], (new SeoKitRobotsGroupsProvider($off))->groups());
    }

    /**
     * @param list<string> $extra
     */
    private function runtime(bool $enabled, array $extra): GeoRuntimeConfigInterface
    {
        return new class($enabled, $extra) implements GeoRuntimeConfigInterface {
            /**
             * @param list<string> $extra
             */
            public function __construct(private readonly bool $enabled, private readonly array $extra)
            {
            }

            public function isAiRobotsEnabled(): bool
            {
                return $this->enabled;
            }

            public function extraAiUserAgents(): array
            {
                return $this->extra;
            }

            public function llmsExtraMarkdown(): string
            {
                return '';
            }
        };
    }
}
