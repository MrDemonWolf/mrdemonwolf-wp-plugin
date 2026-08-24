#!/usr/bin/env bash
#
# Build a distributable plugin zip.
#
#   bin/build-zip.sh                 -> build/mrdemonwolf-wp-plugin.zip (stable)
#   bin/build-zip.sh --nightly       -> build/mrdemonwolf-wp-plugin-nightly.zip
#
# A nightly build stamps the plugin header with the next minor version plus a
# date suffix (1.4.0 -> 1.5.0-nightly.20260823). That sorts above the current
# stable release and below the next one, so a nightly site upgrades onto stable
# as soon as stable catches up.
set -euo pipefail

cd "$(dirname "$0")/.."

SLUG="mrdemonwolf-wp-plugin"
BUILD_DIR="build"
NIGHTLY=0
[[ "${1:-}" == "--nightly" ]] && NIGHTLY=1

VERSION="$(grep -m1 -E "^\s*\*\s*Version:" mrdemonwolf.php | sed -E 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')"
if [[ -z "$VERSION" ]]; then
	echo "error: could not read Version from mrdemonwolf.php" >&2
	exit 1
fi

ZIP_NAME="${SLUG}.zip"
BUILD_VERSION="$VERSION"
if [[ "$NIGHTLY" == "1" ]]; then
	MAJOR="${VERSION%%.*}"
	REST="${VERSION#*.}"
	MINOR="${REST%%.*}"
	BUILD_VERSION="${MAJOR}.$((MINOR + 1)).0-nightly.$(date -u +%Y%m%d)"
	ZIP_NAME="${SLUG}-nightly.zip"
fi

echo "==> Building ${ZIP_NAME} (version ${BUILD_VERSION})"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/$SLUG"

composer install --no-dev --optimize-autoloader --no-interaction --quiet

rsync -a --exclude-from=.distignore ./ "$BUILD_DIR/$SLUG/"

if [[ "$NIGHTLY" == "1" ]]; then
	# Stamp both the header and the runtime constant so the admin screen and the
	# update checker agree on what is installed.
	perl -0pi -e "s/(\*\s*Version:\s*)\Q${VERSION}\E/\${1}${BUILD_VERSION}/" "$BUILD_DIR/$SLUG/mrdemonwolf.php"
	perl -0pi -e "s/(define\(\s*'MRDW_VERSION',\s*')\Q${VERSION}\E(')/\${1}${BUILD_VERSION}\${2}/" "$BUILD_DIR/$SLUG/mrdemonwolf.php"
fi

( cd "$BUILD_DIR" && zip -qr "$ZIP_NAME" "$SLUG" )
rm -rf "${BUILD_DIR:?}/${SLUG:?}"

# Restore the dev dependencies the working tree expects.
composer install --no-interaction --quiet

echo "==> Built ${BUILD_DIR}/${ZIP_NAME}"
echo "version=${BUILD_VERSION}" >> "${GITHUB_OUTPUT:-/dev/null}"
echo "zip=${BUILD_DIR}/${ZIP_NAME}" >> "${GITHUB_OUTPUT:-/dev/null}"
