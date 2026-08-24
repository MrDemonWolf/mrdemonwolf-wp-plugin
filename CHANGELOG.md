# Changelog

All notable changes to this project are documented here. This project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- CI across PHP 8.1–8.4, plus release and nightly build pipelines.

### Changed

- Text domain unified to `mrdemonwolf` across both modules. Custom translation files need renaming.
- Minimum PHP raised to 8.1, from TailSignal's 7.4.
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
