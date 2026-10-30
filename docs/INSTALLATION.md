# Installation

## Table of contents

- [Requirements](#requirements)
- [Composer](#composer)
- [Register the bundle](#register-the-bundle)
- [Routes](#routes)
- [Flex recipe](#flex-recipe)
- [Docker development (bundle contributors)](#docker-development-bundle-contributors)

## Requirements

- PHP >= 8.2, < 8.6
- Symfony **7.4+** or **8.x**
- `nowo-tech/seo-kit-bundle` ^1.11 (registers `GeoRobotsGroupsProviderInterface` and serves `robots.txt`)

## Composer

```bash
composer require nowo-tech/generative-seo-kit-bundle
```

This package **requires** SeoKit. Do not extract crawler groups out of SeoKit; this bundle implements the existing provider tag.

## Register the bundle

Symfony Flex registers the bundles automatically. Manual registration:

```php
// config/bundles.php
Nowo\FormKitBundle\NowoFormKitBundle::class => ['all' => true],
Nowo\SeoKitBundle\SeoKitBundle::class => ['all' => true],
Nowo\GenerativeSeoKitBundle\GenerativeSeoKitBundle::class => ['all' => true],
```

FormKit is pulled by SeoKit.

## Routes

Import bundle routes (Flex recipe creates `config/routes/nowo_generative_seo_kit.yaml`):

```yaml
nowo_generative_seo_kit:
    resource: .
    type: nowo_generative_seo_kit
```

SeoKit routes (`type: nowo_seo_kit`) remain required for `/robots.txt` and `/sitemap.xml`.

The routes are declared with `#[Route]` attributes on `LlmsTxtController` (an invokable controller service, not autoconfigured): `nowo_generative_seo_kit_llms` (`/llms.txt`), `nowo_generative_seo_kit_llms_well_known` (`/.well-known/llms.txt`), `nowo_generative_seo_kit_llms_full` (`/llms-full.txt`) and `nowo_generative_seo_kit_llms_full_well_known` (`/.well-known/llms-full.txt`). The `nowo_generative_seo_kit` route type (`Routing\LlmsTxtRouteLoader`) is the thin import for those attributes: it applies the configured paths (`llms.path`, `llms.well_known_path`, `llms.full_path`, `llms.full_well_known_path`) and leaves out the routes that are disabled (`enabled` / `llms.enabled` false, an empty well-known path, `llms.full_enabled` false) or whose path collides with another one. Import the bundle through this route type, not through `type: attribute` on the bundle's `src/Controller/` (that would register every route on its default path, ignoring the configuration). The controller is not autoconfigured, so the Symfony >= 7.3 default `controllers: { resource: routing.controllers }` import does not register these routes either. Route names stay stable across releases; check them with `php bin/console debug:router | grep nowo_generative_seo_kit`.

## Flex recipe

See `.symfony/recipe/nowo-tech/generative-seo-kit-bundle/1.0.0/` in this repository. Until the recipe is published on [symfony/recipes-contrib](https://github.com/symfony/recipes-contrib), copy those files into the host app.

## Docker development (bundle contributors)

```bash
make up
make test
```

Root compose uses a single PHP CLI service and a `coverage-data` volume (REQ-DOC-001). Demos live under `demo/symfony8`.
