<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use function is_array;
use function is_string;
use function trim;

/**
 * Builds llms.txt (plain text index for generative engines).
 */
final readonly class LlmsTxtGenerator
{
    /**
     * @param array<string, mixed> $config
     * @param iterable<CitationSourceProviderInterface> $citationProviders
     */
    public function __construct(
        private array $config,
        private iterable $citationProviders = [],
    ) {
    }

    public function isEnabled(): bool
    {
        if (($this->config['enabled'] ?? true) !== true) {
            return false;
        }

        $llms = is_array($this->config['llms'] ?? null) ? $this->config['llms'] : [];

        return ($llms['enabled'] ?? true) === true;
    }

    public function generate(): string
    {
        $llms  = is_array($this->config['llms'] ?? null) ? $this->config['llms'] : [];
        $title = is_string($llms['title'] ?? null) ? trim($llms['title']) : '';
        $lines = [];

        $lines[] = '# ' . ($title !== '' ? $title : 'llms.txt');
        $lines[] = '';

        $summary = is_string($llms['summary'] ?? null) ? trim($llms['summary']) : '';
        if ($summary !== '') {
            $lines[] = '> ' . $summary;
            $lines[] = '';
        }

        $description = is_string($llms['description'] ?? null) ? trim($llms['description']) : '';
        if ($description !== '') {
            $lines[] = $description;
            $lines[] = '';
        }

        $contact = is_string($llms['contact'] ?? null) ? trim($llms['contact']) : '';
        if ($contact !== '') {
            $lines[] = 'Contact: ' . $contact;
            $lines[] = '';
        }

        $citations = $this->citations();
        if ($citations !== []) {
            $lines[] = '## Citations';
            $lines[] = '';
            foreach ($citations as $citation) {
                $item  = '- [' . $citation['title'] . '](' . $citation['url'] . ')';
                $notes = trim($citation['notes']);
                if ($notes !== '') {
                    $item .= ': ' . $notes;
                }
                $lines[] = $item;
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<array{title: string, url: string, notes: string}>
     */
    public function citations(): array
    {
        $merged = [];
        $seen   = [];
        foreach ($this->citationProviders as $provider) {
            foreach ($provider->sources() as $row) {
                $url = $row['url'];
                if (isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;
                $merged[]   = [
                    'title' => $row['title'],
                    'url'   => $url,
                    'notes' => $row['notes'] ?? '',
                ];
            }
        }

        return $merged;
    }
}
