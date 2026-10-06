# Demo with FrankenPHP

## Table of contents

- [Start](#start)
- [What it shows](#what-it-shows)
- [Worker vs classic](#worker-vs-classic)
- [Playwright](#playwright)

## Start

```bash
make -C demo up-symfony8
```

Default `PORT` is **8070** (`demo/symfony8/.env.example`). Compose name: `generative-seo-kit-bundle-demo-symfony-8`.

The PHP image is FrankenPHP (latest PHP on Symfony 8.1 lock via `extra.symfony.require`). DNS `8.8.8.8` / `8.8.4.4` is set so Composer can resolve Packagist (REQ-DEMO-009).

## What it shows

- Home page with links to `/llms.txt`, `/.well-known/llms.txt`, `/robots.txt`, `/sitemap.xml`
- SeoKit + GenerativeSeoKit both registered
- WebProfiler, DebugBundle, Twig Inspector in `dev`

## Worker vs classic

`FRANKENPHP_MODE` in `.env` / `.env.example`: `worker` (default) or `classic`. Recreate containers after changing (`docker compose up -d`). The entrypoint copies `Caddyfile` or `Caddyfile.dev` accordingly (REQ-DEMO-010).

## Playwright

```bash
make -C demo/symfony8 test-e2e
make -C demo/symfony8 demo-screenshots
```

Screenshots land in `docs/images/demo/` (REQ-DEMO-013).
