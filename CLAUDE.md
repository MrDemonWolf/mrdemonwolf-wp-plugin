# CLAUDE.md — mrdemonwolf-wp-plugin

Bun + Turborepo monorepo holding one WordPress plugin and its documentation site.

```
apps/plugin   The WordPress plugin (PHP). Not published to npm; it is a bun workspace
              only so its Tailwind stylesheet can be rebuilt.
apps/docs     Fumadocs site, static-exported to GitHub Pages.
```

## The merge, and the rules that come from it

This plugin is PackRelay and TailSignal merged into one. Both upstream repos are **archived**.
Their code lives under `apps/plugin/modules/forms/` and `apps/plugin/modules/push/` and was copied
in **verbatim**.

**Hard rules. Breaking any of these breaks the MrDemonWolf app or existing installs:**

- Do not rename PHP classes. `PackRelay_*` and `TailSignal_*` stay as they are.
- Do not change REST namespaces or route paths: `packrelay/v1`, `tailsignal/v1`.
- Do not rename options (`packrelay_settings`, `tailsignal_*`), database tables
  (`{prefix}packrelay_entries`, `{prefix}tailsignal_*`), or the `tailsignal_manage` capability.
- Do not change admin page slugs (`packrelay-entries`, `tailsignal-devices`, …).
- New first-party code is prefixed `MRDW_` / `mrdw_` and lives in `apps/plugin/includes/`.

The bootstrap maps the old constants (`PACKRELAY_PLUGIN_DIR`, `TAILSIGNAL_PLUGIN_DIR`, …) onto the
module directories, which is why the module code runs untouched. Do not remove those defines.

## Layout

```
apps/plugin/
  mrdemonwolf.php            Bootstrap: constants, conflict guard, module load/run split
  uninstall.php              Removes both modules' data
  includes/
    class-mrdw-modules.php   mrdemonwolf_modules option, is_enabled(), sanitize()
    class-mrdw-secrets.php   Constant-first secret resolution
    class-mrdw-conflict.php  Predecessor-still-active guard
    class-mrdw-updater.php   plugin-update-checker, stable/nightly channel
    class-mrdw-admin.php     Top-level menu + module settings screen
  modules/forms/             Was PackRelay
  modules/push/              Was TailSignal
  tests/forms/ tests/push/   Two separate suites, two separate bootstraps
```

`mrdw_load_*_module()` only `require`s files; `mrdw_run_*_module()` requires _and_ registers hooks.
Activation and the module-toggle handler call **load**, the `plugins_loaded` bootstrap calls **run**.
Mixing them up double-registers every hook.

## Tests

Two suites, two configs, two bootstraps — **deliberately not merged**. Both bootstraps define
`ABSPATH` and stub `WP_Error` with incompatible shapes, so a single bootstrap would break one suite.

```bash
cd apps/plugin && composer test          # both suites (417 tests)
composer test:forms                       # Forms only
composer test:push                        # Push only
```

PHPUnit 10 requires **filename == class name**. The Push suite's files were renamed from
`test-foo.php` to `Test_TailSignal_Foo.php` for this reason; keep that convention.

`tests/forms/bootstrap.php` sets `ABSPATH` to a scratch dir under the system temp. Do not point it
at the plugin directory — the WordPress stubs it writes then ship inside the release zip.

## Coding standards

```bash
cd apps/plugin && composer lint
```

Full `WordPress` ruleset. First-party code (`mrdemonwolf.php`, `uninstall.php`, `includes/`) passes
cleanly and must stay that way. `modules/` is exempted from a documented list of **cosmetic** sniffs
only — the security sniffs (escaping, sanitising, nonces, prepared SQL) are live everywhere. Each
exemption in `phpcs.xml.dist` carries the reasoning; do not add to that list without one.

Warnings do not fail the build (`ignore_warnings_on_exit`); errors do.

## Secrets

The only credential either module stores is the optional Expo token. Read it through
`MRDW_Secrets::expo_access_token()`, never `get_option()` directly — the constant
`MRDW_EXPO_ACCESS_TOKEN` must win, and `MRDW_Secrets::sanitize_expo_access_token()` blocks DB writes
while it is set. Nothing secret belongs in this repository.

## Release channels

- **Stable** — push a `v*` tag. `release.yml` verifies the tag matches the plugin header version,
  runs the suite, builds the zip and publishes it.
- **Nightly** — `nightly.yml`, daily cron. Builds from `main`, stamps the header as
  `<next-minor>-nightly.<YYYYMMDD>`, publishes a **pre-release** with a dated tag, prunes nightlies
  older than 14 days.

Nightly tags must be **dated, not rolling**. `plugin-update-checker` reads the version out of the
tag name, so a fixed `nightly` tag yields an unparseable version and updates silently stop working.

`MRDW_Updater` calls `setReleaseFilter( $callback, $api::RELEASE_FILTER_ALL )` on the nightly path.
That API is real in plugin-update-checker v5.7 but is **not** in the published docs — check
`vendor/yahnis-elsts/plugin-update-checker/Puc/v5p7/Vcs/ReleaseFilteringFeature.php` before changing it.

## Ops prerequisites

- A GitHub environment named **`nightly`** with **no required reviewers**. With reviewers set, the
  scheduled run blocks forever waiting for an approval.
- A GitHub environment named **`release`**.
- GitHub Pages enabled with the **GitHub Actions** source.

## Styling

Prefer core WordPress admin markup and classes; the General screen uses the Settings API and ships
no CSS at all. The Push module's Tailwind is scoped on purpose — `tw-` prefix, `preflight: false`,
`important: '#tailsignal-app'` — so it cannot leak into wp-admin. Rebuild with `bun run plugin:css`
after editing any file listed in `apps/plugin/tailwind.config.js`.

Note the two Tailwind majors: v3 for the plugin, v4 for the docs. `bunfig.toml` sets
`linker = "isolated"`, which is what keeps them apart. Do not switch to the hoisted linker.

## Known debt

- `modules/push/admin/partials/dashboard.php` reads row properties without null coalescing, which
  surfaces as PHPUnit warnings when the partial is rendered with an incomplete fixture.
- `ctwillie/expo-server-sdk-php` has an implicit-nullable parameter that is deprecated in PHP 8.4+
  and becomes an error in PHP 9. Upstream fix needed.
- 88 cosmetic phpcs violations remain in `modules/`, mostly missing docblocks and non-snake_case
  variables.
