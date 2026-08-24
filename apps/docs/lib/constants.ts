export const GITHUB_REPO = "https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin";

export const SITE_URL = "https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin";

/**
 * GitHub resolves this server-side, so it always points at the newest stable
 * build without the page having to call the API at build time.
 */
export const DOWNLOAD_STABLE = `${GITHUB_REPO}/releases/latest/download/mrdemonwolf-wp-plugin.zip`;

/**
 * Nightly builds are dated pre-releases, so there is no permanent asset URL —
 * link to the filtered release list instead.
 */
export const DOWNLOAD_NIGHTLY = `${GITHUB_REPO}/releases?q=prerelease%3Atrue&expanded=true`;

export const DISCORD_URL = "https://mrdwolf.net/discord";

/** Production builds are served from a GitHub Pages project path. */
export const BASE_PATH = process.env.NODE_ENV === "production" ? "/mrdemonwolf-wp-plugin" : "";

/** Prefix a public asset with the base path; needed because images are unoptimised. */
export function assetPath(path: string): string {
	return `${BASE_PATH}${path}`;
}
