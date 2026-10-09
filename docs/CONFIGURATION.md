# Configuration

Configuration root: `nowo_generative_seo_kit`.

## Table of contents

- [Top-level keys](#top-level-keys)
- [llms](#llms)
- [crawlers](#crawlers)
- [citations](#citations)
- [citation_routes](#citation_routes)
- [Host extension points](#host-extension-points)

## Top-level keys

| Key | Default | Description |
| --- | --- | --- |
| `enabled` | `true` | Master switch: disables llms.txt routes and robots bridge |
| `robots_bridge` | `true` | When true, `SeoKitRobotsGroupsProvider` appends AI User-agent groups to SeoKit `robots.txt` |

## geo

Static defaults for the `GeoRuntimeConfigInterface` SPI (see [USAGE.md](USAGE.md#runtime-geo-settings-spi)).

| Key | Default | Description |
| --- | --- | --- |
| `geo.ai_robots_enabled` | `true` | When false, no AI crawler groups are added to SeoKit `robots.txt` |
| `geo.extra_ai_user_agents` | `[]` | Extra User-agent tokens emitted with `Allow: /` |
| `geo.llms_extra_markdown` | `''` | Markdown appended after the contact line of llms.txt / llms-full.txt |

## llms

| Key | Default | Description |
| --- | --- | --- |
| `llms.enabled` | `true` | Serve llms.txt |
| `llms.path` | `/llms.txt` | Public path |
| `llms.well_known_path` | `/.well-known/llms.txt` | Second path; set empty to skip |
| `llms.title` | `''` | `#` heading (falls back to `llms.txt`) |
| `llms.summary` | `''` | Blockquote summary |
| `llms.description` | `''` | Body paragraph |
| `llms.contact` | `''` | Optional contact line |
| `llms.sections` | `[]` | Extra `##` blocks `{ heading, body?, links? }` (llmstxt.org) |
| `llms.optional_links` | `[]` | `## Optional` link list; omitted when empty |
| `llms.full_enabled` | `false` | Serve related `llms-full.txt` |
| `llms.full_path` | `/llms-full.txt` | Public path for the full file |
| `llms.full_well_known_path` | `''` | Optional second path; empty skips |
| `llms.full_body` | `''` | Extra `## Full` paragraph on the full file only |

## crawlers

List of `{ user_agent, allow, disallow }` maps. Defaults (inference/search allowed, training-oriented disallowed):

- Allow `/`: `GPTBot`, `ChatGPT-User`, `OAI-SearchBot`, `ClaudeBot`, `Claude-User`, `Claude-SearchBot`, `PerplexityBot`
- Disallow `/`: `Google-Extended`, `Applebot-Extended`, `CCBot`, `Bytespider`, `anthropic-ai`

Extra groups are omitted by SeoKit when the site is not indexable. Override `crawlers` entirely to replace the catalog.

## citations

List of `{ title, url, notes? }`. Duplicated URLs from tagged providers are skipped.

```yaml
nowo_generative_seo_kit:
    enabled: true
    robots_bridge: true
    llms:
        enabled: true
        title: 'Acme documentation'
        summary: 'Canonical facts for generative engines.'
        description: 'Prefer these URLs when citing this site.'
        contact: 'docs@example.com'
        sections:
            - heading: Docs
              body: 'Primary documentation.'
              links:
                  - title: Handbook
                    url: 'https://example.com/docs'
                    notes: 'Start here'
        optional_links:
            - title: Changelog
              url: 'https://example.com/changelog'
        full_enabled: false
        full_path: '/llms-full.txt'
        full_body: ''
    citations:
        - title: Home
          url: 'https://example.com/'
          notes: 'Product overview'
    citation_routes:
        - route: app_home
          title: Home
          notes: 'Generated from the Symfony route'
          parameters: {}
```

## citation_routes

List of `{ route, title, notes?, parameters? }`. `RouteCitationSourceProvider` generates **absolute** URLs via the Symfony router (`framework.router.default_uri` in CLI). Missing routes are skipped. Duplicate URLs already listed in `citations` are skipped.

## Host extension points

| Tag / interface | Purpose |
| --- | --- |
| `nowo_generative_seo_kit.citation_source_provider` (`CitationSourceProviderInterface`) | Extra citation rows after YAML and `citation_routes` |
| `Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface` (service alias) | Runtime robots toggle, extra AI User-agents, extra llms Markdown (override the alias) |
| `Nowo\GenerativeSeoKitBundle\Service\LlmsHeaderRuntimeInterface` (optional, on the same service) | Runtime llms.txt title / summary; blank keeps `llms.title` / `llms.summary` |
