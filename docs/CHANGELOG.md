# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## [1.3.0] - 2026-10-07

- `GeoRuntimeConfigInterface` SPI (`isAiRobotsEnabled()`, `extraAiUserAgents()`, `llmsExtraMarkdown()`) so hosts can supply runtime GEO settings (for example from a database admin) without Doctrine in the kit.
- Default `ConfigGeoRuntimeConfig` reads the new optional `geo` YAML node (`ai_robots_enabled`, `extra_ai_user_agents`, `llms_extra_markdown`); defaults keep 1.2.x output unchanged.
- `SeoKitRobotsGroupsProvider` honours the toggle and appends extra agents (`Allow: /`, deduplicated case-insensitively against the crawler catalog).
- `LlmsTxtGenerator` renders `llmsExtraMarkdown()` after the contact line on `/llms.txt` and `/llms-full.txt`.

## [1.2.0] - 2026-10-06

- Optional llmstxt.org `llms.sections` and `llms.optional_links` on `/llms.txt` (no output change when empty).
- Opt-in `/llms-full.txt` (`llms.full_enabled`) with `## Related` pointer from the index.

## [1.1.0] - 2026-10-06

- Default crawler catalog: allow `ChatGPT-User`, `Claude-User`, `Claude-SearchBot`; disallow `Bytespider`, `anthropic-ai`.
- `citation_routes` generates llms.txt citations from named Symfony routes (`RouteCitationSourceProvider`).
- Demo: `StaticPagesCitationProvider` plus `make -C demo/symfony8 audit` / `make -C demo audit-symfony8`.
- Product roadmap: `docs/ROADMAP.md`.

## [1.0.0] - 2026-10-06

- Initial release: AI crawler policy via SeoKit `GeoRobotsGroupsProviderInterface`, `/llms.txt` + `/.well-known/llms.txt`, YAML/tagged citations, `nowo:generative-seo:audit`.
