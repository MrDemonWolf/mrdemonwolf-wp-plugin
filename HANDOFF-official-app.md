# Handoff: `official-app` work needed after plugin 2.0.0

For a Claude Code session in `~/Developer/mrdemonwolf/official-app`
(`github.com/MrDemonWolf/official-app`). Written from a read-only survey on
2026-08-24, alongside the WordPress plugin release it depends on.

**Read `CLAUDE.md` and `compliance.md` in that repo before starting.** `compliance.md`
documents a previous App Store rejection and is the single most useful doc for any
upgrade work.

---

## 1. Priority one: the app is broken against the current plugin

The WordPress plugin shipped 2.0.0, which collapsed its two REST namespaces into one.
The app still calls the old ones, so **device registration, unregistration, status
checks and contact-form submission all 404 today**.

| Was                               | Is now                      |
| --------------------------------- | --------------------------- |
| `https://…/wp-json/tailsignal/v1` | `https://…/wp-json/mrdw/v1` |
| `https://…/wp-json/packrelay/v1`  | `https://…/wp-json/mrdw/v1` |

Route paths, request bodies, responses and both authentication schemes are unchanged.
Only the namespace segment moved.

### Files to change

- `.env`, `.env.example`, `.env.production` — the `EXPO_PUBLIC_TAILSIGNAL_API_URL` and
  `EXPO_PUBLIC_PACKRELAY_API_URL` variables. Consider collapsing them into a single
  `EXPO_PUBLIC_MRDW_API_URL`, since they now point at the same namespace.
- `src/services/notifications.ts` — `registerDevice()` (`:43`), `unregisterDevice()`
  (`:63`), `isDeviceRegistered()` (`:80`).
- `src/services/contact.ts` — the `POST ${PACKRELAY_API_URL}/submit/${FORM_ID}` call.
- `docs/content/docs/services/tailsignal.mdx`, `docs/content/docs/wordpress/rest-api.mdx`
  — both still describe the old namespaces and link to the archived repos.

`.env.production` is separately stale: it still carries `EXPO_PUBLIC_GF_API_URL` and
`EXPO_PUBLIC_GF_CONTACT_FORM_ID` from the Gravity Forms era, and has no PackRelay vars
at all.

Also note the configured host is `mrdemonwolf.dev`, not `.com`. Confirm which site the
plugin is actually installed on before changing anything else.

---

## 2. The Notification Service Extension already exists — do not rebuild it

I expected this to be the blocker for rich push images. It is not.

`targets/notification-service/` is a working NSE, generated into the Xcode project by
`@bacons/apple-targets` (listed in `app.config.ts` plugins). `NotificationService.swift`
already downloads the image and attaches it. It reads:

```swift
if let body = request.content.userInfo["body"] as? [String: Any],
   let richContent = body["_richContent"] as? [String: Any],
   let imageUrlString = richContent["image"] as? String,
```

That is correct. Expo's documented `richContent` field arrives at APNs nested under
`body._richContent`, so the sender and the extension agree.

**The plugin side is confirmed working.** As of plugin 2.1.0 it sends
`richContent: { image: … }` together with `mutableContent: true`, which is what makes
APNs invoke the extension at all, and it now rejects image URLs that are not HTTPS or
not publicly reachable before sending.

### Real gaps in the extension

1. **Hard-coded `.jpg` extension.** The downloaded file is renamed to `.jpg` regardless
   of its actual type. `UNNotificationAttachment` validates by extension, so a PNG or
   GIF featured image may be rejected outright and silently produce a plain
   notification. Derive the extension from the response `MIME` type or the URL path.
2. **No size cap on the download.** iOS allows attachments up to 10 MB; a large
   featured image will simply blow the extension's execution budget and fall through.
   Bail out early on `Content-Length` over a few MB.
3. No App Group or shared container, so nothing is cached between invocations. Fine
   for now, worth knowing.

### How to test it end to end

1. In WordPress, **MrDemonWolf → Push Settings**, turn on **Dev mode**.
2. Register the device, then flag it as **Dev** under **MrDemonWolf → Devices**.
3. **MrDemonWolf → Send**, set an **Image URL** on a public HTTPS host, send.
4. Android should show the image with no app changes. iOS shows it only on a build
   that includes the extension — so a dev client or TestFlight build, not Expo Go.

---

## 3. Push registration is not attested, but the contact form is

`src/services/app-check.ts` initialises Firebase App Check with App Attest /
DeviceCheck on iOS and Play Integrity on Android, and `src/services/contact.ts`
attaches a token to every form submission. Push registration does not.

That matches the plugin, which treats the Expo push token itself as the credential for
device routes and only verifies App Check on form submission. It is a deliberate
design, not an oversight — but worth a decision: if you want device registration
attested too, both sides need changing together.

---

## 4. Build blocker: the Firebase config files are missing

`app.config.ts` references both:

```ts
ios: {
	googleServicesFile: "./GoogleService-Info.plist";
}
android: {
	googleServicesFile: "./google-services.json";
}
```

Neither file exists in the working tree. They were gitignored in `ceffd97` and removed
in `fdeb897`. **`pnpm prebuild` will fail until they are restored** from the Firebase
console.

`CLAUDE.md` still claims they are "committed to repo" — stale, and it carries an
existing TODO to upload the production plist to EAS as a `GOOGLE_SERVICES_PLIST` file
env var and switch the config to
`process.env.GOOGLE_SERVICES_PLIST || "./GoogleService-Info.plist"`. Worth doing as
part of this.

---

## 5. Security note

`credentials.json` sits at the repo root containing **plaintext Android keystore
passwords**, alongside `credentials/android/keystore.jks`. Both are gitignored, so they
are not in the published history, but they are unencrypted on disk. Consider moving
them to EAS-managed credentials.

I did not read or reproduce the values.

---

## 6. Docs drift to fix while you are in there

- `docs/content/docs/features/push-notifications.mdx:73` references a custom config
  plugin `plugins/notification-service-extension.js`. That file does not exist; it was
  replaced by the `@bacons/apple-targets` approach.
- `CLAUDE.md`'s claim that the Firebase files are committed.
- `README.md` already advertises "rich notification images (iOS)" — true only for
  builds that include the extension, which is worth stating.
- `GEMINI.md` is a 0-byte file.

---

## Project context

Managed Expo with native escape hatches (CNG — no `ios/` or `android/` on disk, both
gitignored and generated by prebuild).

|                 |                                                            |
| --------------- | ---------------------------------------------------------- |
| Expo            | 55.0.4                                                     |
| React Native    | 0.83.2                                                     |
| React           | 19.2.0                                                     |
| expo-router     | 55.0.3                                                     |
| Package manager | pnpm                                                       |
| EAS project     | `4a220b17-d746-48f1-9f46-d83a0a933b40`                     |
| Bundle ID       | `com.mrdemonwolf.OfficialApp` (`.dev` for the dev variant) |

New Architecture, typed routes and React Compiler are all enabled.

One thing to confirm: the Android `package` is set to the same string as the iOS
bundle identifier, including the `.dev` suffix on dev builds. That looks intentional
but is worth checking against the Play Console listing.

Git state at survey time: branch `main`, working tree clean, HEAD `1cd1121`.

---

## Suggested order

1. Namespace fix + env consolidation, then verify against a real site — this is what
   makes the app work again.
2. Restore the Firebase config files so `prebuild` runs.
3. Fix the NSE image extension and add the size cap.
4. Build a dev client and confirm a featured image renders on a real iPhone.
5. Docs drift and the `credentials.json` move.

## Plugin-side reference

- Docs: https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin/
- Endpoints the app uses, all now under `mrdw/v1`:
  - `POST /register`, `DELETE /register`, `GET /register/status` — authorised by the
    Expo push token itself
  - `POST /submit/{form_id}` — requires `app_check_token` in the request body
- Plugin repo: https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin
