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
        private ?GeoRuntimeConfigInterface $runtime = null,
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

    public function isFullEnabled(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $llms = is_array($this->config['llms'] ?? null) ? $this->config['llms'] : [];

        return ($llms['full_enabled'] ?? false) === true;
    }

    public function generate(): string
    {
        return $this->render(false);
    }

    public function generateFull(): string
    {
        return $this->render(true);
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

    private function render(bool $full): string
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

        $extra = trim(($this->runtime ?? new ConfigGeoRuntimeConfig($this->config))->llmsExtraMarkdown());
        if ($extra !== '') {
            $lines[] = $extra;
            $lines[] = '';
        }

        $citations = $this->citations();
        if ($citations !== []) {
            $lines[] = '## Citations';
            $lines[] = '';
            foreach ($this->linkLines($citations) as $item) {
                $lines[] = $item;
            }
            $lines[] = '';
        }

        foreach ($this->sections($llms) as $section) {
            $lines[] = '## ' . $section['heading'];
            $lines[] = '';
            if ($section['body'] !== '') {
                $lines[] = $section['body'];
                $lines[] = '';
            }
            foreach ($this->linkLines($section['links']) as $item) {
                $lines[] = $item;
            }
            if ($section['links'] !== []) {
                $lines[] = '';
            }
        }

        $optional = $this->optionalLinks($llms);
        if ($optional !== []) {
            $lines[] = '## Optional';
            $lines[] = '';
            foreach ($this->linkLines($optional) as $item) {
                $lines[] = $item;
            }
            $lines[] = '';
        }

        if ($full) {
            $body = is_string($llms['full_body'] ?? null) ? trim($llms['full_body']) : '';
            if ($body !== '') {
                $lines[] = '## Full';
                $lines[] = '';
                $lines[] = $body;
                $lines[] = '';
            }
        } elseif ($this->isFullEnabled()) {
            $fullPath = is_string($llms['full_path'] ?? null) && $llms['full_path'] !== '' ? $llms['full_path'] : '/llms-full.txt';
            $lines[]  = '## Related';
            $lines[]  = '';
            $lines[]  = '- [llms-full.txt](' . $fullPath . ')';
            $lines[]  = '';
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $llms
     *
     * @return list<array{heading: string, body: string, links: list<array{title: string, url: string, notes: string}>}>
     */
    private function sections(array $llms): array
    {
        $rows = $llms['sections'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $heading = is_string($row['heading'] ?? null) ? trim($row['heading']) : '';
            if ($heading === '') {
                continue;
            }
            $body  = is_string($row['body'] ?? null) ? trim($row['body']) : '';
            $links = $this->optionalLinks(['optional_links' => $row['links'] ?? []]);
            if ($body === '' && $links === []) {
                continue;
            }
            $out[] = [
                'heading' => $heading,
                'body'    => $body,
                'links'   => $links,
            ];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $llms
     *
     * @return list<array{title: string, url: string, notes: string}>
     */
    private function optionalLinks(array $llms): array
    {
        $rows = $llms['optional_links'] ?? [];
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

    /**
     * @param list<array{title: string, url: string, notes: string}> $rows
     *
     * @return list<string>
     */
    private function linkLines(array $rows): array
    {
        $lines = [];
        foreach ($rows as $citation) {
            $item  = '- [' . $citation['title'] . '](' . $citation['url'] . ')';
            $notes = trim($citation['notes']);
            if ($notes !== '') {
                $item .= ': ' . $notes;
            }
            $lines[] = $item;
        }

        return $lines;
    }
}
