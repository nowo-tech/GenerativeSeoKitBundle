# Upgrading

## Unreleased

## To 1.3.0

Additive. `GeoRuntimeConfigInterface` is aliased to `ConfigGeoRuntimeConfig` (optional `geo` YAML node), so existing apps need no change. To drive the toggle, extra User-agents, or extra llms Markdown from your own storage, implement the interface and override the alias:

```yaml
services:
    App\Seo\SiteGeoRuntimeConfig: ~
    Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface: '@App\Seo\SiteGeoRuntimeConfig'
```

Behaviour notes: when `isAiRobotsEnabled()` is false the kit contributes no AI groups to SeoKit `robots.txt` (previously it was only controllable via `robots_bridge`). If your host already has a custom `GeoRobotsGroupsProviderInterface` that emits the same extra agents, remove the duplicate or let the kit own them. The `SeoKitRobotsGroupsProvider` / `LlmsTxtGenerator` constructors gained an optional trailing `?GeoRuntimeConfigInterface` argument.

## To 1.2.0

Optional `llms.sections` / `llms.optional_links` / `llms.full_*` are additive. Existing `/llms.txt` body is unchanged until you set those keys. Enabling `full_enabled` adds `/llms-full.txt` and a `## Related` block on the index.

## Supported lines

| Bundle | PHP | Symfony | SeoKit |
|--------|-----|---------|--------|
| 1.3.x | >= 8.2, < 8.6 | 7.4 / 8.x | ^1.11 |
| 1.2.x | >= 8.2, < 8.6 | 7.4 / 8.x | ^1.11 |
| 1.1.x | >= 8.2, < 8.6 | 7.4 / 8.x | ^1.11 |
| 1.0.x | >= 8.2, < 8.6 | 7.4 / 8.x | ^1.11 |

Dropping a major Symfony line will be documented here before the next minor/major of this bundle.

## To 1.1.0

Default `nowo_generative_seo_kit.crawlers` includes additional inference (`ChatGPT-User`, `Claude-User`, `Claude-SearchBot`) and training-oriented (`Bytespider`, `anthropic-ai`) groups. To restore the 1.0.0 list, set `crawlers` explicitly to GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot (allow `/`) and Google-Extended, Applebot-Extended, CCBot (disallow `/`).

Named-route citations: add `citation_routes` (see [CONFIGURATION.md](CONFIGURATION.md)). Duplicate URLs against `citations` are skipped.

## To 1.0.0

Initial public API. Require SeoKit `^1.11` and import routes:

```yaml
nowo_generative_seo_kit:
    resource: .
    type: nowo_generative_seo_kit
```

No migration from SeoKit YAML: keep `nowo_seo_kit` as-is. Move AI User-agent lists from `nowo_seo_kit.robots.groups` into `nowo_generative_seo_kit.crawlers` if you previously configured GPTBot there, to avoid duplicate groups.
