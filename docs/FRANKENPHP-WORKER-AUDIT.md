# FrankenPHP worker audit (REQ-CS-008)

This bundle keeps request handling **stateless**:

- Configuration is injected as arrays (`readonly` services).
- `LlmsTxtGenerator`, `SeoKitRobotsGroupsProvider`, and `GenerativeSeoAuditor` hold no request-mutable properties.
- There is no runtime registry that must be cleared on `kernel.terminate`.

Run `make igor` / `composer igor` (`igor-php`, require-dev only) against `src/`. Safe namespaces include Symfony components (see root `igor.json`).

SeoKit still owns `SeoRuntime` request state; this package does not add worker-unsafe statics.
