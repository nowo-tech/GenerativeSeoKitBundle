<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use FilesystemIterator;
use Nowo\GenerativeSeoKitBundle\Service\AiRobotsGroupsProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\ConfigGeoRuntimeConfig;
use Nowo\GenerativeSeoKitBundle\Service\CrawlerRobotsGroupsProvider;
use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_column;
use function file_get_contents;
use function is_string;
use function str_contains;

/**
 * The bundle-owned robots groups port and its equivalence with the 1.6.x SeoKit provider.
 */
final class CrawlerRobotsGroupsProviderTest extends TestCase
{
    /**
     * Fixtures and the groups 1.6.1's SeoKitRobotsGroupsProvider returned for them.
     *
     * @return iterable<string, array{array<string, mixed>, ?GeoRuntimeConfigInterface, list<array<string, mixed>>}>
     */
    public static function fixtures(): iterable
    {
        yield 'bundle disabled' => [['enabled' => false, 'crawlers' => DefaultCrawlerCatalog::defaults()], null, []];
        yield 'bridge off' => [['enabled' => true, 'robots_bridge' => false, 'crawlers' => DefaultCrawlerCatalog::defaults()], null, []];
        yield 'crawlers not an array' => [['enabled' => true, 'crawlers' => 'nope'], null, []];
        yield 'invalid rows skipped' => [
            ['enabled' => true, 'crawlers' => [
                'nope',
                ['user_agent' => ''],
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => 'bad'],
                ['user_agent' => 'CCBot', 'allow' => ['/', '', 1], 'disallow' => ['/private']],
            ]],
            null,
            [
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'CCBot', 'allow' => ['/'], 'disallow' => ['/private']],
            ],
        ];
        yield 'runtime toggle off' => [
            ['enabled' => true, 'crawlers' => DefaultCrawlerCatalog::defaults()],
            self::runtime(false, ['MyBot']),
            [],
        ];
        yield 'extra agents deduplicated' => [
            ['enabled' => true, 'crawlers' => [['user_agent' => 'GPTBot', 'allow' => ['/']]]],
            self::runtime(true, ['gptbot', ' MyBot ', 'mybot', '', 'OtherBot']),
            [
                ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'MyBot', 'allow' => ['/'], 'disallow' => []],
                ['user_agent' => 'OtherBot', 'allow' => ['/'], 'disallow' => []],
            ],
        ];
        $yaml = ['enabled' => true, 'crawlers' => [], 'geo' => ['ai_robots_enabled' => true, 'extra_ai_user_agents' => ['YamlBot']]];
        yield 'yaml geo node' => [$yaml, null, [['user_agent' => 'YamlBot', 'allow' => ['/'], 'disallow' => []]]];
        yield 'yaml geo node, explicit runtime' => [$yaml, new ConfigGeoRuntimeConfig($yaml), [['user_agent' => 'YamlBot', 'allow' => ['/'], 'disallow' => []]]];
    }

    /**
     * @param array<string, mixed> $config
     * @param list<array<string, mixed>> $expected
     */
    #[DataProvider('fixtures')]
    public function testPortMatchesPreviousSeoKitProviderBehaviour(array $config, ?GeoRuntimeConfigInterface $runtime, array $expected): void
    {
        $port   = new CrawlerRobotsGroupsProvider($config, $runtime);
        $bridge = new SeoKitRobotsGroupsProvider($config, $runtime);

        self::assertSame($expected, $port->groups());
        self::assertSame($expected, $bridge->groups(), 'the SeoKit bridge keeps the 1.6.x output');
    }

    public function testCatalogDefaultsAreContributed(): void
    {
        $groups = (new CrawlerRobotsGroupsProvider(['enabled' => true, 'crawlers' => DefaultCrawlerCatalog::defaults()]))->groups();

        self::assertContains('GPTBot', array_column($groups, 'user_agent'));
    }

    public function testBridgeForwardsAnInjectedPort(): void
    {
        $host = new class implements AiRobotsGroupsProviderInterface {
            public function groups(): array
            {
                return [['user_agent' => 'HostBot', 'allow' => ['/docs'], 'disallow' => []]];
            }
        };

        $bridge = new SeoKitRobotsGroupsProvider(['enabled' => false], null, $host);

        self::assertSame([['user_agent' => 'HostBot', 'allow' => ['/docs'], 'disallow' => []]], $bridge->groups());
    }

    /**
     * Only the SeoKit bridge may reference SeoKit, so the bundle loads without it (2.0 moves it to `suggest`).
     */
    public function testOnlyTheBridgeReferencesSeoKit(): void
    {
        $offenders = [];
        $files     = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../../src', FilesystemIterator::SKIP_DOTS));
        foreach ($files as $info) {
            self::assertInstanceOf(SplFileInfo::class, $info);
            if ($info->getExtension() !== 'php') {
                continue;
            }
            $file   = $info->getPathname();
            $source = file_get_contents($file);
            self::assertTrue(is_string($source));
            if (str_contains($source, 'Nowo\SeoKitBundle\\') && !str_contains($file, 'SeoKitRobotsGroupsProvider.php') && !str_contains($file, 'GenerativeSeoKitExtension.php')) {
                $offenders[] = $file;
            }
        }

        self::assertSame([], $offenders);
    }

    /**
     * @param list<string> $extra
     */
    private static function runtime(bool $enabled, array $extra): GeoRuntimeConfigInterface
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
