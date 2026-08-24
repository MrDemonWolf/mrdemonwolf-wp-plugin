import type { BaseLayoutProps } from "fumadocs-ui/layouts/shared";
import { GITHUB_REPO } from "@/lib/constants.ts";

export function baseOptions(): BaseLayoutProps {
	return {
		nav: {
			title: "MrDemonWolf for WordPress",
		},
		links: [
			{
				text: "Documentation",
				url: "/docs",
			},
			{
				text: "GitHub",
				url: GITHUB_REPO,
				external: true,
			},
		],
	};
}
