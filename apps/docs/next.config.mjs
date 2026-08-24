import { createMDX } from "fumadocs-mdx/next";

// Published to GitHub Pages at /mrdemonwolf-wp-plugin/, so production builds
// need the repository name as a base path. Dev serves from the root.
const repoName = "mrdemonwolf-wp-plugin";
const isProd = process.env.NODE_ENV === "production";

/** @type {import('next').NextConfig} */
const config = {
	reactStrictMode: true,
	output: "export",
	trailingSlash: true,
	images: { unoptimized: true },
	basePath: isProd ? `/${repoName}` : "",
};

export default createMDX()(config);
