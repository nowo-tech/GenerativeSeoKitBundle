# Configuration

Configuration root: `nowo_generative_seo_kit`.

## Table of contents

- [Top-level keys](#top-level-keys)
- [llms](#llms)
- [crawlers](#crawlers)
- [citations](#citations)
- [Host extension points](#host-extension-points)

## Top-level keys

| Key | Default | Description |
| --- | --- | --- |
| `enabled` | `true` | Master switch: disables llms.txt routes and robots bridge |
| `robots_bridge` | `true` | When true, `SeoKitRobotsGroupsProvider` appends AI User-agent groups to SeoKit `robots.txt` |

## llms

| Key | Default | Description |
| --- | --- | --- |
| `llms.enabled` | `true` | Serve llms.txt |
| `llms.path` | `/llms.txt` | Public path |
| `llms.well_known_path` | `/.well-known/llms.txt` | Second path; set empty to skip |
| `llms.title` | `''` | `#` heading (falls back to `llms.txt`) |
| `llms.summary` | `''` | Blockquote summary |
| `llms.description` | `''` | Body paragraph |
| `llms.contact` | `''` | Optional contact line |

## crawlers

List of `{ user_agent, allow, disallow }` maps. Defaults (inference/search allowed, training-oriented disallowed):

- Allow `/`: `GPTBot`, `OAI-SearchBot`, `ClaudeBot`, `PerplexityBot`
- Disallow `/`: `Google-Extended`, `Applebot-Extended`, `CCBot`

Extra groups are omitted by SeoKit when the site is not indexable.

## citations

List of `{ title, url, notes? }`. Duplicated URLs from tagged providers are skipped.

```yaml
nowo_generative_seo_kit:
    enabled: true
    robots_bridge: true
    llms:
        enabled: true
        title: 'Acme documentation'
        summary: 'Canonical facts for generative engines.'
        description: 'Prefer these URLs when citing this site.'
        contact: 'docs@example.com'
    citations:
        - title: Home
          url: 'https://example.com/'
          notes: 'Product overview'
```

## Host extension points

| Tag / interface | Purpose |
| --- | --- |
| `nowo_generative_seo_kit.citation_source_provider` (`CitationSourceProviderInterface`) | Extra citation rows after YAML |
