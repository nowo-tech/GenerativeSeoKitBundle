<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
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
}
