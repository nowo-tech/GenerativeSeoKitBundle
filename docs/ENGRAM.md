# Engram

[Engram](https://github.com/gannonh/engram) provides persistent memory for AI assistants via MCP.

## Table of contents

- [Setup](#setup)
- [Usage in Cursor](#usage-in-cursor)
- [Configuration](#configuration)
- [Privacy](#privacy)
- [Relationship to spec-driven development](#relationship-to-spec-driven-development)

## Setup

1. Install Engram CLI on your machine.
2. This repository ships `.cursor/mcp.json` with the `engram` MCP server.
3. Restart Cursor or reload MCP servers after installation.

## Usage in Cursor

Once connected, the agent can store and recall project-specific context (configuration decisions, release notes, spec links) across sessions.

## Configuration

Default MCP entry:

```json
{
  "mcpServers": {
    "engram": {
      "command": "engram",
      "args": ["mcp"]
    }
  }
}
```

Adjust if your `engram` binary is not on `PATH`.

## Privacy

Do not store secrets, credentials, or customer PII in Engram memory.

## Relationship to spec-driven development

Engram covers the org-wide checklist plane (documentation hygiene, MCP, agent memory). Product behavior and local `REQ-*` traceability live in [SPEC-DRIVEN-DEVELOPMENT.md](SPEC-DRIVEN-DEVELOPMENT.md) (user stories, scope, validation commands, requirement identifiers) and in the GitHub Spec Kit baseline (`specs/001-baseline/`). Use Engram for cross-session context; use the spec-driven doc as the functional contract.
