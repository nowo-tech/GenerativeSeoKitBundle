# Server cookbook

`llms.txt` is served by Symfony (same pattern as SeoKit sitemap/robots). Point the web server at `public/index.php` / FrankenPHP `php_server`. Do not place a static `llms.txt` in `public/` that would shadow the route.

Typical checks:

```bash
curl -sI https://your-host/llms.txt
curl -sI https://your-host/robots.txt
```

FrankenPHP demos: [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md).
