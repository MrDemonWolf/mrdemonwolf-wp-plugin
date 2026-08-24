=== MrDemonWolf ===
Contributors: mrdemonwolf
Tags: rest-api, push-notifications, expo, gravity-forms, divi
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.3
Stable tag: 1.4.0
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

1. Deactivate PackRelay and TailSignal if either is installed. Do not delete them — deleting drops
   their database tables.
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

= 1.4.0 =
* First release of the merged plugin, superseding PackRelay 1.3.0 and TailSignal 1.2.0.
* Added: single MrDemonWolf admin menu, per-module enable/disable switches.
* Added: stable and nightly update channels via `MRDW_UPDATE_CHANNEL`.
* Added: `MRDW_EXPO_ACCESS_TOKEN` constant so the Expo token need not be stored in the database.
* Added: conflict guard that refuses to load while PackRelay or TailSignal is still active.
* Changed: text domain unified to `mrdemonwolf`; PHP floor raised to 8.1.
* Changed: codebase brought up to WordPress Coding Standards.
* Fixed: a settings template overwrote the `$page` WordPress global.
* Fixed: missing `wp_unslash()` on several admin request reads; unsanitised bulk-action input.
* Unchanged: REST namespaces and routes, database tables, option names, capabilities, page slugs.

== Upgrade Notice ==

= 1.4.0 =
Deactivate PackRelay and TailSignal before activating this plugin — do not delete them, as deleting
drops their tables. Custom translation files must be renamed to the `mrdemonwolf` text domain.
