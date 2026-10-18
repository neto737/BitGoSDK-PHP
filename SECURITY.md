# Security policy

BitGoSDK-PHP handles access tokens, wallet passphrases and transaction data, so security reports are taken seriously.

## Supported versions

| Version | Supported | Notes |
|---|---|---|
| 3.x | Not released yet | In development on the `v3` branch. |
| 2.3.x / 2.4.x | ✅ | Bug fixes until 3.0.0 is released. Security fixes for 12 months after 3.0.0. |
| ≤ 2.2.x | ❌ | Upgrade to the latest 2.x release. 2.3.0 is meant as a drop-in upgrade from 2.2.x. |

Until 2.3.0 is released, fixes for vulnerabilities found in 2.2.3 ship in 2.3.0 rather than as 2.2.x patches.

## Reporting a vulnerability

**Do not open a public issue, pull request or discussion for a security problem.**

Report it privately through GitHub Security Advisories:

1. Go to [Report a vulnerability](https://github.com/neto737/BitGoSDK-PHP/security/advisories/new) (the **Security** tab, then **Report a vulnerability**).
2. Describe the problem, the affected versions, and how to reproduce it. A proof of concept helps. Use BitGo **testnet** for any reproduction, never real funds or production tokens.
3. Tell us how you'd like to be credited, if at all.

Never include real access tokens, passphrases, private keys or wallet IDs in a report.

## What happens next

- **Acknowledgement within 72 hours.** This is a best-effort target for a project with a small number of maintainers.
- We confirm the problem, assess its severity and agree on a disclosure date with you, and keep you updated until the fix ships.
- The fix is released as a patch on every supported line. A GitHub Security Advisory is published (with a CVE where appropriate), and the `CHANGELOG.md` entry links to it.
- You're credited in the advisory unless you ask not to be.

## Scope

In scope: the code in this repository, including the default TLS settings, how credentials and passphrases are passed to BitGo and BitGo Express, and anything that could cause the SDK to send funds to the wrong place or the wrong amount.

Out of scope:

- Vulnerabilities in the BitGo platform, BitGo Express or BitGoJS. Report those to BitGo directly.
- Problems that require an attacker to already control the server running your application.
- Running BitGo Express over plain HTTP on a network you don't trust. This is a known limitation of 2.2.x, and TLS support is planned for 2.3.0.
