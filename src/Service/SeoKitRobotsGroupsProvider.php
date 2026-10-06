<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Nowo\SeoKitBundle\Service\GeoRobotsGroupsProviderInterface;

use function is_array;
use function is_string;

/**
 * Feeds SeoKit robots.txt extra User-agent groups from this bundle's crawler policy.
 *
 * @phpstan-type RobotsGroup array{user_agent: string, allow?: list<string>, disallow?: list<string>}
 */
final readonly class SeoKitRobotsGroupsProvider implements GeoRobotsGroupsProviderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    public function groups(): array
    {
        if (($this->config['enabled'] ?? true) !== true) {
            return [];
        }
        if (($this->config['robots_bridge'] ?? true) !== true) {
            return [];
        }

        $crawlers = $this->config['crawlers'] ?? [];
        if (!is_array($crawlers)) {
            return [];
        }

        $groups = [];
        foreach ($crawlers as $row) {
            if (!is_array($row)) {
                continue;
            }
            $agent = $row['user_agent'] ?? '';
            if (!is_string($agent) || $agent === '') {
                continue;
            }
            $allow    = $this->stringList($row['allow'] ?? []);
            $disallow = $this->stringList($row['disallow'] ?? []);
            $groups[] = [
                'user_agent' => $agent,
                'allow'      => $allow,
                'disallow'   => $disallow,
            ];
        }

        return $groups;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = $item;
            }
        }

        return $out;
    }
}
