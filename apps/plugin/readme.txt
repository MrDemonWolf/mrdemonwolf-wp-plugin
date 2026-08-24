=== MrDemonWolf ===
Contributors: mrdemonwolf
Tags: rest-api, push-notifications, expo, gravity-forms, divi
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.3
Stable tag: 2.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connects a WordPress site to the MrDemonWolf app: REST form submissions and self-hosted Expo push notifications.

== Description ==

The official MrDemonWolf plugin. It bundles two modules, either of which can be switched off without
uninstalling the plugin or losing its data.

**Forms** exposes REST endpoints so an external app can read a form's fields and submit to it.
Submissions are routed to Divi, WPForms or Gravity Forms and recorded in an entries table with CSV
export. Requests are authorised by a Firebase App Check token, a form-ID allow-list and a CORS origin
allow-list.

**Push** is a self-hosted push notification system built on Expo. Devices register themselves, can be
grouped, and notifications can be sent on demand, scheduled, or fired automatically when a post is
published. Delivery receipts are collected afterwards, and tokens Expo reports as dead are retired
automatically.

This plugin replaces PackRelay and TailSignal, which are now its Forms and Push modules.

= Privacy =

Form submissions and device records stay in your own database. Data leaves the site in two cases
only: push tokens and message content go to Expo (`exp.host`) in order to be delivered, and GitHub
(`api.github.com`) is contacted to check for plugin updates.

== Installation ==

1. Export any entries and devices you want to keep, then deactivate PackRelay and TailSignal if
   either is installed. Do not delete them yet — deleting drops their database tables.
2. Upload the plugin zip under Plugins → Add New → Upload Plugin.
3. Activate it.
4. Configure the modules under MrDemonWolf → General.

Full documentation: https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin/

== Frequently Asked Questions ==

= Do I need both modules? =

No. Turn either off under MrDemonWolf → General. A disabled module registers no hooks and no REST
routes, and its stored data is left untouched.

= Why are some REST routes public? =

The app has no WordPress user, so it cannot log in. Authorisation happens inside the handler instead:
form submissions must carry a verified Firebase App Check token, and device routes are authorised by
the device's own Expo push token, which is cryptographically random and grants access to that device
only.

= Where do updates come from? =

GitHub releases, through the normal WordPress updates screen. There is no update server and no
licence key. Sites can opt into nightly builds with a wp-config.php constant.

= How do I keep the Expo token out of the database? =

Define `MRDW_EXPO_ACCESS_TOKEN` in wp-config.php. The settings field then shows as locked and the
option cannot be written.

== Changelog ==

= 2.1.0 =
* Changed: the Expo PHP SDK is replaced by direct calls to Expo's API over the WordPress HTTP API.
  The SDK never exposed richContent (the field Expo documents for notification images) and carried
  11 deprecations that are fatal on PHP 9. All of them are gone.
* Added: server-side image URL validation. HTTPS and a publicly reachable host are now required,
  because iOS refuses plain HTTP attachments and a phone cannot reach a private host -- previously
  such a notification arrived with its image silently missing.
* Added: mrdw_push_featured_image_size and mrdw_push_message filters.
* Fixed: saving a post from a screen without the MrDemonWolf meta box permanently opted that post
  out of featured images.
* Fixed: REST /send ignored featured images even when the site setting was on.
* Fixed: uninstalling left pre-2.0.0 tables and options behind.
* Docs: new setup guide written for non-technical readers, with copy-a-prompt buttons for Claude.

= 2.0.0 =
* Breaking: every identifier renamed onto a single `mrdw` namespace.
* Breaking: REST namespaces `packrelay/v1` and `tailsignal/v1` collapse into `mrdw/v1`. Route paths
  are otherwise unchanged.
* Breaking: options, database tables, capability, classes, page slugs, cron hooks, post meta and
  asset handles all renamed to the mrdw_forms_ / mrdw_push_ prefixes.
* Breaking: data is NOT migrated. New tables are created empty; export first.
* Breaking: capability tailsignal_manage is now mrdw_manage.
* Breaking: text domain is now mrdw.
* Breaking: minimum PHP is 8.3, required by the Firebase SDK.
* Unchanged: REST route paths, request and response shapes, both authentication schemes, and all
  module behaviour.

= 1.4.0 =
* First release of the merged plugin, superseding PackRelay 1.3.0 and TailSignal 1.2.0.
* Added: single MrDemonWolf admin menu, per-module enable/disable switches.
* Added: stable and nightly update channels via MRDW_UPDATE_CHANNEL.
* Added: MRDW_EXPO_ACCESS_TOKEN constant so the Expo token need not be stored in the database.
* Added: conflict guard that refuses to load while PackRelay or TailSignal is still active.
* Changed: codebase brought up to WordPress Coding Standards.
* Fixed: a settings template overwrote the $page WordPress global.
* Fixed: missing wp_unslash() on several admin request reads; unsanitised bulk-action input.

== Upgrade Notice ==

= 2.1.0 =
No action needed. If you send notification images, note that image URLs must now be HTTPS and on a
publicly reachable host -- an unusable URL is refused rather than producing a notification with a
silently missing image.

= 2.0.0 =
Breaking release. Every option, table, capability and REST namespace is renamed, and existing data
is NOT migrated. Export your entries and devices to CSV first, then update the app to call
/wp-json/mrdw/v1/. Requires PHP 8.3.
