# Generative SEO Kit Bundle

[![CI](https://github.com/nowo-tech/GenerativeSeoKitBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/GenerativeSeoKitBundle/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/generative-seo-kit-bundle.svg?style=flat)](https://packagist.org/packages/nowo-tech/generative-seo-kit-bundle) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/generative-seo-kit-bundle.svg)](https://packagist.org/packages/nowo-tech/generative-seo-kit-bundle) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net) [![Symfony](https://img.shields.io/badge/Symfony-7.4%20%7C%208.0%20%7C%208.1%2B-000000?logo=symfony)](https://symfony.com) [![Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen)](#tests-and-coverage)

> ⭐ **Found this useful?** Give it a star on GitHub! It helps us maintain and improve the project.

Symfony **Generative Engine Optimization** kit (not geolocation): AI crawler `robots.txt` groups via [SeoKitBundle](https://github.com/nowo-tech/SeoKitBundle), `/llms.txt`, citation index, and `nowo:generative-seo:audit` for FrankenPHP, php-fpm and Nginx.

![FrankenPHP Friendly Worker Mode](docs/images/frankenphp-friendly.png)

This bundle is **FrankenPHP worker mode friendly**.

## Table of contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Documentation](#documentation)
  - [Additional documentation](#additional-documentation)
- [Version information](#version-information)
- [Demos](#demos)
- [Tests and coverage](#tests-and-coverage)
- [License](#license)

## Features

- ✅ **Depends on SeoKit** — does not fork head tags, sitemap, or canonical; implements `GeoRobotsGroupsProviderInterface`
- ✅ **AI crawler policy** — default allow for inference/search bots (GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot); default disallow for training-oriented agents (Google-Extended, Applebot-Extended, CCBot)
- ✅ **llms.txt** — `/llms.txt` and `/.well-known/llms.txt` as `text/plain`
- ✅ **Citation index** — YAML `citations` plus tagged `CitationSourceProviderInterface`
- ✅ **Audit CLI** — `nowo:generative-seo:audit` (`--lenient` for warn-only)
- ✅ **FrankenPHP-ready demo** — single-container Symfony 8 demo

**FrankenPHP:** Demos use a **single PHP service** (FrankenPHP, no nginx). With **`APP_ENV=dev`** (default), the Docker **entrypoint swaps in `Caddyfile.dev`** when `FRANKENPHP_MODE=classic`. The baked-in production `Caddyfile` can use **worker** mode (`FRANKENPHP_MODE=worker`, default); see [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md). Access the demo at `http://localhost:PORT` (see `demo/README.md` and `.env.example`).

## Installation

```bash
composer require nowo-tech/generative-seo-kit-bundle
```

Register the bundle in `config/bundles.php` (Flex does this automatically). SeoKitBundle must also be registered:

```php
Nowo\SeoKitBundle\SeoKitBundle::class => ['all' => true],
Nowo\GenerativeSeoKitBundle\GenerativeSeoKitBundle::class => ['all' => true],
```

## Requirements

- PHP >= 8.2, < 8.6
- Symfony **7.4+** or **8.x**
- `nowo-tech/seo-kit-bundle` ^1.11

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [PSR evaluation (REQ-CS-007)](docs/PSR.md)
- [Usage](docs/USAGE.md)
- [Contributing](docs/CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Changelog](docs/CHANGELOG.md)
- [Upgrading](docs/UPGRADING.md)
- [Release](docs/RELEASE.md)
- [Security](docs/SECURITY.md)
- [Engram](docs/ENGRAM.md)
- [Spec-driven development](docs/SPEC-DRIVEN-DEVELOPMENT.md)
- [GitHub Spec Kit](docs/SPEC-KIT.md)

### Additional documentation

- [Demo with FrankenPHP](docs/DEMO-FRANKENPHP.md) (includes worker mode)
- [FrankenPHP worker audit](docs/FRANKENPHP-WORKER-AUDIT.md)
- [Server cookbook (Nginx, php-fpm, FrankenPHP)](docs/SERVERS.md)
- [GitHub Actions CI requirements](docs/GITHUB_CI.md)

## Version information

| Version | PHP | Symfony | Status |
|---------|-----|---------|--------|
| 1.0.x | >= 8.2 | 7.4 – 8.1+ | Initial |

## Demos

```bash
make -C demo up-symfony8   # http://localhost:8070 (default PORT)
```

See [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

## Tests and coverage

```bash
make test
make test-coverage
```

- Tests: PHPUnit (unit + integration)
- PHP: **100%** lines (`make test-coverage`)

## License

MIT — see [LICENSE](LICENSE).
