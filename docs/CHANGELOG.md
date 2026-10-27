# Changelog

All notable changes to this project are documented in this file.

## Table of contents

- [[Unreleased]](#unreleased)
- [[1.6.0] - 2026-10-27](#160---2026-10-27)
- [[1.5.1] - 2026-10-21](#151---2026-10-21)
- [[1.5.0] - 2026-10-13](#150---2026-10-13)
- [[1.4.1] - 2026-10-13](#141---2026-10-13)
- [[1.4.0] - 2026-10-09](#140---2026-10-09)
- [[1.3.0] - 2026-10-07](#130---2026-10-07)
- [[1.2.0] - 2026-10-06](#120---2026-10-06)
- [[1.1.0] - 2026-10-06](#110---2026-10-06)
- [[1.0.0] - 2026-10-06](#100---2026-10-06)

## [Unreleased]

## [1.6.0] - 2026-10-27

Attribute-declared llms.txt routes (REQ-SF-004), native PHPUnit deprecation gate (REQ-SF-005), 100% coverage gate See [UPGRADING](UPGRADING.md#to-160).

### Changed

- `LlmsTxtController` declares its four routes with `#[Route]` attributes and `#[AsController]`; the `nowo_generative_seo_kit` route loader now builds the routes from those attributes and only applies the configured paths / drops disabled routes. Route names, paths, methods and the route import are unchanged (REQ-SF-004).
- PHPUnit fails on any deprecation, including Symfony's silenced `trigger_deprecation()` calls (`failOnDeprecation`, `ignoreSuppressionOfDeprecations`, `restrictDeprecations="false"`); `SYMFONY_DEPRECATIONS_HELPER=max[direct]=0` set in `phpunit.xml.dist` and the CI test jobs; `phpunit/phpunit` floor raised to `^10.5` (REQ-SF-005).
- Coverage gates at 100% PHP lines: CI `Code Coverage` job (was 75%) and `make test-coverage` (`.scripts/php-coverage-percent.sh`, override with `PHP_COVERAGE_MIN`) now exit non-zero below it (REQ-TEST-003).

## [1.5.1] - 2026-10-21

Wave-2 REQ compliance: documented public API, full release-check chain with demo smoke, demo on PHP 8.5, security threat model. See [UPGRADING](UPGRADING.md#to-151).

### Changed

- PHPDoc description on every public method of `src/` (REQ-CS-001).
- Makefile: `release-check` runs `check-open-prs` and no longer swallows demo failures; new `demo-smoke` target and `.github/workflows/demo-smoke.yml` (REQ-MAKE-002, REQ-REL-003, REQ-TEST-011).
- Demo: FrankenPHP image `1-php8.5-alpine`, `php >=8.4.1` (Symfony 8.1 floor); entrypoint waits for `vendor/` so worker mode boots on a clean checkout; PHP-version policy and smoke check documented (REQ-DEMO-010/012).
- `docs/SECURITY.md`: threat model / attack surface and logging sections (REQ-SEC-001); INSTALLATION explains why routes come from a loader (REQ-SF-004).
- `composer.json` branch-alias `1.5.x-dev`; Rector capped at 2 parallel workers (REQ-PKG-004, REQ-CS-003); `.scripts/check-no-cursor-coauthor.sh` accepts git worktrees (REQ-GIT-001).
- Dependencies refreshed (Composer and pnpm/npm lock files of the bundle and its demos, campaign 2026-10-19).
- `.php-cs-fixer.dist.php`: `new ParallelConfig(2)` (REQ-CS-001).
- Documentation: wave-1 REQ compliance — README section order (Version information and Demos before Documentation), documentation list order (Roadmap last, PSR under Additional documentation), GitHub stars badge, canonical issue templates, CONTRIBUTING Cursor Agent queue settings, post-commit co-author check in RELEASE/CONTRIBUTING, TOCs, DEMO-FRANKENPHP dev/prod start and troubleshooting, demo production `Caddyfile` worker block enabled (`worker { file; watch }`), spec-driven development rewrite (user stories, scope, non-goals, REQ table, Engram), full GitHub Spec Kit manual, baseline spec (US-*, grouped FR-*, SC-*, validation) and categorized inventory (17/17).

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
