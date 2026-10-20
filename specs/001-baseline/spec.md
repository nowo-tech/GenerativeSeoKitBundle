# Feature Specification: GenerativeSeoKitBundle baseline

**Feature**: 001-baseline  
**Date**: 2026-10-06 (reviewed 2026-10-20)  
**Input**: Baseline specification for Generative Engine Optimization kit (AI crawlers, llms.txt, citations, audit) on top of SeoKitBundle.

## Summary

**Package**: `nowo-tech/generative-seo-kit-bundle`  
**Configuration root**: `nowo_generative_seo_kit`  
**Related docs**: [`docs/SPEC-DRIVEN-DEVELOPMENT.md`](../../docs/SPEC-DRIVEN-DEVELOPMENT.md), [`docs/CONFIGURATION.md`](../../docs/CONFIGURATION.md), [`docs/USAGE.md`](../../docs/USAGE.md)  
**Code inventory**: [`code-inventory.md`](code-inventory.md)

The bundle guarantees integrators: `/llms.txt`, `/.well-known/llms.txt` and the opt-in `/llms-full.txt` as `text/plain`; AI crawler `robots.txt` groups contributed to SeoKitBundle; a citation index built from YAML, named routes and tagged providers; a runtime SPI for GEO settings; and the `nowo:generative-seo:audit` CLI.

## User Scenarios & Testing (`US-*`)

### US-01 — llms.txt for models (Priority: P1)

As an integrator, I expose `/llms.txt` (and `/.well-known/llms.txt`) with a title, summary, and citation URLs so generative engines can discover citable pages.

**Independent Test**: Request `/llms.txt` and receive `text/plain` containing the configured title and citations.

**Acceptance**: **Given** `llms.enabled` and a configured title, **When** `/llms.txt` is requested, **Then** the response is `text/plain` and contains the title, summary and citations.

### US-02 — AI crawler robots policy (Priority: P1)

As an integrator, I declare allow/disallow groups for GPTBot and related agents without editing SeoKit's robots renderer.

**Independent Test**: With SeoKit indexable, `/robots.txt` contains extra User-agent blocks from `nowo_generative_seo_kit.crawlers`.

**Acceptance**: **Given** SeoKit robots are indexable and `crawlers` groups are configured, **When** `/robots.txt` is rendered, **Then** it contains the extra `User-agent` allow/disallow blocks.

### US-03 — Audit CLI (Priority: P2)

As an integrator, I run `nowo:generative-seo:audit` in CI and fail when citations are missing or llms.txt is disabled.

**Independent Test**: Empty citations → command failure unless `--lenient`.

**Acceptance**: **Given** no citations, **When** `nowo:generative-seo:audit` runs, **Then** it exits non-zero unless `--lenient` is passed.

### US-04 — Named-route citations (Priority: P2)

As an integrator, I list Symfony route names under `citation_routes` so llms.txt citations stay in sync with the router (absolute URLs).

**Independent Test**: Configured `app_home` appears as an absolute URL in `/llms.txt`; a missing route name is skipped.

**Acceptance**: **Given** `citation_routes: [app_home, missing_route]`, **When** `/llms.txt` is rendered, **Then** `app_home` appears as an absolute URL and `missing_route` is skipped.

### US-05 — llmstxt.org sections and related file (Priority: P3)

As an integrator, I add extra `##` sections and an optional `/llms-full.txt` without changing the 1.x llms.txt body when those keys stay at defaults.

**Independent Test**: Empty `sections` / `full_enabled: false` → same index shape as 1.1.0; `full_enabled: true` → `/llms-full.txt` is `text/plain` and the index lists it under `## Related`.

**Acceptance**: **Given** empty `sections` and `full_enabled: false`, **When** `/llms.txt` is rendered, **Then** the body matches the 1.1.0 shape; **Given** `full_enabled: true`, **Then** `/llms-full.txt` is served and listed under `## Related`.

### US-06 — Runtime GEO settings (Priority: P3)

As an integrator, I override the AI robots toggle, extra AI User-agents and the llms header at runtime by aliasing my own `GeoRuntimeConfigInterface` service.

**Acceptance**: **Given** a host service implementing `GeoRuntimeConfigInterface` and `LlmsHeaderRuntimeInterface`, **When** it returns a non-blank title, **Then** `/llms.txt` uses it instead of `llms.title`; **When** it returns null or blank, **Then** the YAML value is kept.

## Functional requirements (`FR-GEO-*`)

### Bundle and configuration

| ID | Requirement |
| --- | --- |
| FR-GEO-001 | Master `enabled` switch disables llms routes and robots bridge |

### HTTP — llms.txt routes and controller

| ID | Requirement |
| --- | --- |
| FR-GEO-002 | `LlmsTxtGenerator` emits markdown-ish plain text with optional summary, description, contact, citations |
| FR-GEO-003 | Routes `/llms.txt` and configurable well-known path via `type: nowo_generative_seo_kit` |
| FR-GEO-009 | Optional `llms.sections` and `llms.optional_links` append llmstxt.org-style `##` blocks without changing output when empty |
| FR-GEO-010 | Opt-in `llms-full.txt` (`full_enabled`) registers a related file; index output is unchanged when disabled |

### Robots bridge (SeoKit) and crawler catalog

| ID | Requirement |
| --- | --- |
| FR-GEO-004 | Default crawler catalog allows inference/search bots (including ChatGPT-User, Claude-User, Claude-SearchBot) and disallows training-oriented agents (including Bytespider, anthropic-ai) |
| FR-GEO-005 | `SeoKitRobotsGroupsProvider` implements SeoKit `GeoRobotsGroupsProviderInterface` |

### Citations

| ID | Requirement |
| --- | --- |
| FR-GEO-006 | YAML citations plus tagged `CitationSourceProviderInterface` (URL de-dup) |
| FR-GEO-008 | `citation_routes` emit absolute citation URLs via the Symfony router; missing routes are skipped |

### Runtime SPI

| ID | Requirement |
| --- | --- |
| FR-GEO-011 | `GeoRuntimeConfigInterface` SPI (default `ConfigGeoRuntimeConfig` reading the `geo` node) supplies the AI robots toggle, extra AI User-agents and extra llms Markdown at runtime; hosts override the service alias |
| FR-GEO-012 | When the runtime service also implements optional `LlmsHeaderRuntimeInterface`, non-blank `llmsTitle()` / `llmsSummary()` replace `llms.title` / `llms.summary`; null or blank keeps the YAML value |

### CLI

| ID | Requirement |
| --- | --- |
| FR-GEO-007 | `nowo:generative-seo:audit` reports missing llms, citations, or crawler groups |

## Key entities

- **Citation**: absolute URL (plus optional title/description) published in `llms.txt`; sources are YAML `citations`, `citation_routes` and tagged `CitationSourceProviderInterface` services (URL de-duplicated).
- **Crawler group**: `User-agent` list with allow/disallow rules contributed to SeoKit `robots.txt`.
- **GEO runtime config**: `GeoRuntimeConfigInterface` values (AI robots toggle, extra User-agents, extra llms Markdown, optional header overrides).

## Success criteria (`SC-*`)

- **SC-001**: **17/17** files tracked under `src/` are mapped in [`code-inventory.md`](code-inventory.md) (audit: `git ls-files src | wc -l`).
- **SC-002**: Every `FR-GEO-*` above is cited by at least one inventory row and covered by PHPUnit tests.
- **SC-003**: `make qa` and `make release-check` pass; PHP line coverage stays at 100% (`make test-coverage`).
- **SC-004**: The demo serves `/llms.txt`, `/.well-known/llms.txt` and `/robots.txt` with HTTP 200 (`make -C demo/symfony8 test-e2e`).

## Non-goals

- Head tags, sitemaps, canonical URLs and the robots renderer (owned by SeoKitBundle).
- Geolocation / geo-targeting.
- Generating citation content or summaries with AI.
- Demo applications under `demo/` are illustrative, not Packagist API.

## Validation commands

```bash
make qa              # composer cs-check + phpstan + PHPUnit (Docker)
make test-coverage   # PHPUnit with coverage (100% lines)
make release-check   # full pre-release gate (includes check-no-cursor-coauthor, demo healthchecks)
```
