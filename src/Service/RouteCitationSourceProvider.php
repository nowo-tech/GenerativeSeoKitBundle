<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function is_array;
use function is_string;

/**
 * Citations from named Symfony routes (no sitemap scrape).
 */
final readonly class RouteCitationSourceProvider implements CitationSourceProviderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
        private ?UrlGeneratorInterface $urlGenerator = null,
    ) {
    }

    public function sources(): array
    {
        if (!$this->urlGenerator instanceof UrlGeneratorInterface) {
            return [];
        }

        $rows = $this->config['citation_routes'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $route = $row['route'] ?? null;
            $title = $row['title'] ?? null;
            if (!is_string($route) || $route === '' || !is_string($title) || $title === '') {
                continue;
            }

            $parameters = $row['parameters'] ?? [];
            if (!is_array($parameters)) {
                $parameters = [];
            }

            $routeParams = [];
            foreach ($parameters as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }
                $routeParams[$key] = $value;
            }

            try {
                $url = $this->urlGenerator->generate($route, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (RoutingExceptionInterface) {
                continue;
            }

            if ($url === '') {
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
