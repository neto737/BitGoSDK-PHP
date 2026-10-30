# Architecture decision records

These are the design decisions behind the 3.x core on `main`, one per file. Each says what problem it answers, what was decided and what follows from it, so a change that goes against one starts from the reasons rather than from the code.

| # | Decision | Status |
|---|---|---|
| [0001](0001-navigation-and-the-wallet-handle.md) | Navigation and the wallet handle | Accepted |
| [0002](0002-arrays-with-documented-shapes.md) | Arrays with documented shapes instead of models | Accepted |
| [0003](0003-psr-18-with-no-bundled-client.md) | PSR-18 with no bundled HTTP client | Accepted |
| [0004](0004-exception-hierarchy.md) | The exception hierarchy | Accepted |
| [0005](0005-path-escaping.md) | Path escaping | Accepted |
| [0006](0006-retries-are-opt-in-and-never-post.md) | Retries are opt-in and never apply to `POST` | Accepted |

## Adding one

Write an ADR when a change sets a rule that later code has to follow, or reverses one of the decisions above. A bug fix or a new endpoint that follows the existing rules doesn't need one.

1. Copy the template below to `NNNN-short-title.md`, numbered after the last one.
2. Open it in the same pull request as the change it describes, with the status **Proposed**. It becomes **Accepted** when the pull request merges.
3. Don't rewrite an accepted ADR to change the decision. Write a new one, set the old one's status to **Superseded by [NNNN](NNNN-short-title.md)**, and add the new row to the table above.

```markdown
# NNNN. Title

- **Status:** Proposed
- **Date:** YYYY-MM-DD

## Context

What problem this answers, and what forces and constraints apply.

## Decision

What we do, stated so a reviewer can check code against it.

## Consequences

What follows, good and bad, including what it rules out.
```
