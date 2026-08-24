export const GITHUB_REPO = "https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin";

/**
 * GitHub resolves these server-side, so they always point at the newest build
 * without the page having to call the API at build time.
 */
export const DOWNLOAD_STABLE = `${GITHUB_REPO}/releases/latest/download/mrdemonwolf-wp-plugin.zip`;

/**
 * Nightly builds are published as pre-releases with dated version tags, so
 * there is no single permanent asset URL — link to the filtered release list.
 */
export const DOWNLOAD_NIGHTLY = `${GITHUB_REPO}/releases?q=prerelease%3Atrue&expanded=true`;

export const DISCORD_URL = "https://mrdwolf.net/discord";
