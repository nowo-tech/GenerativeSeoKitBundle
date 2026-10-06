# Upgrading

## Unreleased

## To 1.0.0

Initial public API. Require SeoKit `^1.11` and import routes:

```yaml
nowo_generative_seo_kit:
    resource: .
    type: nowo_generative_seo_kit
```

No migration from SeoKit YAML: keep `nowo_seo_kit` as-is. Move AI User-agent lists from `nowo_seo_kit.robots.groups` into `nowo_generative_seo_kit.crawlers` if you previously configured GPTBot there, to avoid duplicate groups.
