# Security

## Table of contents

- [Reporting vulnerabilities](#reporting-vulnerabilities)
- [Threat model and attack surface](#threat-model-and-attack-surface)
- [Logging](#logging)
- [Integrator guidance](#integrator-guidance)
- [Dependencies](#dependencies)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)
- [AI security audit (REQ-SEC-004)](#ai-security-audit-req-sec-004)

## Reporting vulnerabilities

Report security issues privately to **hectorfranco@nowo.tech**. Do not open public issues for sensitive reports.

See [.github/SECURITY.md](../.github/SECURITY.md) for supported versions.

## Threat model and attack surface

| Surface | Exposure | Mitigation |
|---------|----------|------------|
| `GET /llms.txt`, `GET /llms-full.txt` (`LlmsTxtController`) | Public, unauthenticated, cacheable | Content comes only from bundle configuration and route generation (no request input is echoed); served as `text/plain` with `X-Robots-Tag: noindex`; 404 when disabled |
| AI crawler groups in SeoKit `robots.txt` | Public | Advisory only (crawlers may ignore it); never an access-control layer |
| Citation routes (`citation_routes`) | URLs are generated server-side and published | Only list public pages; private / authenticated / draft URLs would be disclosed |
| `nowo:generative-seo:audit` | CLI, operators only | Read-only checks; no network calls |

Out of scope: authentication, rate limiting and caching of the llms.txt endpoints (host / reverse-proxy responsibility). The bundle makes no outbound HTTP calls and stores nothing.

## Logging

The bundle does not log: it has no error paths that handle secrets or personal data, and the llms.txt / robots output is deterministic from configuration. Failures (invalid configuration, route generation errors) surface as Symfony exceptions handled by the host's error logging. Do not put secrets or personal data in llms.txt titles, summaries, extra Markdown or citation notes — they are published verbatim.

## Integrator guidance

- Treat `citations` URLs and llms.txt title/summary/description as trusted configuration, not end-user HTML.
- Do not list private, authenticated, or draft URLs in `citations` or `citation_routes`.
- Crawler allow/disallow only affects SeoKit `robots.txt`; it is not an access-control layer.
- `llms.txt` is served with `X-Robots-Tag: noindex`.
- Keep training-oriented agents (`Google-Extended`, `CCBot`, `Applebot-Extended`, `Bytespider`, `anthropic-ai`) disallowed unless legal and product policy allow training.

## Dependencies

Run `composer audit` in consuming applications. This bundle requires `nowo-tech/seo-kit-bundle`.

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|--------|
| **SECURITY.md** | This document is current and linked from the README where applicable. |
| **`.gitignore` and `.env`** | `.env` and local env files are ignored; no committed secrets. |
| **No secrets in repo** | No API keys, passwords, or tokens in tracked files. |
| **Recipe / Flex** | Default recipe templates do not ship production secrets. |
| **Input / output** | Citation fields come from YAML/providers; llms.txt is plain text. |
| **Dependencies** | `composer audit` run; issues triaged. |
| **Logging** | Audit command does not print secrets. |
| **Cryptography** | Not used. |
| **Permissions / exposure** | llms.txt and optional llms-full.txt are public by design; do not cite private URLs. |
| **Limits / DoS** | Citation lists are config-bounded. |
| **AI security audit** | Grade recorded when applicable (REQ-SEC-004). |

Record confirmation in the release PR or tag notes.

## AI security audit (REQ-SEC-004)

| Field | Value |
|-------|--------|
| Date | 2026-10-06 |
| Method | Cursor / Nowo campaign static AI security review (initial scaffold) |
| Grade | **Pass (conditional)** |
| Overall risk | **Low** |
| Open Critical / High / Medium | None |
| Residual | Hosts must not put confidential URLs in citations or `citation_routes`; robots.txt is advisory |
| Notes | No session mutations (REQ-SEC-005 N/A); no admin UI |
