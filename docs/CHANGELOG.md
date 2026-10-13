# Changelog

All notable changes to this project are documented in this file.

## Unreleased

## [1.5.0] - 2026-10-13

### Fixed

- `llms.txt`: the title and summary (runtime `LlmsHeaderRuntimeInterface` values and YAML `llms.title` / `llms.summary`) have newlines and runs of whitespace collapsed to a single space, so a multi-line value can no longer inject its own Markdown sections or links.
- `llms.txt`: the "Related → llms-full.txt" link is only emitted when the llms-full route is actually registered. When `full_path` collides with `path` / `well_known_path` the route loader skips it; the link now points to `full_well_known_path` if that route exists, or is omitted.

### Changed

- CI: the test matrix now includes PHP 8.5 (Symfony 7.4, 8.0, 8.1), matching the `php: >=8.2 <8.6` constraint.

## [1.4.1] - 2026-10-13

### Changed

- Development: `composer.json` pins `config.platform.php` to 8.2.0 so the committed lock stays installable on the minimum PHP; CI overrides the platform per matrix cell.
- Repository: `.twig-cs-fixer.cache` is now git-ignored.

### Dependencies

- `nowo-tech/seo-kit-bundle` 1.11.1 → 1.12.0 (development lock; #1).

## [1.4.0] - 2026-10-09

### Added

- `LlmsHeaderRuntimeInterface` (optional, next to `GeoRuntimeConfigInterface`): when the runtime service implements it, `llmsTitle()` / `llmsSummary()` replace the configured `llms.title` / `llms.summary` (blank keeps the YAML value), so hosts can drive them from a database admin.
- Specs: FR-GEO-011 (runtime GEO SPI) and FR-GEO-012 (runtime llms header) with code-inventory rows.

### Dependencies

- `nowo-tech/form-kit-bundle` 2.6.0 (lockfile); dev tooling `phpstan/phpstan` 2.3.1, `phpstan/phpstan-phpunit` 2.1.1, `nowo-tech/phpstan-frankenphp` 1.2.3.

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
