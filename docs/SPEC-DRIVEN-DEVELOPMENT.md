# Spec-driven development

In this repository, **spec-driven development** has three layers that stay in sync:

1. **GitHub Spec Kit baseline** — [`specs/001-baseline/`](../specs/001-baseline/) ([`spec.md`](../specs/001-baseline/spec.md), [`code-inventory.md`](../specs/001-baseline/code-inventory.md)), initialized with [GitHub Spec Kit](https://github.com/github/spec-kit) (`.specify/`, **Cursor Agent** skills in `.cursor/skills/speckit-*`). The inventory maps **100%** of the files under `src/`. **How to install, initialize, and use Spec Kit:** [`SPEC-KIT.md`](SPEC-KIT.md).
2. **Product behavior** — what **GenerativeSeoKitBundle** guarantees to applications that integrate it (see [`USAGE.md`](USAGE.md), [`CONFIGURATION.md`](CONFIGURATION.md), [`INSTALLATION.md`](INSTALLATION.md)). **PHPUnit** and **PHPStan** enforce the contracts in CI.
3. **Traceability anchors** — stable **`REQ-*`** identifiers in Makefiles and demos so changes to scripts and demo workflows stay discoverable from issues and PRs.

There is no separate executable spec language (for example Gherkin); Spec Kit specs, tests, and static analysis are the mechanical proof alongside this document.

## Table of contents

- [User stories](#user-stories)
- [Bundle functional scope](#bundle-functional-scope)
  - [Goal](#goal)
  - [In scope](#in-scope)
  - [Explicit non-goals](#explicit-non-goals)
- [Validating the functional spec](#validating-the-functional-spec)
- [Requirement identifiers (`REQ-*`)](#requirement-identifiers-req-)
- [Suggested workflow for contributors](#suggested-workflow-for-contributors)
- [Relationship to Engram](#relationship-to-engram)
- [GitHub Spec Kit (summary)](#github-spec-kit-summary)
- [See also](#see-also)

## User stories

| ID | Story | Integrator docs |
| --- | --- | --- |
| US-01 | As an integrator, I expose `/llms.txt` (and `/.well-known/llms.txt`) with a title, summary and citation URLs so generative engines can discover citable pages. | [USAGE — llms.txt](USAGE.md#llmstxt), [CONFIGURATION — llms](CONFIGURATION.md#llms) |
| US-02 | As an integrator, I declare allow/disallow `robots.txt` groups for AI crawlers through SeoKit without forking its robots renderer. | [USAGE — Robots bridge](USAGE.md#robots-bridge), [CONFIGURATION — crawlers](CONFIGURATION.md#crawlers) |
| US-03 | As an integrator, I run `nowo:generative-seo:audit` in CI and fail when citations are missing or llms.txt is disabled. | [USAGE — Audit CLI](USAGE.md#audit-cli) |
| US-04 | As an integrator, I list Symfony route names under `citation_routes` so citations follow the router (absolute URLs). | [USAGE — Named-route citations](USAGE.md#named-route-citations) |
| US-05 | As an integrator, I add llmstxt.org-style sections and an optional `/llms-full.txt` without changing the default index body. | [USAGE — llms-full.txt](USAGE.md#llms-fulltxt) |
| US-06 | As an integrator, I override runtime GEO settings (AI robots toggle, extra User-agents, llms header) from my own service. | [USAGE — Runtime GEO settings SPI](USAGE.md#runtime-geo-settings-spi) |

## Bundle functional scope

### Goal

Generative Engine Optimization (not geolocation) on top of [SeoKitBundle](https://github.com/nowo-tech/SeoKitBundle): AI crawler `robots.txt` policy, `llms.txt` / `llms-full.txt`, a citation index, and an audit command.

### In scope

- Configuration root `nowo_generative_seo_kit` (`enabled`, `geo`, `llms`, `crawlers`, `citations`, `citation_routes`).
- Routes `/llms.txt`, the well-known path and the opt-in `/llms-full.txt` (route type `nowo_generative_seo_kit`).
- Robots groups through SeoKit's `GeoRobotsGroupsProviderInterface`.
- Citation sources: YAML, named routes, and tagged `CitationSourceProviderInterface` services.
- Runtime SPI `GeoRuntimeConfigInterface` (+ optional `LlmsHeaderRuntimeInterface`).
- CLI `nowo:generative-seo:audit`.

### Explicit non-goals

- Head tags, sitemaps, canonical URLs and the robots renderer itself (owned by SeoKitBundle).
- Geolocation or geo-targeting features.
- Generating citation content or summaries with AI; the bundle only publishes what the host configures.
- The demo under `demo/` is illustrative and not part of the Packagist API.

## Validating the functional spec

- **`make qa`** (Docker) or **`composer qa`**: coding standard, PHPStan (FrankenPHP classic + worker rulesets), PHPUnit.
- **`make test-coverage`**: PHP line coverage (README reports **100%**).
- **`make release-check`**: full pre-release gate including `check-no-cursor-coauthor` and demo healthchecks.
- Behavior changes require tests under `tests/` and an update of [`specs/001-baseline/spec.md`](../specs/001-baseline/spec.md).

## Requirement identifiers (`REQ-*`)

| ID | Location | Meaning |
| --- | --- | --- |
| REQ-GIT-001 | `make check-no-cursor-coauthor`, `.githooks/commit-msg`, CI `git-hygiene` job | No AI co-author trailers in history |
| REQ-MAKE-007 | Root `Makefile` (`down-dev`) | Stop containers while keeping volumes |
| REQ-MAKE-008 | Root `Makefile` (`update-deps`) | Update Composer dependencies in bundle + demos |
| REQ-MAKE-010 | Root `Makefile` (Compose detection) | Prefer Compose V2, fall back to V1 |
| REQ-CS-008 | Root `Makefile` (`igor`) | Igor worker-state audit |
| REQ-DEMO-010 | `demo/symfony8/docker/entrypoint.sh` | `FRANKENPHP_MODE` selects classic vs worker Caddyfile |
| REQ-DEMO-013 | `demo/symfony8/Makefile` (`test-e2e`) | Playwright e2e and screenshots |

When scripted behavior changes, update the comment next to the existing `REQ-*` or add a new ID and cite it from PRs/issues.

## Suggested workflow for contributors

1. **Clarify** the behavior in an issue, the baseline spec, or a new `specs/00N-*` folder.
2. **Implement with tests** (`make test`, `make phpstan`).
3. **Anchor scripts and demos** with `REQ-*` comments when dev UX changes.
4. **Update Spec Kit artifacts** — `spec.md` and `code-inventory.md` when `src/` changes (see the maintainer checklist in [SPEC-KIT.md](SPEC-KIT.md)).
5. **Ship integrator docs** (`USAGE.md`, `CONFIGURATION.md`, `CHANGELOG.md`, `UPGRADING.md`) when behavior or configuration changes; validate with `make release-check`.

## Relationship to Engram

[ENGRAM.md](ENGRAM.md) documents the org-wide checklist plane (Engram MCP memory, documentation hygiene). It does **not** replace this document: the functional contract (user stories, scope, validation, `REQ-*` anchors) lives here and in `specs/001-baseline/`.

## GitHub Spec Kit (summary)

| Artifact | Path |
| --- | --- |
| Baseline spec | [`specs/001-baseline/spec.md`](../specs/001-baseline/spec.md) |
| Code inventory (100% of `src/`) | [`specs/001-baseline/code-inventory.md`](../specs/001-baseline/code-inventory.md) |
| Constitution | [`.specify/memory/constitution.md`](../.specify/memory/constitution.md) |
| Cursor skills | `.cursor/skills/speckit-*` |

Re-initialize with `specify init --here --force --integration cursor-agent --script sh`. **Full manual:** [SPEC-KIT.md](SPEC-KIT.md).

## See also

- [SPEC-KIT.md](SPEC-KIT.md) · [`specs/001-baseline/spec.md`](../specs/001-baseline/spec.md)
- [USAGE.md](USAGE.md) · [CONFIGURATION.md](CONFIGURATION.md) · [CONTRIBUTING.md](CONTRIBUTING.md) · [RELEASE.md](RELEASE.md)
- [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md) · [FRANKENPHP-WORKER-AUDIT.md](FRANKENPHP-WORKER-AUDIT.md)
