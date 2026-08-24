<?php
/**
 * Secret resolution.
 *
 * Secrets are read from wp-config.php constants first so a site can keep them
 * out of the database entirely. The legacy option is honoured as a fallback so
 * existing MRDW_Push installs keep working after the merge.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Secrets
 */
class MRDW_Secrets {

	/**
	 * Constant that may hold the Expo access token.
	 */
	const EXPO_TOKEN_CONSTANT = 'MRDW_EXPO_ACCESS_TOKEN';

	/**
	 * Legacy option name, kept for backwards compatibility with MRDW_Push.
	 */
	const EXPO_TOKEN_OPTION = 'mrdw_push_expo_access_token';

	/**
	 * Whether the Expo token is supplied by a wp-config.php constant.
	 *
	 * @return bool
	 */
	public static function expo_token_is_constant() {
		return defined( self::EXPO_TOKEN_CONSTANT )
			&& '' !== trim( (string) constant( self::EXPO_TOKEN_CONSTANT ) );
	}

	/**
	 * Resolve the Expo access token.
	 *
	 * @return string Empty string when no token is configured.
	 */
	public static function expo_access_token() {
		if ( self::expo_token_is_constant() ) {
			return trim( (string) constant( self::EXPO_TOKEN_CONSTANT ) );
		}

		return trim( (string) get_option( self::EXPO_TOKEN_OPTION, '' ) );
	}

	/**
	 * Guard the legacy option against writes while the constant is in force.
	 *
	 * Registered as the sanitize callback for the Expo token setting, so the
	 * database never shadows an explicitly configured constant.
	 *
	 * @param mixed $value Submitted value.
	 * @return string
	 */
	public static function sanitize_expo_access_token( $value ) {
		if ( self::expo_token_is_constant() ) {
			// Constant wins; preserve whatever is already stored rather than
			// letting a disabled form field blank it out.
			return (string) get_option( self::EXPO_TOKEN_OPTION, '' );
		}

		return sanitize_text_field( (string) $value );
	}
}
