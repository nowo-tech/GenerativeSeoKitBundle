# Usage

## Table of contents

- [llms.txt](#llmstxt)
- [llms-full.txt](#llms-fulltxt)
- [Robots bridge](#robots-bridge)
- [Citation providers](#citation-providers)
- [Named-route citations](#named-route-citations)
- [Audit CLI](#audit-cli)
- [Host CI](#host-ci)
- [Demo](#demo)

## llms.txt

After importing routes (`type: nowo_generative_seo_kit`):

```bash
curl -s https://your-host/llms.txt
curl -s https://your-host/.well-known/llms.txt
```

Responses are `text/plain` with `X-Robots-Tag: noindex` so the file is a machine index, not a ranking URL.

Optional `llms.sections` and `llms.optional_links` append `##` blocks (llmstxt.org). With empty defaults the 1.x title / summary / citations body is unchanged.

## llms-full.txt

Opt-in related file (`llms.full_enabled: true`):

```bash
curl -s https://your-host/llms-full.txt
```

The index then includes a `## Related` link. The full file repeats the index sections plus optional `llms.full_body` under `## Full`. Leave `full_enabled` false to keep the 1.x route set.

## Robots bridge

Keep SeoKit `robots.txt` as the only robots document. This bundle implements `GeoRobotsGroupsProviderInterface`. Tune agents under `nowo_generative_seo_kit.crawlers`; do not duplicate `nowo_seo_kit.robots.groups` unless you need extra host-specific agents.

## Citation providers

YAML `citations` are merged first. Host apps add CMS, docs, or blog URLs with a tagged provider (do not scrape SeoKit’s sitemap renderer):

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

The Symfony 8 demo ships `App\Citation\StaticPagesCitationProvider` as a copy-paste pattern.

## Named-route citations

Prefer `citation_routes` when the canonical URL is a Symfony route (absolute URLs via `framework.router.default_uri` in CLI):

```yaml
nowo_generative_seo_kit:
    citation_routes:
        - route: app_home
          title: Home
          notes: 'Product overview'
```

Missing routes are skipped. Duplicate URLs already listed in `citations` are skipped.

## Audit CLI

```bash
php bin/console nowo:generative-seo:audit
php bin/console nowo:generative-seo:audit --lenient
```

Fails when llms.txt is disabled, citations are empty, or no crawler groups are emitted.

Use **non-lenient** on production-like config. `--lenient` is for local/dev warn-only.

## Host CI

Example GitHub Actions step after the app is installed (set `framework.router.default_uri` so route citations resolve):

```yaml
      - name: Generative SEO audit
        run: php bin/console nowo:generative-seo:audit
```

Demo equivalent: `make -C demo/symfony8 audit` (also runs from `make -C demo release-check`).

## Demo

```bash
make -C demo up-symfony8
```

Default URL: `http://localhost:8070`.
