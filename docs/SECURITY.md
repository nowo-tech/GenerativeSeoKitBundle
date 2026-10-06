# Security

## Table of contents

- [Reporting vulnerabilities](#reporting-vulnerabilities)
- [Integrator guidance](#integrator-guidance)
- [Dependencies](#dependencies)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)
- [AI security audit (REQ-SEC-004)](#ai-security-audit-req-sec-004)

## Reporting vulnerabilities

Report security issues privately to **hectorfranco@nowo.tech**. Do not open public issues for sensitive reports.

See [.github/SECURITY.md](../.github/SECURITY.md) for supported versions.

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
| **Permissions / exposure** | llms.txt is public by design; do not cite private URLs. |
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
