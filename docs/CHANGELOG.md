# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## [1.1.0] - 2026-10-06

- Default crawler catalog: allow `ChatGPT-User`, `Claude-User`, `Claude-SearchBot`; disallow `Bytespider`, `anthropic-ai`.
- `citation_routes` generates llms.txt citations from named Symfony routes (`RouteCitationSourceProvider`).
- Demo: `StaticPagesCitationProvider` plus `make -C demo/symfony8 audit` / `make -C demo audit-symfony8`.
- Product roadmap: `docs/ROADMAP.md`.

## [1.0.0] - 2026-10-06

- Initial release: AI crawler policy via SeoKit `GeoRobotsGroupsProviderInterface`, `/llms.txt` + `/.well-known/llms.txt`, YAML/tagged citations, `nowo:generative-seo:audit`.
