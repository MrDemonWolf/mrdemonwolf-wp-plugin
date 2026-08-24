import js from "@eslint/js";
import nextPlugin from "@next/eslint-plugin-next";
import prettier from "eslint-config-prettier";
import unusedImports from "eslint-plugin-unused-imports";
import tseslint from "typescript-eslint";

export default tseslint.config(
	// Global ignores
	{
		ignores: [
			"**/node_modules/**",
			"**/dist/**",
			"**/.next/**",
			"**/.turbo/**",
			"**/.source/**",
			"**/out/**",
			"apps/plugin/**",
		],
	},

	// Base configs
	js.configs.recommended,
	...tseslint.configs.recommended,

	// Unused imports plugin
	{
		plugins: {
			"unused-imports": unusedImports,
		},
		rules: {
			"no-unused-vars": "off",
			"@typescript-eslint/no-unused-vars": "off",
			"unused-imports/no-unused-imports": "error",
			"unused-imports/no-unused-vars": [
				"error",
				{
					vars: "all",
					varsIgnorePattern: "^_",
					args: "after-used",
					argsIgnorePattern: "^_",
				},
			],
		},
	},

	// General rules
	{
		rules: {
			"no-param-reassign": "error",
			"@typescript-eslint/no-non-null-assertion": "warn",
			"@typescript-eslint/no-explicit-any": "warn",
			"@typescript-eslint/no-empty-object-type": "off",
			"@typescript-eslint/no-require-imports": "off",
		},
	},

	// Node.js globals for config files and mjs scripts
	{
		files: ["**/*.config.{ts,mjs,js}", "**/*.mjs"],
		languageOptions: {
			globals: {
				process: "readonly",
				console: "readonly",
				Buffer: "readonly",
				__dirname: "readonly",
				__filename: "readonly",
				module: "readonly",
				require: "readonly",
			},
		},
	},

	// Next.js plugin for the docs app
	{
		files: ["apps/docs/**/*.{ts,tsx,js,jsx}"],
		plugins: {
			"@next/next": nextPlugin,
		},
		rules: {
			...nextPlugin.configs.recommended.rules,
			...nextPlugin.configs["core-web-vitals"].rules,
			"@next/next/no-img-element": "off",
			"@next/next/no-html-link-for-pages": "off",
		},
	},

	// Prettier must be last
	prettier,
);
