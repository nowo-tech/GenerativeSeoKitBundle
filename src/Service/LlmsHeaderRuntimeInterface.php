<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

/**
 * Optional host SPI next to {@see GeoRuntimeConfigInterface}: runtime llms.txt title and summary
 * (for example the brand and description stored in a database admin) instead of the YAML values.
 *
 * Implemented by the same service that overrides the `GeoRuntimeConfigInterface` alias; returning
 * null or an empty string keeps the configured `llms.title` / `llms.summary`.
 */
interface LlmsHeaderRuntimeInterface
{
    public function llmsTitle(): ?string;

    public function llmsSummary(): ?string;
}
