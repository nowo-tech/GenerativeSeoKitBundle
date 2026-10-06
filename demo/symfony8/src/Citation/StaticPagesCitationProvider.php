<?php

declare(strict_types=1);

namespace App\Citation;

use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Example host CMS/docs citations (URLs the demo treats as canonical facts).
 */
#[AutoconfigureTag('nowo_generative_seo_kit.citation_source_provider')]
final class StaticPagesCitationProvider implements CitationSourceProviderInterface
{
    public function sources(): array
    {
        return [
            [
                'title' => 'llms.txt',
                'url'   => 'http://localhost:8070/llms.txt',
                'notes' => 'Machine index for this demo',
            ],
        ];
    }
}
