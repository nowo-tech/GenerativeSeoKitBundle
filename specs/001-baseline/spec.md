# spec.md

**Feature**: 001-baseline  
**Date**: 2026-10-06  
**Input**: Baseline specification for Generative Engine Optimization kit (AI crawlers, llms.txt, citations, audit) on top of SeoKitBundle.

## User Scenarios & Testing

### User Story 1 — llms.txt for models (Priority: P1)

As an integrator, I expose `/llms.txt` (and `/.well-known/llms.txt`) with a title, summary, and citation URLs so generative engines can discover citable pages.

**Independent Test**: Request `/llms.txt` and receive `text/plain` containing the configured title and citations.

### User Story 2 — AI crawler robots policy (Priority: P1)

As an integrator, I declare allow/disallow groups for GPTBot and related agents without editing SeoKit's robots renderer.

**Independent Test**: With SeoKit indexable, `/robots.txt` contains extra User-agent blocks from `nowo_generative_seo_kit.crawlers`.

### User Story 3 — Audit CLI (Priority: P2)

As an integrator, I run `nowo:generative-seo:audit` in CI and fail when citations are missing or llms.txt is disabled.

**Independent Test**: Empty citations → command failure unless `--lenient`.

### User Story 4 — Named-route citations (Priority: P2)

As an integrator, I list Symfony route names under `citation_routes` so llms.txt citations stay in sync with the router (absolute URLs).

**Independent Test**: Configured `app_home` appears as an absolute URL in `/llms.txt`; a missing route name is skipped.

### User Story 5 — llmstxt.org sections and related file (Priority: P3)

As an integrator, I add extra `##` sections and an optional `/llms-full.txt` without changing the 1.x llms.txt body when those keys stay at defaults.

**Independent Test**: Empty `sections` / `full_enabled: false` → same index shape as 1.1.0; `full_enabled: true` → `/llms-full.txt` is `text/plain` and the index lists it under `## Related`.

## Functional requirements

| ID | Requirement |
| --- | --- |
| FR-GEO-001 | Master `enabled` switch disables llms routes and robots bridge |
| FR-GEO-002 | `LlmsTxtGenerator` emits markdown-ish plain text with optional summary, description, contact, citations |
| FR-GEO-003 | Routes `/llms.txt` and configurable well-known path via `type: nowo_generative_seo_kit` |
| FR-GEO-004 | Default crawler catalog allows inference/search bots (including ChatGPT-User, Claude-User, Claude-SearchBot) and disallows training-oriented agents (including Bytespider, anthropic-ai) |
| FR-GEO-005 | `SeoKitRobotsGroupsProvider` implements SeoKit `GeoRobotsGroupsProviderInterface` |
| FR-GEO-006 | YAML citations plus tagged `CitationSourceProviderInterface` (URL de-dup) |
| FR-GEO-007 | `nowo:generative-seo:audit` reports missing llms, citations, or crawler groups |
| FR-GEO-008 | `citation_routes` emit absolute citation URLs via the Symfony router; missing routes are skipped |
| FR-GEO-009 | Optional `llms.sections` and `llms.optional_links` append llmstxt.org-style `##` blocks without changing output when empty |
| FR-GEO-010 | Opt-in `llms-full.txt` (`full_enabled`) registers a related file; index output is unchanged when disabled |
| FR-GEO-011 | `GeoRuntimeConfigInterface` SPI (default `ConfigGeoRuntimeConfig` reading the `geo` node) supplies the AI robots toggle, extra AI User-agents and extra llms Markdown at runtime; hosts override the service alias |
| FR-GEO-012 | When the runtime service also implements optional `LlmsHeaderRuntimeInterface`, non-blank `llmsTitle()` / `llmsSummary()` replace `llms.title` / `llms.summary`; null or blank keeps the YAML value |
