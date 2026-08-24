/** @type {import('tailwindcss').Config} */
module.exports = {
	// Utilities are prefixed and scoped so they can never leak into, or be
	// overridden by, the surrounding wp-admin stylesheet.
	prefix: "tw-",
	corePlugins: {
		preflight: false,
	},
	important: "#tailsignal-app",
	content: [
		"modules/push/admin/partials/**/*.php",
		"modules/push/admin/class-tailsignal-admin-devices.php",
		"modules/push/admin/class-tailsignal-admin-history.php",
		"modules/push/admin/js/**/*.js",
	],
	theme: {
		extend: {
			colors: {
				brand: {
					50: "#edf9ff",
					100: "#d6f1ff",
					200: "#a3ddfb",
					300: "#5cc8f5",
					400: "#22b5ec",
					500: "#0FACED",
					600: "#0991d4",
					700: "#0776a8",
					800: "#064a6e",
					900: "#091533",
					950: "#060d20",
				},
			},
		},
	},
};
