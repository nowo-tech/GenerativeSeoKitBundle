# FrankenPHP worker mode audit (kernel not reset between requests)

| Field | Value |
|-------|-------|
| Package | `nowo-tech/generative-seo-kit-bundle` (`symfony-bundle`) |
| Audited revision | `v1.6.1` (kept-alive test added 2026-11-12) |
| Audit date | 2026-11-12 (first note 2026-10, REQ-CS-008) |
| Method | Manual review of every service under `src/`, Igor (`make igor` / `composer igor`, `igor-php`, require-dev only) and PHPStan FrankenPHP rulesets, plus the kept-alive test below |
| **Verdict** | ✅ **Viable with kernel reset false** — every service is stateless; nothing needs `kernel.reset` |

## Table of contents

- [Execution model assumed](#execution-model-assumed)
- [Services reviewed](#services-reviewed)
- [Kept-alive test (REQ-RUNTIME-002)](#kept-alive-test-req-runtime-002)
- [Rules for future code](#rules-for-future-code)

## Execution model assumed

FrankenPHP worker mode boots the Symfony kernel once per worker and serves many requests with the same container. This audit assumes the **strict** variant: the kernel is **not** rebooted between requests (`FRANKENPHP_RESET_KERNEL` unset or false), so every shared service, static property and PHP global survives from one request to the next.

- **A — kernel not rebooted, `services_resetter` still runs:** services tagged `kernel.reset` are reset between requests.
- **B — no reset at all:** any per-request state kept in a service leaks into the next request.

A bundle that is safe under **B** is safe under **A** and under classic mode / PHP-FPM.

## Services reviewed

| Service | State | Reset mechanism |
|---------|-------|-----------------|
| `LlmsTxtGenerator` | `readonly`; compiled config, the runtime SPI and the citation providers | none needed |
| `ConfigCitationSourceProvider` | `readonly`; compiled config | none needed |
| `RouteCitationSourceProvider` | `readonly`; compiled config and the shared `router` (absolute URLs come from the router context, which `RouterListener` refreshes on every main request) | none needed |
| `ConfigGeoRuntimeConfig` (default `GeoRuntimeConfigInterface`) | `readonly`; compiled config | none needed |
| `SeoKitRobotsGroupsProvider`, `GenerativeSeoAuditor`, `DefaultCrawlerCatalog` | `readonly` / constant data | none needed |
| `LlmsTxtController` | `final readonly`; the request is an argument | none needed |
| `LlmsTxtRouteLoader`, `GenerativeSeoAuditCommand` | compile-time / CLI | none needed |

There is no runtime registry that must be cleared on `kernel.terminate`, no static state, no persistence and no outbound I/O. SeoKit still owns `SeoRuntime` request state; this package adds no worker-unsafe state.

## Kept-alive test (REQ-RUNTIME-002)

Test: `Nowo\GenerativeSeoKitBundle\Tests\Integration\Worker\WorkerKeptAliveKernelTest` (`tests/Integration/Worker/WorkerKeptAliveKernelTest.php`). It uses one booted kernel (`WorkerKeptAliveKernel`: FrameworkBundle, the bundle and its route import, with a route citation that renders as an absolute URL) and drives `http_kernel` `handle()` + `terminate()`; `services_resetter` / `kernel.reset` is **never** called. It runs 1,200 iterations alternating two sites (hosts) on `/llms.txt` and `/.well-known/llms.txt`, asserts every response carries only its own host (nothing of the other site), and that memory growth after a 100-iteration warm-up stays below 4 MiB.

Result: no state is carried between requests, so the bundle is **safe with kernel reset false**.

Rule 7: the bundle never clears or resets the host's default EntityManager (it has no persistence).

## Rules for future code

- Never memoize per-request data (host, locale, user) in a service property; read it from the `Request` or the router context per call.
- Any outbound client added later needs explicit timeouts (REQ-RUNTIME-001).
- Re-run this audit (and the table) whenever a service is added under `src/`.
