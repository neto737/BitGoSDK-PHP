# Contributing

Thanks for helping out with BitGoSDK-PHP. This guide explains which branch to target, how to set up and test your changes, and how releases are made.

- **Found a bug or want a feature?** [Open an issue](https://github.com/neto737/BitGoSDK-PHP/issues/new) first, so we can agree on the approach before you write code.
- **Found a security problem?** Don't open a public issue. Follow [SECURITY.md](SECURITY.md) instead.

## Branch model

| Branch | What it's for | Target it with |
|---|---|---|
| `2.x` | 2.x maintenance: bug fixes and security fixes. Cut from `2.3.1`. | Fixes for the 2.x line |
| `main` | 3.x development. 3.0 is being written here with a new structure. | New features and everything else |
| `v3` | An unfinished 2022 rewrite, kept for reference only. It won't be merged. Once `3.0.0-alpha.1` is tagged, it's archived as the tag `v3-prototype` and deleted. | Nothing |

If a fix applies to both lines, open the pull request against `2.x`. A maintainer ports it to `main`.

### Protection rules

`main` and `2.x` are protected: no force pushes, no deletion, every change goes through a pull request, and the CI checks must pass. Fork the repository or push a topic branch, then open a pull request against the branch you're targeting.

## Local setup

You need:

- PHP 8.3 or newer for `main`, or PHP 7.4 or newer with the `curl` extension for `2.x`. A current PHP is recommended so you see deprecation notices.
- [Composer](https://getcomposer.org/).
- A [BitGo testnet](https://app.bitgo-test.com/) account and access token, if you want to run requests against the API. Never use production tokens or real funds while developing.

```sh
git clone https://github.com/<your-username>/BitGoSDK-PHP.git
cd BitGoSDK-PHP
composer install
```

`composer install` also installs the dev tools: PHPUnit, PHPStan and PHP-CS-Fixer. On `main` it also installs Rector, and a mock HTTP client and PSR-7 factories for the tests.

## Coding style

- Follow the style of the surrounding code: 4-space indentation, LF line endings, UTF-8 and a final newline. [.editorconfig](.editorconfig) sets these up for most editors.
- Code must follow [PSR-12](https://www.php-fig.org/psr/psr-12/). CI enforces it with PHP-CS-Fixer. Run `composer cs:fix` before you push. On `main`, every file also starts with `declare(strict_types=1)`.
- On `main`, CI also runs [Rector](https://getrector.com/) as a dry run with the rules in [rector.php](rector.php): PHP 8.3 syntax, dead code, code quality and type declarations. Run `composer rector:fix` to apply what it finds, then `composer cs:fix`.
- 2.x changes must not break the public API. Don't rename or remove public methods or reorder their parameters. Widening a type (for example `int` to `int|string`) is fine. Breaking changes go to 3.x.
- Code must still run on the minimum PHP version in `composer.json`. Don't use syntax or functions newer than that.
- Give every public method a docblock with a short description, `@param` lines and `@return`, like the existing methods.
- On `main`, follow the 3.0 structure: methods named after a thing (`coin()`, `wallet()`, `addresses()`) only navigate and never send a request. Methods named after an action send exactly one request (or, for `list()`, one per batch). Each action takes its params as named arguments, plus `array $extra = []` for params the SDK doesn't name yet, and returns BitGo's response as an array. Document the main fields with an open array shape such as `@return array{id: string, label?: string, ...}`.

## Running tests

CI runs these checks on every pull request. PHPUnit and `php -l` run on every supported PHP version (8.3 to 8.5 on `main`, 7.4 to 8.5 on `2.x`), and PHPUnit runs with both the lowest and the highest allowed dependencies.

1. Run the same checks locally:

   ```sh
   composer test
   composer analyse
   composer cs
   ```

   On `main`, `composer check` runs all of these plus `composer rector`.

   On `main`, PHPStan runs at level 8 with no baseline, and line coverage must stay at 100%: CI fails below it, and Codecov reports the coverage of each pull request. On `2.x`, it runs at level 6 against `phpstan-baseline.neon`. Don't add new entries to that baseline.

   Tests never touch the network. On `main`, tests extend `Tests\TestCase`, which wires the client to a mock PSR-18 client: queue responses with `respond()`, then check what was sent with `requests()`, `lastRequest()` and `assertRequest()`. Every new endpoint needs a test of its method, URL and body. On `2.x`, see the testing notes in that branch's CONTRIBUTING.md.

2. Exercise your change against BitGo **testnet**. Write a short script in `local_tests/` (which is gitignored), fill in your testnet token and wallet ID there, and run it from the repository root:

   ```sh
   mkdir -p local_tests
   php local_tests/createAddress.php
   ```

   Never commit tokens, passphrases or wallet IDs.

3. Describe in your pull request what you ran and what you saw.

## Pull requests

- Keep each pull request focused on one change.
- Add a line under `## [Unreleased]` in [CHANGELOG.md](CHANGELOG.md) for anything a user would notice. Call out behaviour changes clearly.
- Update the README or `examples/` when you change how something is used.

## Release process

This section is for maintainers. 2.x releases are tagged from `2.x`, and 3.x releases from `main`.

1. **Prepare the changelog.** In a pull request, rename `## [Unreleased]` in `CHANGELOG.md` to `## [X.Y.Z] - YYYY-MM-DD`, add a new empty `## [Unreleased]` section above it, and update the compare links at the bottom of the file. Set the version constant to `X.Y.Z`, since it is sent in the `User-Agent` header: `BitGo::VERSION` in `src/BitGo.php` on `main`, or `BitGoSDK::VERSION` in `src/BitGoSDK.php` on `2.x`. Merge it once all required checks pass.
2. **Tag the release** on the merged commit. Tags use plain `X.Y.Z` with no `v` prefix, matching the existing tags.

   ```sh
   git checkout 2.x   # or main for a 3.x release
   git pull
   git tag -a X.Y.Z -m "X.Y.Z"
   git push origin X.Y.Z
   ```

   Pre-releases use `X.Y.Z-alpha.N`, `-beta.N` or `-RC.N`.
3. **Check Packagist.** The GitHub webhook tells [Packagist](https://packagist.org/packages/neto737/bitgosdk-php) about the new tag automatically. Make sure the version shows up within a few minutes. If it doesn't, click **Update** on the package page and check the webhook under the repository's **Settings → Webhooks**.
4. **Publish the GitHub Release.** Create a release from the tag, titled `X.Y.Z`, and paste that version's `CHANGELOG.md` section as the notes. Tick **Set as a pre-release** for alpha, beta and RC tags.
