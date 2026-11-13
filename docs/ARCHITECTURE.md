# Architecture

## Table of contents

- [Overview](#overview)
- [Ports and SPI](#ports-and-spi)
- [SeoKit bridge (optional at runtime, required by Composer until 2.0)](#seokit-bridge-optional-at-runtime-required-by-composer-until-20)
- [Standard Symfony and DDD hosts](#standard-symfony-and-ddd-hosts)
  - [Standard Symfony host](#standard-symfony-host)
  - [DDD / hexagonal host](#ddd--hexagonal-host)
- [REQ-ARCH-001 summary](#req-arch-001-summary)

## Overview

GenerativeSeoKit has no entities, no Doctrine dependency and no events. Configuration comes from the
`nowo_generative_seo_kit` tree and, at runtime, from the `GeoRuntimeConfigInterface` provider port. It serves
`/llms.txt` (and friends), contributes AI crawler groups to SeoKit's `robots.txt`, and runs the
`nowo:generative-seo:audit` command.

## Ports and SPI

All ports are plain PHP (arrays, strings, booleans); none carries a Symfony, Doctrine or HTTP type.

| Port | Default service | Purpose |
|------|-----------------|---------|
| `Service\GeoRuntimeConfigInterface` | `ConfigGeoRuntimeConfig` (YAML `geo` node) | Runtime GEO settings (AI robots toggle, extra agents, llms.txt extra Markdown) |
| `Service\LlmsHeaderRuntimeInterface` | — (optional on the same service) | Runtime llms.txt title and summary |
| `Service\CitationSourceProviderInterface` | `ConfigCitationSourceProvider`, `RouteCitationSourceProvider` | Citation rows (tag `nowo_generative_seo_kit.citation_source_provider`, autoconfigured) |
| `Service\AiRobotsGroupsProviderInterface` (1.7.0) | `CrawlerRobotsGroupsProvider` | AI crawler robots.txt groups, read by the audit and by the SeoKit bridge |

Hosts replace a single-binding port by aliasing the interface in `config/services.yaml`; citation providers are added by
tagging (or autoconfiguring) more services.

```yaml
services:
    Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface: '@App\Seo\SiteGeoRuntimeConfig'
    Nowo\GenerativeSeoKitBundle\Service\AiRobotsGroupsProviderInterface: '@App\Seo\SiteAiRobotsGroups'
```

## SeoKit bridge (optional at runtime, required by Composer until 2.0)

`SeoKitRobotsGroupsProvider` is the only class that touches SeoKit. It implements SeoKit's
`GeoRobotsGroupsProviderInterface` (tag `nowo_seo_kit.geo_robots_groups_provider`) and forwards the
`AiRobotsGroupsProviderInterface` port, so overriding the port changes what SeoKit renders in `robots.txt`.

Since 1.7.0 the extension registers the bridge only when SeoKit is installed (`interface_exists()` on SeoKit's interface).
Everything else (llms.txt, citations, runtime config, the audit) works without SeoKit. A test guards that no other class
references SeoKit.

`nowo-tech/seo-kit-bundle` **stays in `require` for all 1.x releases.** Moving it to `suggest` in a minor would let
`composer update` uninstall SeoKit from hosts that only got it through this package, while their `config/bundles.php`
and `config/routes` (as [INSTALLATION](INSTALLATION.md) asks) still reference SeoKit's bundle and route loader: their
kernel would stop booting. REQ-ARCH-001 rule 8 (hard dependency) is therefore **deferred to 2.0**, which moves SeoKit to
`suggest` with no code change. To be ready, hosts that use SeoKit should require it directly now:

```bash
composer require nowo-tech/seo-kit-bundle
```

## Standard Symfony and DDD hosts

### Standard Symfony host

Install the bundle (SeoKit comes with it in 1.x), import the `nowo_generative_seo_kit` route type, tune
`nowo_generative_seo_kit.crawlers` / `citations` in YAML. Nothing else is needed.

### DDD / hexagonal host

There are no entities to map and no events. A DDD host:

1. keeps its GEO settings in its own domain and exposes them through an adapter implementing
   `GeoRuntimeConfigInterface` (and optionally `LlmsHeaderRuntimeInterface`), aliased in `config/services.yaml`;
2. adds citation sources with adapters implementing `CitationSourceProviderInterface` (autoconfigured, or tagged
   `nowo_generative_seo_kit.citation_source_provider`);
3. if it owns the crawler policy itself, implements `AiRobotsGroupsProviderInterface` and aliases it; the audit and the
   SeoKit bridge then use it;
4. needs no InstanceSettings, ORM or EntityManager wiring (the bundle has none).

## REQ-ARCH-001 summary

| Rule | Status |
|------|--------|
| 1 Interfaces + VOs | Every host-facing service is behind a port (table above) |
| 2 No forced base class / trait | No entities, base classes or traits |
| 3 No framework types in ports | Ports use plain PHP types |
| 4 Events carry IDs | No events |
| 5 SPI | Runtime config, llms header, citation and robots-groups ports |
| 6 No host EM flush | No Doctrine usage |
| 7 Events after commit | No events |
| 8 Config through provider ports | Config tree + `GeoRuntimeConfigInterface`; SeoKit is optional at runtime, but stays in Composer `require` until 2.0 (see above) |
