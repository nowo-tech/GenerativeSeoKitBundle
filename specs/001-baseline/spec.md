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

## Functional requirements

| ID | Requirement |
| --- | --- |
| FR-GEO-001 | Master `enabled` switch disables llms routes and robots bridge |
| FR-GEO-002 | `LlmsTxtGenerator` emits markdown-ish plain text with optional summary, description, contact, citations |
| FR-GEO-003 | Routes `/llms.txt` and configurable well-known path via `type: nowo_generative_seo_kit` |
| FR-GEO-004 | Default crawler catalog allows inference/search bots and disallows training-oriented agents |
| FR-GEO-005 | `SeoKitRobotsGroupsProvider` implements SeoKit `GeoRobotsGroupsProviderInterface` |
| FR-GEO-006 | YAML citations plus tagged `CitationSourceProviderInterface` (URL de-dup) |
| FR-GEO-007 | `nowo:generative-seo:audit` reports missing llms, citations, or crawler groups |
