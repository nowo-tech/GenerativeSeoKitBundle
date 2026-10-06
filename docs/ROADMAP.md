# Roadmap

This document outlines the planned direction for Generative SEO Kit Bundle. Items are grouped by horizon and are subject to change based on feedback, crawler-policy changes, and maintainer capacity.

Shipped history lives in [CHANGELOG.md](CHANGELOG.md). Product behavior lives in [specs/001-baseline/spec.md](../specs/001-baseline/spec.md).

## Table of contents

- [Current state (v1.2.x)](#current-state-v12x)
- [Short term](#short-term)
- [Medium term](#medium-term)
- [Long term / ideas](#long-term-ideas)
- [Non-goals](#non-goals)

---

## Current state (v1.2.x)

Current tag **v1.2.0** (PHP >= 8.2, Symfony 7.4 / 8.x, SeoKit `^1.11`).

Already in the bundle:

- **SeoKit composition** — implements `GeoRobotsGroupsProviderInterface`; does not fork head tags, sitemap, canonical, or robots rendering.
- **AI crawler policy** — default allow for inference/search bots (`GPTBot`, `ChatGPT-User`, `OAI-SearchBot`, `ClaudeBot`, `Claude-User`, `Claude-SearchBot`, `PerplexityBot`); default disallow for training-oriented agents (`Google-Extended`, `Applebot-Extended`, `CCBot`, `Bytespider`, `anthropic-ai`). Configurable under `nowo_generative_seo_kit.crawlers`.
- **llms.txt** — `/llms.txt` and `/.well-known/llms.txt` as `text/plain` (`X-Robots-Tag: noindex`). Optional `sections` / `optional_links`; opt-in `/llms-full.txt`.
- **Citation index** — YAML `citations`, `citation_routes`, plus tagged `CitationSourceProviderInterface` (URL de-dup).
- **Audit CLI** — `nowo:generative-seo:audit` (`--lenient` for warn-only); demo `make -C demo/symfony8 audit`.
- **FrankenPHP worker** — request handling stays stateless; see [FRANKENPHP-WORKER-AUDIT.md](FRANKENPHP-WORKER-AUDIT.md).
- **Demo** — `demo/` FrankenPHP (see [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md)).

---

## Short term

Shipped in **v1.1.0** (see [CHANGELOG.md](CHANGELOG.md)):

- Crawler catalog hygiene (`ChatGPT-User`, `Claude-User`, `Claude-SearchBot`, `Bytespider`, `anthropic-ai`).
- Integrator docs aligned with defaults, `citation_routes`, and SeoKit robots bridge.

---

## Medium term

Shipped in **v1.1.0**:

- Host citation patterns: `citation_routes` + demo `StaticPagesCitationProvider` (no sitemap scrape).
- Audit in CI: USAGE host workflow snippet; `make -C demo/symfony8 audit` in demo `release-check`.

Further CMS adapters stay host-specific.

---

## Long term / ideas

Shipped as additive 1.x options in **v1.2.0** (see [CHANGELOG.md](CHANGELOG.md)):

- llmstxt.org extra `##` sections, `## Optional`, and related `/llms-full.txt` without changing the index when keys stay at defaults.

Still not committed:

- Further related files if the GEO ecosystem standardizes names beyond `llms-full.txt`.
- **Backward compatibility** — supported PHP/Symfony/SeoKit lines are listed in [UPGRADING.md](UPGRADING.md); document the upgrade path there when dropping a major Symfony line.

---

## Non-goals

- Replacing SeoKitBundle (title, description, canonical, sitemap, robots.txt rendering).
- Generating marketing or AI-written page copy.
- Owning a full crawler blocklist as a service (this package ships a small default catalog; hosts override YAML).
- Built-in frontend assets or a public HTML “GEO dashboard”.

---

If you want to influence the roadmap, open an issue or a discussion in the project repository.

**Last updated:** 2026-10-06
