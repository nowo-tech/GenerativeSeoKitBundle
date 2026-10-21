# Upgrading

## Table of contents

- [Unreleased](#unreleased)
- [To 1.5.1](#to-151)
- [To 1.5.0](#to-150)
- [To 1.4.1](#to-141)
- [To 1.4.0](#to-140)
- [To 1.3.0](#to-130)
- [To 1.2.0](#to-120)
- [Supported lines](#supported-lines)
- [To 1.1.0](#to-110)
- [To 1.0.0](#to-100)

## Unreleased

## To 1.5.1

From **1.5.0**:

```bash
composer update nowo-tech/generative-seo-kit-bundle
```

## To 1.5.0

From **1.4.1** — single-line llms.txt title/summary; Related link only for a registered full route; PHP 8.5 in CI.

```bash
composer update nowo-tech/generative-seo-kit-bundle
```

No configuration changes. `llms.txt` output can differ in two cases: a multi-line `llms.title` / `llms.summary` (YAML or runtime) is now rendered on one line, and the "Related" link to llms-full.txt disappears when `full_path` collides with `path` / `well_known_path` (that route was never registered, so the link was a 404) unless `full_well_known_path` provides the route.

## To 1.4.1

From **1.4.0** — dependency refresh and repository maintenance.

```bash
composer update nowo-tech/generative-seo-kit-bundle
```

- No breaking changes. No application upgrade steps.

## To 1.4.0

From **1.3.0** — additive, no breaking changes.

```bash
composer update nowo-tech/generative-seo-kit-bundle
```

- New optional `LlmsHeaderRuntimeInterface` (`llmsTitle(): ?string`, `llmsSummary(): ?string`). If the service behind your `GeoRuntimeConfigInterface` alias implements it, non-blank values replace `llms.title` / `llms.summary` in `/llms.txt` and `/llms-full.txt`. Without it, output is unchanged. See [USAGE.md](USAGE.md#runtime-geo-settings-spi).

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
| 1.4.x | >= 8.2, < 8.6 | 7.4 / 8.x | ^1.11 |
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
