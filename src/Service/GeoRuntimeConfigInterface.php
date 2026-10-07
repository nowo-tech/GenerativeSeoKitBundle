<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

/**
 * Host SPI for runtime GEO settings (for example values stored in a database admin).
 *
 * The kit ships {@see ConfigGeoRuntimeConfig} (YAML `geo` node) as the default alias; hosts override
 * the `Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface` service to provide their own values.
 * The kit never persists these settings.
 */
interface GeoRuntimeConfigInterface
{
    /**
     * When false, no AI crawler groups are contributed to SeoKit robots.txt.
     */
    public function isAiRobotsEnabled(): bool;

    /**
     * Extra User-agent tokens that receive `Allow: /` (tokens already in the configured crawler catalog are skipped).
     *
     * @return list<string>
     */
    public function extraAiUserAgents(): array;

    /**
     * Extra Markdown appended after the contact line of llms.txt / llms-full.txt. Empty string disables it.
     */
    public function llmsExtraMarkdown(): string;
}
