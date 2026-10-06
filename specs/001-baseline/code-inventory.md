# Code inventory (001-baseline)

Every production file under `src/` (REQ-SPECKIT-001 / REQ-SPECKIT-003).

| Path | Role | FR |
| --- | --- | --- |
| `GenerativeSeoKitBundle.php` | Bundle entry | FR-GEO-001 |
| `DependencyInjection/Configuration.php` | YAML tree | FR-GEO-001 |
| `DependencyInjection/GenerativeSeoKitExtension.php` | Load services + parameters | FR-GEO-001 |
| `Resources/config/services.yaml` | DI wiring | FR-GEO-001 |
| `Service/DefaultCrawlerCatalog.php` | Default AI user-agents | FR-GEO-004 |
| `Service/CitationSourceProviderInterface.php` | Host citation SPI | FR-GEO-006 |
| `Service/ConfigCitationSourceProvider.php` | YAML citations | FR-GEO-006 |
| `Service/RouteCitationSourceProvider.php` | Named-route citations | FR-GEO-008 |
| `Service/LlmsTxtGenerator.php` | llms.txt body | FR-GEO-002 |
| `Service/SeoKitRobotsGroupsProvider.php` | SeoKit robots bridge | FR-GEO-005 |
| `Service/GenerativeSeoAuditor.php` | Audit rules | FR-GEO-007 |
| `Routing/LlmsTxtRouteLoader.php` | Route type | FR-GEO-003 |
| `Controller/LlmsTxtController.php` | HTTP text/plain | FR-GEO-003 |
| `Command/GenerativeSeoAuditCommand.php` | CLI | FR-GEO-007 |
