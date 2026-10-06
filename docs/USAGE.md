# Usage

## Table of contents

- [llms.txt](#llmstxt)
- [Robots bridge](#robots-bridge)
- [Citation providers](#citation-providers)
- [Audit CLI](#audit-cli)
- [Demo](#demo)

## llms.txt

After importing routes (`type: nowo_generative_seo_kit`):

```bash
curl -s https://your-host/llms.txt
curl -s https://your-host/.well-known/llms.txt
```

Responses are `text/plain` with `X-Robots-Tag: noindex` so the file is a machine index, not a ranking URL.

## Robots bridge

Keep SeoKit `robots.txt` as the only robots document. This bundle implements `GeoRobotsGroupsProviderInterface`. Tune agents under `nowo_generative_seo_kit.crawlers`; do not duplicate `nowo_seo_kit.robots.groups` unless you need extra host-specific agents.

## Citation providers

```php
use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('nowo_generative_seo_kit.citation_source_provider')]
final class BlogCitationProvider implements CitationSourceProviderInterface
{
    public function sources(): array
    {
        return [
            ['title' => 'Latest post', 'url' => 'https://example.com/blog/hello', 'notes' => 'Evergreen'],
        ];
    }
}
```

## Audit CLI

```bash
php bin/console nowo:generative-seo:audit
php bin/console nowo:generative-seo:audit --lenient
```

Fails when llms.txt is disabled, citations are empty, or no crawler groups are emitted.

## Demo

```bash
make -C demo up-symfony8
```

Default URL: `http://localhost:8070`.
