<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

/**
 * Bundle-owned port for the AI crawler robots.txt groups (REQ-ARCH-001 rules 1, 3 and 8).
 *
 * Framework-free and independent of SeoKit: the audit command reads it, and the optional SeoKit bridge
 * ({@see SeoKitRobotsGroupsProvider}, registered only when nowo-tech/seo-kit-bundle is installed) forwards it to
 * SeoKit's robots.txt. Hosts may alias this interface to their own service to replace the crawler policy.
 *
 * Implementations must be worker safe (FrankenPHP worker, kernel kept alive): stateless, or reset per request.
 *
 * @phpstan-type RobotsGroup array{user_agent: string, allow?: list<string>, disallow?: list<string>}
 */
interface AiRobotsGroupsProviderInterface
{
    /**
     * AI crawler groups (empty when the bundle is disabled, `robots_bridge` is off or the runtime toggle is off).
     *
     * @return list<RobotsGroup>
     */
    public function groups(): array;
}
