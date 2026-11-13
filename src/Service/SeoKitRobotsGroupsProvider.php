<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Nowo\SeoKitBundle\Service\GeoRobotsGroupsProviderInterface;

/**
 * SeoKit bridge: feeds SeoKit robots.txt extra User-agent groups from this bundle's crawler policy.
 *
 * Registered only when nowo-tech/seo-kit-bundle is installed (its {@see GeoRobotsGroupsProviderInterface} exists).
 * The groups come from the bundle-owned {@see AiRobotsGroupsProviderInterface} port; without one passed in, the
 * default {@see CrawlerRobotsGroupsProvider} is built from the same configuration, so direct callers keep the exact
 * previous behaviour.
 *
 * Optional {@see GeoRuntimeConfigInterface} toggles the whole bridge and appends host-provided extra agents.
 *
 * @phpstan-type RobotsGroup array{user_agent: string, allow?: list<string>, disallow?: list<string>}
 */
final readonly class SeoKitRobotsGroupsProvider implements GeoRobotsGroupsProviderInterface, AiRobotsGroupsProviderInterface
{
    private AiRobotsGroupsProviderInterface $groups;

    /**
     * @param array<string, mixed> $config
     * @param AiRobotsGroupsProviderInterface|null $groups Port to forward (the container passes its alias); null builds the default provider
     */
    public function __construct(
        array $config,
        ?GeoRuntimeConfigInterface $runtime = null,
        ?AiRobotsGroupsProviderInterface $groups = null,
    ) {
        $this->groups = $groups ?? new CrawlerRobotsGroupsProvider($config, $runtime);
    }

    /**
     * AI crawler robots.txt groups handed to SeoKit (empty when disabled or when the robots bridge is off).
     */
    public function groups(): array
    {
        return $this->groups->groups();
    }
}
