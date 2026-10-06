<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use function is_array;
use function is_string;

/**
 * Citations declared in nowo_generative_seo_kit.citations.
 */
final readonly class ConfigCitationSourceProvider implements CitationSourceProviderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    public function sources(): array
    {
        $rows = $this->config['citations'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = $row['title'] ?? null;
            $url   = $row['url'] ?? null;
            if (!is_string($title) || $title === '' || !is_string($url) || $url === '') {
                continue;
            }
            $notes = $row['notes'] ?? '';
            $out[] = [
                'title' => $title,
                'url'   => $url,
                'notes' => is_string($notes) ? $notes : '',
            ];
        }

        return $out;
    }
}
