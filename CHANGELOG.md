# Changelog

All notable changes to this project are documented here. This project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-08-24

Renames every identifier onto a single `mrdw` namespace. 1.4.0 kept the predecessors' names so the
merge would be a drop-in replacement; this release drops that constraint so the plugin reads as one
plugin rather than two bolted together.

### Breaking

- **REST namespaces collapse into one.** `packrelay/v1` and `tailsignal/v1` are both now `mrdw/v1`.
  Route paths are otherwise unchanged, so only the namespace segment moves.
- **Options renamed** — `packrelay_*` to `mrdw_forms_*`, `tailsignal_*` to `mrdw_push_*`,
  `mrdemonwolf_modules` to `mrdw_modules`.
- **Database tables renamed** — `{prefix}packrelay_entries` to `{prefix}mrdw_forms_entries`, and
  `{prefix}tailsignal_*` to `{prefix}mrdw_push_*`. **Data is not migrated**; the new tables are
  created empty. Export entries and devices to CSV before upgrading.
- **Capability renamed** — `tailsignal_manage` is now the plugin-wide `mrdw_manage`.
- **Classes renamed** — `PackRelay_*` to `MRDW_Forms_*`, `TailSignal_*` to `MRDW_Push_*`.
- **Admin page slugs renamed** — `packrelay-entries` to `mrdw-form-entries`, `tailsignal-*` to
  `mrdw-push-*`. Page hook suffixes are now `mrdw_page_*`.
- **Text domain** is now `mrdw`, from `mrdemonwolf`.
- **Cron hooks, post meta, nonces, AJAX actions and asset handles** all move to the `mrdw_forms_` /
  `mrdw_push_` prefixes.
- **Minimum PHP is 8.3.** The Firebase SDK the Forms module depends on requires it; the 8.1 that
  1.4.0 advertised was never actually installable.

### Changed

- The bootstrap no longer maps legacy constants onto the module directories. Modules use
  `MRDW_FORMS_DIR` / `MRDW_PUSH_DIR` directly.
- Every class file, asset and test file renamed to match its class.
- JavaScript globals renamed: `tailsignal` to `mrdwPush`, `packrelayAdmin` to `mrdwFormsAdmin`.
- The Tailwind scope moved from `#tailsignal-app` to `#mrdw-push-app`.
- phpcs now allows only the `mrdw` / `MRDW` prefixes.

### Unchanged

- REST route paths, request and response shapes, and both authentication schemes.
- Module behaviour. Nothing about how forms are submitted or notifications are delivered changed.

[2.0.0]: https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/releases/tag/v2.0.0

## [1.4.0] - 2026-08-23

First release of the merged plugin. It supersedes PackRelay 1.3.0 and TailSignal 1.2.0, which are
now the Forms and Push modules. The version number starts above both predecessors so either one
upgrades forward cleanly.

### Added

- Single **MrDemonWolf** admin menu; both modules now register submenus beneath it.
- Per-module enable/disable switches under **MrDemonWolf → General**, backed by the
  `mrdemonwolf_modules` option. A disabled module registers no hooks and no REST routes, and its
  stored data is left untouched. Enabling a module for the first time runs its activator.
- Stable and nightly update channels, selected with the `MRDW_UPDATE_CHANNEL` constant and
  overridable through the `mrdw_update_channel` filter. Unrecognised values fall back to stable.
- `MRDW_EXPO_ACCESS_TOKEN` constant, so the Expo access token can be kept out of the database. When
  defined, the settings field renders disabled and the sanitize callback refuses to write the option.
- Conflict guard: the plugin declines to load and shows a notice naming the offender while PackRelay
  or TailSignal is still active, rather than fatally redeclaring their classes.
- Merged uninstall handler that removes both modules' tables, options, capability, post meta and cron
  events, multisite included.
- Documentation site with installation, configuration, migration and REST API reference.
- CI across PHP 8.3–8.5, plus release and nightly build pipelines.

### Changed

- Text domain unified to `mrdemonwolf` across both modules. Custom translation files need renaming.
- Minimum PHP raised to 8.3. PackRelay declared 8.1 and TailSignal 7.4, but the Firebase SDK
  the Forms module depends on now requires 8.3, so 8.1 was never actually installable.
- Admin page hook suffixes are now `mrdemonwolf_page_*` instead of `toplevel_page_tailsignal` and
  `tailsignal_page_*`.
- Both hand-rolled GitHub updaters replaced by a single `plugin-update-checker` instance.
- Tailwind build for the Push module's admin styling wired back up against the merged paths.
- Codebase brought up to WordPress Coding Standards; first-party code passes the full ruleset.

### Fixed

- A settings template assigned to `$page`, a WordPress global, from inside an included partial.
- `current_time( 'timestamp' )` replaced with a direct format string.
- Missing `wp_unslash()` on fifteen admin request reads, including three nonce verifications.
- Unsanitised bulk-action input on the Devices screen.
- `$_SERVER['REMOTE_ADDR']` sanitised without unslashing first.
- The test bootstrap wrote WordPress stub files into the plugin directory, so they were packaged into
  the distributed zip.

### Unchanged, deliberately

REST namespaces (`packrelay/v1`, `tailsignal/v1`), routes, and authentication; database table names;
option names; the `tailsignal_manage` capability; admin page slugs. Existing sites keep their data
and the app needs no changes.

[1.4.0]: https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/releases/tag/v1.4.0
