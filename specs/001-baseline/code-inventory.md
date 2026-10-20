# Code inventory (001-baseline)

**Spec**: [`spec.md`](spec.md)  
**Last audited**: 2026-10-20  
**Production units**: 17 (every file tracked under `src/`; audit: `git ls-files src | wc -l`)

Every production file under `src/` (REQ-SPECKIT-001 / REQ-SPECKIT-003). Paths are relative to `src/`.

## Bundle and configuration

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `GenerativeSeoKitBundle.php` | Bundle and configuration | FR-GEO-001 |
| `DependencyInjection/Configuration.php` | Bundle and configuration | FR-GEO-001 |
| `DependencyInjection/GenerativeSeoKitExtension.php` | Bundle and configuration | FR-GEO-001 |
| `Resources/config/services.yaml` | Bundle and configuration | FR-GEO-001 |

## HTTP — llms.txt routes and controller

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `Routing/LlmsTxtRouteLoader.php` | HTTP — llms.txt routes and controller | FR-GEO-003, FR-GEO-010 |
| `Controller/LlmsTxtController.php` | HTTP — llms.txt routes and controller | FR-GEO-003, FR-GEO-010 |
| `Service/LlmsTxtGenerator.php` | HTTP — llms.txt routes and controller | FR-GEO-002, FR-GEO-009, FR-GEO-010, FR-GEO-011, FR-GEO-012 |

## Robots bridge and crawler catalog

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `Service/DefaultCrawlerCatalog.php` | Robots bridge (SeoKit) and crawler catalog | FR-GEO-004 |
| `Service/SeoKitRobotsGroupsProvider.php` | Robots bridge (SeoKit) and crawler catalog | FR-GEO-005 |

## Citations

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `Service/CitationSourceProviderInterface.php` | Citations | FR-GEO-006 |
| `Service/ConfigCitationSourceProvider.php` | Citations | FR-GEO-006 |
| `Service/RouteCitationSourceProvider.php` | Citations | FR-GEO-008 |

## Runtime SPI

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `Service/GeoRuntimeConfigInterface.php` | Runtime SPI | FR-GEO-011 |
| `Service/ConfigGeoRuntimeConfig.php` | Runtime SPI | FR-GEO-011 |
| `Service/LlmsHeaderRuntimeInterface.php` | Runtime SPI | FR-GEO-012 |

## CLI

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `Service/GenerativeSeoAuditor.php` | CLI | FR-GEO-007 |
| `Command/GenerativeSeoAuditCommand.php` | CLI | FR-GEO-007 |

## Coverage summary

| Category | Files | Mapped |
| --- | ---: | ---: |
| Bundle and configuration | 4 | 4 |
| HTTP — llms.txt routes and controller | 3 | 3 |
| Robots bridge and crawler catalog | 2 | 2 |
| Citations | 3 | 3 |
| Runtime SPI | 3 | 3 |
| CLI | 2 | 2 |
| **Total production files (`git ls-files src`)** | **17** | **17** |
