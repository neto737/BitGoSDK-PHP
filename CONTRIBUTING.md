# Contributing

Thanks for helping out with BitGoSDK-PHP. This guide explains which branch to target and how changes get merged.

## Branch model

### Now: 2.x on `main`

| Branch | What it's for |
|---|---|
| `main` | 2.x development. All fixes and features for the 2.x line go here. |
| `v3` | The unfinished 3.0 rewrite. It gets merged into `main` when 3.0 work starts (see below). |

Open your pull request against `main`.

### After 3.0 work starts

When work on 3.0 begins:

1. A `2.x` maintenance branch is cut from `main` at the latest 2.3.x tag.
2. `v3` is merged into `main`, and `main` becomes the 3.x development branch.

From then on:

| Branch | What it's for | Target it with |
|---|---|---|
| `2.x` | 2.x maintenance: bug fixes and security fixes. | Fixes for the 2.x line |
| `main` | 3.x development. | New features and everything else |

If a fix applies to both lines, open the pull request against `2.x`. A maintainer ports it to `main`.

### Protection rules

`main` (and `2.x`, once it exists) is protected: no force pushes, no deletion, and every change goes through a pull request. Fork the repository or push a topic branch, then open a pull request against the branch you're targeting.
