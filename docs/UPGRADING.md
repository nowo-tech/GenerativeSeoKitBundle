# Upgrading

## Unreleased

## To 1.1.0

Default `nowo_generative_seo_kit.crawlers` includes additional inference (`ChatGPT-User`, `Claude-User`, `Claude-SearchBot`) and training-oriented (`Bytespider`, `anthropic-ai`) groups. To restore the 1.0.0 list, set `crawlers` explicitly to GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot (allow `/`) and Google-Extended, Applebot-Extended, CCBot (disallow `/`).

Named-route citations: add `citation_routes` (see [CONFIGURATION.md](CONFIGURATION.md)). Duplicate URLs against `citations` are skipped.

## To 1.0.0

Initial public API. Require SeoKit `^1.11` and import routes:

```yaml
nowo_generative_seo_kit:
    resource: .
    type: nowo_generative_seo_kit
```

No migration from SeoKit YAML: keep `nowo_seo_kit` as-is. Move AI User-agent lists from `nowo_seo_kit.robots.groups` into `nowo_generative_seo_kit.crawlers` if you previously configured GPTBot there, to avoid duplicate groups.
