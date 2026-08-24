# MrDemonWolf - The Official WordPress Plugin

MrDemonWolf is the WordPress plugin that connects the MrDemonWolf site to the
MrDemonWolf app. It accepts form submissions over the REST API and sends
self-hosted push notifications through Expo, replacing the separate PackRelay
and TailSignal plugins with a single install. It is free, GPL-licensed, and
carries no licence key, activation step, or usage tracking.

Own your data. Keep your pack in the loop.

## Features

- **Forms module** - REST endpoints that let an external app read a form's
  fields and submit to it, routed into Divi, WPForms, or Gravity Forms.
- **App Check authorisation** - Submissions carry a Firebase App Check token
  that is verified server-side, backed by form-ID and CORS origin allow-lists.
- **Entry storage** - Every submission, REST or front-end, is recorded in a
  filterable admin table with a formula-safe CSV export.
- **Push module** - A self-hosted Expo notification system with a device
  registry, groups, scheduling, and delivery-receipt collection.
- **Publish automation** - Fire a notification when a post is published, with
  per-post overrides and template placeholders.
- **Module switches** - Turn either module off without uninstalling the plugin
  or losing its data.
- **Update channels** - Stable and nightly releases delivered from GitHub
  through the normal WordPress updates screen.
- **Secrets outside the database** - Supply the Expo access token with a
  `wp-config.php` constant and the settings field locks itself.
- **Conflict guard** - Refuses to load, with an explanatory notice, while
  PackRelay or TailSignal is still active.

## Getting Started

Full documentation:
[mrdemonwolf.github.io/mrdemonwolf-wp-plugin](https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin/)

1. Deactivate PackRelay and TailSignal if either is installed. Deactivate
   them, do not delete them, since deleting drops their database tables.
2. Download the
   [latest stable zip](https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/releases/latest/download/mrdemonwolf-wp-plugin.zip).
3. In wp-admin, go to `Plugins -> Add New -> Upload Plugin`, choose the zip,
   and install it.
4. Activate the plugin. Its tables and the `tailsignal_manage` capability are
   created on activation.
5. Open `MrDemonWolf -> General` and confirm both modules are enabled.

## Usage

### Admin screens

| Screen         | Module | What it does                                             |
| -------------- | ------ | -------------------------------------------------------- |
| General        | Core   | Module switches, installed version, update channel       |
| Form Entries   | Forms  | Browse, filter, view, and export submissions             |
| Forms Settings | Forms  | Provider, Firebase project, allow-lists, notifications   |
| Push           | Push   | Device and delivery overview                             |
| Send           | Push   | Compose, target, and schedule a notification             |
| Devices        | Push   | Registered devices, labels, dev flags, CSV import/export |
| Groups         | Push   | Group devices for targeted sends                         |
| History        | Push   | Past and scheduled sends, with delivery outcomes         |
| Push Settings  | Push   | Dev mode, Expo token, auto-notify, templates             |

### REST endpoints

| Endpoint                                       | Method       | Access              |
| ---------------------------------------------- | ------------ | ------------------- |
| `/wp-json/packrelay/v1/forms/{form_id}/fields` | GET          | App Check token     |
| `/wp-json/packrelay/v1/submit/{form_id}`       | POST         | App Check token     |
| `/wp-json/tailsignal/v1/register`              | POST, DELETE | Expo push token     |
| `/wp-json/tailsignal/v1/register/status`       | GET          | Expo push token     |
| `/wp-json/tailsignal/v1/send`                  | POST         | `tailsignal_manage` |
| `/wp-json/tailsignal/v1/stats`                 | GET          | `tailsignal_manage` |
| `/wp-json/tailsignal/v1/devices/export`        | GET          | `tailsignal_manage` |
| `/wp-json/tailsignal/v1/devices/import`        | POST         | `tailsignal_manage` |

### Configuration constants

```php
// Keep the Expo access token out of the database.
define( 'MRDW_EXPO_ACCESS_TOKEN', 'your-expo-access-token' );

// Track nightly builds instead of stable releases.
define( 'MRDW_UPDATE_CHANNEL', 'nightly' );
```

## Tech Stack

| Layer              | Technology                                                   |
| ------------------ | ------------------------------------------------------------ |
| Plugin             | PHP 8.3+, WordPress 6.0+ (tested to 7.1)                     |
| Push delivery      | `ctwillie/expo-server-sdk-php`                               |
| Token verification | `kreait/firebase-php`                                        |
| Updates            | `yahnis-elsts/plugin-update-checker`                         |
| Admin styling      | WordPress core styles, Tailwind CSS 3 (scoped, `tw-` prefix) |
| Tests              | PHPUnit 10, Brain Monkey, Mockery                            |
| Standards          | WordPress Coding Standards via PHP_CodeSniffer               |
| Docs site          | Next.js 16, Fumadocs, Tailwind CSS 4                         |
| Monorepo           | Bun workspaces, Turborepo                                    |
| CI/CD              | GitHub Actions, GitHub Pages, GitHub Releases                |

## Development

### Prerequisites

- PHP 8.3 or newer with Composer
- Bun 1.3 or newer
- Node.js 20 or newer

### Setup

1. Clone the repository.

   ```bash
   git clone https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin.git
   ```

2. Install the JavaScript workspace dependencies.

   ```bash
   bun install
   ```

3. Install the plugin's PHP dependencies.

   ```bash
   cd apps/plugin && composer install
   ```

4. Run the test suites.

   ```bash
   cd apps/plugin && composer test
   ```

5. Start the documentation site on port 3001.

   ```bash
   bun run docs:dev
   ```

### Development Scripts

Repository root:

- `bun run docs:dev` - Run the docs site locally on port 3001.
- `bun run docs:build` - Build the static docs export.
- `bun run plugin:css` - Rebuild the Push module's Tailwind stylesheet.
- `bun run plugin:test` - Run both PHP test suites.
- `bun run plugin:lint` - Run PHP_CodeSniffer over the plugin.
- `bun run plugin:zip` - Build a distributable plugin zip.
- `bun run typecheck` - Type-check the workspaces.
- `bun run lint` - Run ESLint.
- `bun run format` - Apply Prettier formatting.
- `bun run format:check` - Verify Prettier formatting.

Inside `apps/plugin`:

- `composer test` - Run the Forms and Push suites.
- `composer test:forms` - Run the Forms suite only.
- `composer test:push` - Run the Push suite only.
- `composer lint` - Check coding standards.
- `composer lint:fix` - Auto-fix what PHP_CodeSniffer can.
- `bin/build-zip.sh` - Build `build/mrdemonwolf-wp-plugin.zip`.
- `bin/build-zip.sh --nightly` - Build a date-stamped nightly zip.

### Code Quality

- WordPress Coding Standards enforced in full on first-party code, with the
  merged module trees held to the security and correctness sniffs while their
  style debt is paid down file by file.
- 417 unit tests across the two modules, run against PHP 8.3 through 8.5 in CI.
- Every GitHub Action pinned to a commit SHA, with `persist-credentials`
  disabled on checkout.
- Prettier and ESLint for the docs workspace, verified in CI.

## Project Structure

```
mrdemonwolf-wp-plugin/
├── apps/
│   ├── docs/                 # Fumadocs site, static-exported to GitHub Pages
│   │   ├── app/              # Next.js routes and the download landing page
│   │   ├── content/docs/     # MDX documentation source
│   │   └── lib/              # Shared constants and layout options
│   └── plugin/               # The WordPress plugin itself
│       ├── mrdemonwolf.php   # Bootstrap, module gate, legacy constant mapping
│       ├── uninstall.php     # Removes both modules' data
│       ├── includes/         # First-party glue: modules, secrets, updater, admin
│       ├── modules/
│       │   ├── forms/        # Was PackRelay
│       │   └── push/         # Was TailSignal
│       ├── tests/            # PHPUnit suites, one per module
│       └── bin/build-zip.sh  # Release packaging
└── .github/workflows/        # CI, docs deploy, release, nightly
```

## License

![GitHub license](https://img.shields.io/github/license/mrdemonwolf/mrdemonwolf-wp-plugin.svg?style=for-the-badge&logo=github)

## Contact

Questions, bug reports, and feedback are welcome.

- Documentation: [mrdemonwolf.github.io/mrdemonwolf-wp-plugin](https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin/)
- Issues: [GitHub Issues](https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/issues)
- Security: report privately through [GitHub Security Advisories](https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/security/advisories/new)
- Discord: [Join my server](https://mrdwolf.net/discord)

Made with love by [MrDemonWolf, Inc.](https://www.mrdemonwolf.com)
