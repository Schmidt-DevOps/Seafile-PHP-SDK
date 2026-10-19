# AGENTS.md — Seafile PHP SDK

PHP library `sdo/seafile-php-sdk` (MIT, published on Packagist): thin object client for the
Seafile Web API (v2 / v2.1). `src/` is ~2,900 LoC across 26 files. Small codebase — the whole
surface is readable, no hidden layers to discover.

## Source of truth

No `specs/` dir in this repo. Intended behaviour is written in `README.md`:

- **Seafile Web API Support Matrix** — which endpoint groups are implemented and how completely.
  Update this table when you implement or extend a resource group.
- **Issues** — known bugs (e.g. `File::upload()` `$newFilename` is a no-op). Check before
  "discovering" it; fix it and remove the entry.
- **Contributing** — 1 issue = 1 PR, unit tests expected, PHPDoc blocks expected.
- `CHANGELOG.md` — Keep-a-Changelog + SemVer. Public API changes need an entry.

## Architecture

Three layers, one job each. A call is: `Client` (transport) → `Resource\*` (endpoint group) →
`Type\*` (data shape).

- **`src/Http/Client.php`** — Guzzle wrapper. Owns transport defaults only (`http_errors`,
  headers, base config). No API knowledge here.
- **`src/Resource/*.php`** — one class per Seafile endpoint group (`Library`, `Directory`, `File`,
  `Multi`, `Account`, `Group`, `Avatar`, `ShareLinks`, `StarredFile`, `Auth`), all extend the
  abstract `Resource`. URI built as `$this->getApiBaseUrl() . '/repos/...'`; the per-class
  `public const API_VERSION` (`'2'` or `'2.1'`) selects the API prefix. `Auth` is a deliberate
  endpoint facade with no methods.
- **`src/Type/*.php`** — DTOs, all extend the abstract `Type`, except `SharedLinkPermissions`,
  which is a pure bitmask value object (`Sdo\Bitmask\AbstractBitmask`). `Type::fromArray()` hydrates
  snake_case JSON keys into declared camelCase properties via CaseHelper, silently ignoring
  unknown keys, and converts the known timestamp keys to `DateTime` (Seafile mixes seconds and
  microseconds — `Type::getDateTime()` handles both). `toArray(ARRAY_MULTI_PART)` emits the
  multipart form shape the upload endpoints need.

Rules that follow from that shape:

- **Extend, don't modify the base.** New endpoint group = new `Resource` subclass. New payload =
  new `Type` subclass. Shared helpers (`getApiBaseUrl`, `clipUri`, `getDateTime`) already exist —
  reuse them.
- **Dependency injection stays constructor-only.** `Resource::__construct(protected Client $client)`
  is how every resource gets transport. No globals, no singletons, no service locator.
- **Interfaces stay small on purpose.** `ResourceInterface` declares only `clipUri()`;
  `TypeInterface` declares the hydration contract. Do not add a method to an interface for the
  benefit of one implementation.
- **Type subclasses stay substitutable.** Keep `fromArray`/`fromJson`/`toArray`/`toJson`
  signatures and `static`/`self` returns as the base defines them; new keys go in the
  `fromArray` switch, not in a new contract.
- **Public methods are the BC surface.** This is a published library: no signature, return type,
  or behaviour change on a public method without a `CHANGELOG.md` entry and a major version bump.

## YAGNI guardrails

- Do not implement endpoint groups the Support Matrix marks "yet to be done" (`Events`,
  `Organization`) unless explicitly asked.
- No factory / manager / wrapper / interface layer around a single implementation. No DI
  container. The Guzzle config array passed to `Client` **is** the whole configuration layer.
- No new dependency for a few lines. Guzzle (transport), `marcusball/case-helper` (key casing)
  and `sdo/bitmask` (share-link permission bits) already cover the recurring needs.
- `bin/playground.php` is a developer scratch script against a live server. Do not grow it into
  a CLI app; do not treat it as API documentation.
- `Makefile` and `AGENTS.md` are local dev tooling (currently untracked). Not shipped,
  not part of the library, never referenced from `src/`.

## Conventions

- PHP `>=8.3` (`composer.json`), Rector targets PHP 8.4, CI matrix is 8.3 / 8.4 / 8.5.
- PSR-4: `Seafile\Client\` → `src/`, `Seafile\Client\Tests\Unit\` → `test/unit`,
  `Seafile\Client\Tests\Functional\` → `test/functional`.
- php-cs-fixer decides style (`@PhpCsFixer`, risky rules off, `declare_strict_types: false`).
  Do not hand-format against it, and do not flip its rules to make a diff pass.
- House style: short arrays, `#[Override]` on overriding methods, PHPDoc on every public method
  with `@param`/`@return`/`@throws` documented.
- Method shape: typed `Type` objects as parameters (`LibraryType $libraryType`); reads return
  `Type` / `Type[]` / `bool`; downloads and uploads return `ResponseInterface`.
- `composer.lock` and `vendor/` are gitignored. Never commit them; `composer install` locally.

## Tests

- **Unit tests are the gate.** Every change needs one. Extend `UnitTestCase`, annotate
  `@covers \Seafile\Client\Resource\X`, mock the client with
  `getMockedClient(new Response(200, ['Content-Type' => 'application/json'],
  file_get_contents(__DIR__ . '/../../assets/Fixture.json')))`. Response fixtures live in
  `test/assets/<TestName>_<method>.json`. `UnitTestCase::invokeMethod()` exists for non-public
  code — use sparingly.
- **Functional tests are skipped by default** and must stay that way:
  `ALLOW_LIVE_DATA_MANIPULATION_ON_TEST_SERVER=0` in `.env` / `phpunit.xml.dist`. They talk to a
  live Seafile server and `FunctionalTestCase::setUp()` calls `markTestSkipped()` when the
  credentials/env are not complete.
- Targeted run: `./vendor/bin/phpunit test/unit/Resource/FileTest.php`. Whole unit suite:
  `composer run phpunit` (functional self-skips without env).

## Live-server safety

Agents MUST NOT touch a live Seafile server unless the user explicitly asks in the current
request. "Run the tests" never implies it.

Forbidden without a prompt: setting `ALLOW_LIVE_DATA_MANIPULATION_ON_TEST_SERVER=1`; running the
functional suite against a real server; running `bin/playground.php` or any script that uses
`TEST_SERVER_AUTHORIZATION_TOKEN`; creating/deleting accounts, groups, libraries, files, share
links, or avatars on a server. The functional Account and Group tests create and delete real
server accounts; File tests upload and delete real files.

Read-only work is always fine: unit tests, PHPStan, Rector dry-run, PHPMD, cs-fixer dry-run,
`wg`-style inspection of the code, and requests against local fixtures.

Never commit `.env`, API tokens, server URLs, or library IDs. `.env` is gitignored — keep it
that way. When a live-server action is genuinely required, print the exact command and let the
user run it.

## Validation

CI (`.github/workflows/tests.yml`, `.forgejo/workflows/tests.yml`) runs this order on every push,
PHP 8.3 / 8.4 / 8.5. All six must pass before handing work over:

```bash
composer run tests_prepare      # composer install + update
composer run rector             # dry-run; apply with: composer run rector-no-dry-run
composer run phpmd
PHP_CS_FIXER_IGNORE_ENV=1 composer run php-cs-fixer   # dry-run; apply: php-cs-fixer-no-dry-run
composer run phpstan
composer run phpunit
```

- PHPStan runs at level 7, PHPMD with a ruleset — both use committed baselines
  (`phpstan-baseline.neon`, `phpmd.baseline.xml`). Never add a baseline entry to silence newly
  introduced code; fix it instead. Regenerate baselines (`composer run create-phpmd-baseline`)
  only deliberately and say so in the report.
- **Host has no `php` binary** (Docker is available). Run the gates in a container:
  `docker run --rm -v "$PWD":/app -w /app php:8.4-cli sh -c "composer install --no-interaction && composer run phpunit"`
- `.gitlab-ci.yml` is legacy (php:8.1 / 8.2 via `bin/prepare_tests.sh` + `bin/run_tests.sh`); it
  predates the `>=8.3` requirement. Do not extend it; do not trust it as the gate.

## Git safety

Never commit, stage, checkout, revert, reset, rebase, branch, tag, merge, stash, `git rm`,
`git mv`, `git clean`, or rewrite history unless the user explicitly asks in the current request.
Read-only git (`status`, `log`, `diff`, `show`, `blame`, `ls-files`) is always fine. Editing
working-tree files is allowed; leave them unstaged and report the touched files so the user
decides. If a task genuinely needs a git mutation, print the exact command instead of running it.
