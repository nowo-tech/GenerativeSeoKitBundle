<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Extra citation rows for llms.txt (CMS, blog, docs). YAML citations come first.
 *
 * @phpstan-type Citation array{title: string, url: string, notes?: string}
 */
#[AutoconfigureTag('nowo_generative_seo_kit.citation_source_provider')]
interface CitationSourceProviderInterface
{
    /**
     * @return list<Citation>
     */
    public function sources(): array;
}
