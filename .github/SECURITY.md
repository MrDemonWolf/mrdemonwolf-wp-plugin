# Security Policy

## Supported versions

Only the latest stable release receives security fixes. Nightly builds are development snapshots and
are not supported.

## Reporting a vulnerability

Please do not open a public issue for a security problem.

Report it privately through
[GitHub Security Advisories](https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/security/advisories/new),
or email `security@mrdemonwolf.com`.

Include the plugin version, WordPress and PHP versions, which module is affected, and the steps to
reproduce. You can expect an acknowledgement within 72 hours.

## Scope notes

Two REST routes are intentionally reachable without a WordPress login, because the client app has no
WordPress user. These are not vulnerabilities in themselves:

- `POST /packrelay/v1/submit/{form_id}` and `GET /packrelay/v1/forms/{form_id}/fields` — authorised
  by a Firebase App Check token in the request body, plus a form-ID allow-list and a CORS origin
  allow-list.
- `POST|DELETE /tailsignal/v1/register` and `GET /tailsignal/v1/register/status` — authorised by the
  device's own Expo push token, which is issued by Expo and cryptographically random. Possession of
  a token permits managing only that device.

A demonstration that either scheme can be bypassed — forging an accepted App Check token, enumerating
device tokens, or reaching admin routes without `tailsignal_manage` — is very much in scope.
