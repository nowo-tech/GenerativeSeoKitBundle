# Generative SEO Kit Bundle — Demos

## Symfony 8 (FrankenPHP)

| Command | Description |
|---------|-------------|
| `make up-symfony8` | Start demo (default port **8070**) |
| `make down-symfony8` | Stop containers |
| `make shell-symfony8` | Shell in PHP container |
| `make update-bundle-symfony8` | Sync bundle autoload + clear cache |
| `make audit-symfony8` | `nowo:generative-seo:audit` in the demo container |
| `make release-check` | Healthcheck: `/`, `/llms.txt`, `/llms-full.txt`, `/robots.txt`, plus GEO audit |

Demo sources: [`symfony8/`](symfony8/).

Documentation: [docs/DEMO-FRANKENPHP.md](../docs/DEMO-FRANKENPHP.md).
