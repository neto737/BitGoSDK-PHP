# Contributing

Thanks for helping out with BitGoSDK-PHP. This guide explains which branch to target, how to set up and test your changes, and how releases are made.

- **Found a bug or want a feature?** [Open an issue](https://github.com/neto737/BitGoSDK-PHP/issues/new) first, so we can agree on the approach before you write code.
- **Found a security problem?** Don't open a public issue. Follow [SECURITY.md](SECURITY.md) instead.

## Branch model

### Now: 2.x on `main`

| Branch | What it's for |
|---|---|
| `main` | 2.x development. All fixes and features for the 2.x line go here. |
| `v3` | An unfinished 2022 rewrite, kept for reference only. 3.0 doesn't build on it and it won't be merged (see below). |

Open your pull request against `main`.

### After 3.0 work starts

When work on 3.0 begins:

1. A `2.x` maintenance branch is cut from `main` at the latest 2.3.x tag.
2. `main` becomes the 3.x development branch, and 3.0 is written there with a new structure.
3. Once `3.0.0-alpha.1` is tagged, the `v3` branch is archived as the tag `v3-prototype` and deleted.

From then on:

| Branch | What it's for | Target it with |
|---|---|---|
| `2.x` | 2.x maintenance: bug fixes and security fixes. | Fixes for the 2.x line |
| `main` | 3.x development. | New features and everything else |

If a fix applies to both lines, open the pull request against `2.x`. A maintainer ports it to `main`.

### Protection rules

`main` (and `2.x`, once it exists) is protected: no force pushes, no deletion, and every change goes through a pull request. Fork the repository or push a topic branch, then open a pull request against the branch you're targeting.

## Local setup

You need:

- PHP 7.4 or newer with the `curl` extension. A current PHP 8.x is recommended so you see deprecation notices.
- [Composer](https://getcomposer.org/).
- A [BitGo testnet](https://app.bitgo-test.com/) account and access token, if you want to run requests against the API. Never use production tokens or real funds while developing.

```sh
git clone https://github.com/<your-username>/BitGoSDK-PHP.git
cd BitGoSDK-PHP
composer install
```

`composer install` also installs the dev tools: PHPUnit, PHPStan and PHP-CS-Fixer.

## Coding style

- Follow the style of the surrounding code: 4-space indentation, LF line endings, UTF-8 and a final newline. [.editorconfig](.editorconfig) sets these up for most editors.
- Code must follow [PSR-12](https://www.php-fig.org/psr/psr-12/). CI enforces it with PHP-CS-Fixer. Run `composer cs:fix` before you push.
- 2.x changes must not break the public API. Don't rename or remove public methods or reorder their parameters. Widening a type (for example `int` to `int|string`) is fine. Breaking changes go to 3.x.
- Code must still run on the minimum PHP version in `composer.json`. Don't use syntax or functions newer than that.
- Give every public method a docblock with a short description, `@param` lines and `@return`, like the existing methods.

## Running tests

CI runs these checks on every pull request. PHPUnit and `php -l` run on PHP 7.4 to 8.5, and PHPUnit runs with both the lowest and the highest allowed dependencies.

1. Run the same checks locally:

   ```sh
   composer test
   composer analyse
   composer cs
   ```

   PHPStan runs at level 6 against a baseline (`phpstan-baseline.neon`) of the errors in the existing code. Don't add new entries to it. When a fix removes an error, regenerate the baseline with `vendor/bin/phpstan analyse --generate-baseline phpstan-baseline.neon`.

   Tests never touch the network. To check what a method sends, install `Tests\Fixtures\RecordingTransport` on the client with `RecordingTransport::on($client)`, then read the captured method, URL, headers and body. You can queue responses or cURL failures on it too. When you add a public method, add a case for it to `SdkRequestShapeTest` or `ExpressRequestShapeTest`. A guard test fails until you do.

2. Exercise your change against BitGo **testnet**. Copy the relevant script from `examples/` into `local_tests/` (which is gitignored), fill in your testnet token and wallet ID there, and run it from the repository root:

   ```sh
   mkdir -p local_tests
   cp examples/createAddress.php local_tests/
   php local_tests/createAddress.php
   ```

   Never commit tokens, passphrases or wallet IDs.

3. Describe in your pull request what you ran and what you saw.

## Pull requests

- Keep each pull request focused on one change.
- Add a line under `## [Unreleased]` in [CHANGELOG.md](CHANGELOG.md) for anything a user would notice. Call out behaviour changes clearly.
- Update the README or `examples/` when you change how something is used.

## Release process

This section is for maintainers. Releases are tagged from `main` for now, and from `2.x` for 2.x releases once that branch exists.

1. **Prepare the changelog.** In a pull request, rename `## [Unreleased]` in `CHANGELOG.md` to `## [X.Y.Z] - YYYY-MM-DD`, add a new empty `## [Unreleased]` section above it, and update the compare links at the bottom of the file. Set `BitGoSDK::VERSION` in `src/BitGoSDK.php` to `X.Y.Z`, since it is sent in the `User-Agent` header. Merge it once all required checks pass.
2. **Tag the release** on the merged commit. Tags use plain `X.Y.Z` with no `v` prefix, matching the existing tags.

   ```sh
   git checkout main
   git pull
   git tag -a X.Y.Z -m "X.Y.Z"
   git push origin X.Y.Z
   ```

   Pre-releases use `X.Y.Z-alpha.N`, `-beta.N` or `-RC.N`.
3. **Check Packagist.** The GitHub webhook tells [Packagist](https://packagist.org/packages/neto737/bitgosdk-php) about the new tag automatically. Make sure the version shows up within a few minutes. If it doesn't, click **Update** on the package page and check the webhook under the repository's **Settings → Webhooks**.
4. **Publish the GitHub Release.** Create a release from the tag, titled `X.Y.Z`, and paste that version's `CHANGELOG.md` section as the notes. Tick **Set as a pre-release** for alpha, beta and RC tags.
