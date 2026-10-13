<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Nowo\GenerativeSeoKitBundle\Routing\LlmsTxtRouteLoader;

use function in_array;
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
        $title = $this->singleLine($this->runtimeHeader('title') ?? (is_string($llms['title'] ?? null) ? $llms['title'] : ''));
        $lines = [];

        $lines[] = '# ' . ($title !== '' ? $title : 'llms.txt');
        $lines[] = '';

        $summary = $this->singleLine($this->runtimeHeader('summary') ?? (is_string($llms['summary'] ?? null) ? $llms['summary'] : ''));
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
        } elseif ($this->isFullEnabled() && ($fullUrl = $this->fullDocumentPath($llms)) !== null) {
            $lines[] = '## Related';
            $lines[] = '';
            $lines[] = '- [llms-full.txt](' . $fullUrl . ')';
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Path of a registered llms-full.txt route, mirroring {@see LlmsTxtRouteLoader}:
     * full_path is skipped when it collides with path / well_known_path, then full_well_known_path is the fallback.
     *
     * @param array<string, mixed> $llms
     */
    private function fullDocumentPath(array $llms): ?string
    {
        $path      = is_string($llms['path'] ?? null) && $llms['path'] !== '' ? $llms['path'] : '/llms.txt';
        $wellKnown = is_string($llms['well_known_path'] ?? null) ? $llms['well_known_path'] : '';
        $fullPath  = is_string($llms['full_path'] ?? null) && $llms['full_path'] !== '' ? $llms['full_path'] : '/llms-full.txt';
        if ($fullPath !== $path && $fullPath !== $wellKnown) {
            return $fullPath;
        }

        $fullWellKnown = is_string($llms['full_well_known_path'] ?? null) ? $llms['full_well_known_path'] : '';

        return in_array($fullWellKnown, ['', $fullPath, $path, $wellKnown], true) ? null : $fullWellKnown;
    }

    /**
     * Title and summary are single Markdown lines: a newline would let the value open its own section.
     */
    private function singleLine(string $value): string
    {
        return trim((string) preg_replace('/[ \t\n\r\f\v]+/', ' ', $value));
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

    /**
     * Title / summary from {@see LlmsHeaderRuntimeInterface} when the runtime implements it.
     */
    private function runtimeHeader(string $field): ?string
    {
        if (!$this->runtime instanceof LlmsHeaderRuntimeInterface) {
            return null;
        }

        $value = trim((string) ($field === 'title' ? $this->runtime->llmsTitle() : $this->runtime->llmsSummary()));

        return $value !== '' ? $value : null;
    }
}
