<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Override;

use function is_array;
use function is_string;
use function trim;

/**
 * Default {@see GeoRuntimeConfigInterface} reading the static `nowo_generative_seo_kit.geo` YAML node.
 */
final readonly class ConfigGeoRuntimeConfig implements GeoRuntimeConfigInterface
{
    /**
     * @param array<string, mixed> $config Full bundle configuration
     */
    public function __construct(
        private array $config = [],
    ) {
    }

    #[Override]
    public function isAiRobotsEnabled(): bool
    {
        return ($this->geo()['ai_robots_enabled'] ?? true) !== false;
    }

    #[Override]
    public function extraAiUserAgents(): array
    {
        $rows = $this->geo()['extra_ai_user_agents'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (is_string($row) && trim($row) !== '') {
                $out[] = trim($row);
            }
        }

        return $out;
    }

    #[Override]
    public function llmsExtraMarkdown(): string
    {
        $markdown = $this->geo()['llms_extra_markdown'] ?? '';

        return is_string($markdown) ? trim($markdown) : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function geo(): array
    {
        $geo = $this->config['geo'] ?? [];

        return is_array($geo) ? $geo : [];
    }
}
