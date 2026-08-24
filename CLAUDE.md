# CLAUDE.md — mrdemonwolf-wp-plugin

Bun + Turborepo monorepo holding one WordPress plugin and its documentation site.

```
apps/plugin   The WordPress plugin (PHP). Not published to npm; it is a bun workspace
              only so its Tailwind stylesheet can be rebuilt.
apps/docs     Fumadocs site, static-exported to GitHub Pages.
```

## The merge, and the rules that come from it

This plugin is PackRelay and TailSignal merged into one. Both upstream repos are **archived**.
Their code lives under `apps/plugin/modules/forms/` and `apps/plugin/modules/push/`.

**As of 2.0.0 everything is renamed onto a single `mrdw` namespace.** 1.4.0 deliberately preserved
the predecessors' identifiers so the merge was a drop-in replacement; that constraint is gone.

Current naming, which new code must follow:

| Thing       | Forms                             | Push                  | Shared         |
| ----------- | --------------------------------- | --------------------- | -------------- |
| Classes     | `MRDW_Forms_*`                    | `MRDW_Push_*`         | `MRDW_*`       |
| Options     | `mrdw_forms_*`                    | `mrdw_push_*`         | `mrdw_modules` |
| Tables      | `{prefix}mrdw_forms_*`            | `{prefix}mrdw_push_*` | —              |
| Page slugs  | `mrdw-form-entries`, `mrdw-forms` | `mrdw-push-*`         | parent `mrdw`  |
| REST        | `mrdw/v1`                         | `mrdw/v1`             | —              |
| Capability  | —                                 | —                     | `mrdw_manage`  |
| Text domain | —                                 | —                     | `mrdw`         |

**The two modules share one REST namespace.** Before adding a route, check it does not collide with
the other module's. Current routes: `/submit/{form_id}`, `/forms/{form_id}/fields` (Forms);
`/register`, `/register/status`, `/send`, `/stats`, `/devices/export`, `/devices/import` (Push).

`MRDW_FORMS_DIR` / `MRDW_PUSH_DIR` point at the module roots. There is no longer any legacy-constant
mapping; do not reintroduce one.

The only places PackRelay and TailSignal are still named are where they refer to the _historical
plugins_: the conflict guard's display names, its tests, and the migration docs. Those are correct
and must not be renamed.

## Layout

```
apps/plugin/
  mrdemonwolf.php            Bootstrap: constants, conflict guard, module load/run split
  uninstall.php              Removes both modules' data
  includes/
    class-mrdw-modules.php   mrdw_modules option, is_enabled(), sanitize()
    class-mrdw-secrets.php   Constant-first secret resolution
    class-mrdw-conflict.php  Predecessor-still-active guard
    class-mrdw-updater.php   plugin-update-checker, stable/nightly channel
    class-mrdw-admin.php     Top-level menu + module settings screen
  modules/forms/             Was PackRelay; classes MRDW_Forms_*
  modules/push/              Was TailSignal; classes MRDW_Push_*
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

PHPUnit 10 requires **filename == class name**. The Push suite's files are named
`Test_MRDW_Push_Foo.php` for this reason; keep that convention.

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
`important: '#mrdw-push-app'` — so it cannot leak into wp-admin. Rebuild with `bun run plugin:css`
after editing any file listed in `apps/plugin/tailwind.config.js`.

Note the two Tailwind majors: v3 for the plugin, v4 for the docs. `bunfig.toml` sets
`linker = "isolated"`, which is what keeps them apart. Do not switch to the hoisted linker.

## Known debt

- `modules/push/admin/partials/dashboard.php` reads row properties without null coalescing, which
  surfaces as PHPUnit warnings when the partial is rendered with an incomplete fixture.
- ~88 cosmetic phpcs violations remain in `modules/`, mostly missing docblocks and non-snake_case
  variables. Nothing security-related is silenced; see `phpcs.xml.dist` for the reasoning.

## Push transport

`MRDW_Push_Expo` talks to Expo directly over `wp_remote_post()`. There is no SDK. Expo's push
service is two unauthenticated JSON endpoints, and the PHP SDK that used to be here never exposed
`richContent` — the field Expo documents for notification images.

Hard limits, enforced in the class:

- **100 messages per request** (`PUSH_CHUNK_SIZE`) and **1000 ticket IDs per receipts request**
  (`RECEIPT_CHUNK_SIZE`). Expo rejects anything larger.
- Rich images need **both** `richContent: { image }` **and** `mutableContent: true`. Without
  `mutableContent`, APNs never invokes the app's Notification Service Extension and iOS silently
  shows a plain notification.
- Image URLs must be HTTPS and publicly reachable. `validate_image_url()` enforces this on all
  three send paths, because Expo reports nothing when it cannot fetch an image — the notification
  just arrives without one.

iOS rich images additionally require a Notification Service Extension in the app. The app has one
(`official-app/targets/notification-service/`); Android needs no app changes at all.

## Docs site

Themed from `~/Developer/mrdemonwolf/wolfwave/apps/docs`, same Fumadocs stack.

**`bun run start` must serve the export under `/mrdemonwolf-wp-plugin/`.** The production build
sets that base path, so serving `out/` at the root 404s every asset and renders as unstyled HTML.
The script symlinks it into `.preview/` to get the prefix right — do not "simplify" it back.

The landing page visuals in `apps/docs/app/(home)/_mocks/` are CSS/SVG, deliberately not
screenshots, so they cannot drift out of date with the plugin's actual UI.

`ClaudePrompt` prompts must stand alone: whoever pastes one into Claude will not have the docs page
in front of them, so each prompt restates the goal, the screens involved, and the failure modes.
