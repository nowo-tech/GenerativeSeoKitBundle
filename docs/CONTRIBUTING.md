# Contributing

Thank you for contributing to GenerativeSeoKitBundle.

## Table of contents

- [Setup](#setup)
- [Git hygiene (REQ-GIT-001)](#git-hygiene-req-git-001)
- [Quality checks](#quality-checks)
- [Code of Conduct](#code-of-conduct)
- [Pull requests](#pull-requests)
- [Cursor Agent](#cursor-agent)
- [AI co-author trailers](#ai-co-author-trailers)

## Setup

```bash
make up
make install
make setup-hooks
```

`make setup-hooks` installs `.githooks/commit-msg`, which strips Cursor co-author trailers before commits are created.

## Git hygiene (REQ-GIT-001)

Never add `Co-authored-by: Cursor <cursoragent@cursor.com>` to commit messages.

Verify history:

```bash
make check-no-cursor-coauthor
```

If CI fails because trailers were already pushed:

```bash
make strip-cursor-coauthor-from-history
git push --force-with-lease origin main
```

See [GITHUB_CI.md](GITHUB_CI.md) and `.cursor/rules/01-git-commits.mdc`.

Maintainers run `make check-no-cursor-coauthor` **after** creating a release commit and **before** `git push` (REQ-GIT-001): an earlier `release-check` run does not cover the release commit itself.

## Quality checks

```bash
make qa
make phpstan
make igor
make test-coverage
make release-check
```

## Code of Conduct

This project follows the [Contributor Covenant](../CODE_OF_CONDUCT.md). Report issues to hectorfranco@nowo.tech.

## Pull requests

Use the GitHub PR template. Update `docs/CHANGELOG.md` for user-visible changes and `specs/001-baseline/code-inventory.md` when adding production files under `src/`.

## Cursor Agent

Contributors using Cursor Agent on this repository should enable these user-level settings (REQ-IDE-006):

1. **Submit with Ctrl + Enter** (macOS: **⌘ + Enter**) — Cursor Settings → Agents / Chat. Enter inserts a newline; Ctrl/⌘+Enter submits.
2. **Queue follow-ups** in user `settings.json` (Linux `~/.config/Cursor/User/settings.json`, Windows `%APPDATA%\Cursor\User\settings.json`, macOS `~/Library/Application Support/Cursor/User/settings.json`):

```json
{
  "cursor.composer.queueMessageDefaultBehavior": "queue"
}
```

Do not use `"steer"` as the default: it interrupts a running agent turn.

## AI co-author trailers

Do **not** add AI co-author trailers to commit messages: neither `Co-authored-by: Cursor` / `cursoragent@cursor.com` nor `Co-Authored-By: Claude …` / `noreply@anthropic.com` (REQ-GIT-001). Run `make setup-hooks` so the `commit-msg` hook strips them.
