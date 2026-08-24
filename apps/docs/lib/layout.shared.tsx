import type { BaseLayoutProps } from "fumadocs-ui/layouts/shared";
import { assetPath, DISCORD_URL, GITHUB_REPO } from "@/lib/constants.ts";

export function baseOptions(): BaseLayoutProps {
	return {
		nav: {
			title: (
				<span data-mrdw-brand>
					{/* Plain img: static export disables next/image optimisation. */}
					<img src={assetPath("/logo.svg")} alt="" aria-hidden="true" className="h-7 w-auto" />
					<span className="mrdw-display text-[1.05rem] font-semibold tracking-tight">
						MrDemonWolf
					</span>
				</span>
			),
			url: "/",
			transparentMode: "top",
		},
		githubUrl: GITHUB_REPO,
		links: [
			{ text: "Setup", url: "/docs/setup", active: "nested-url" },
			{ text: "Docs", url: "/docs", active: "nested-url" },
			{ text: "Download", url: "/download", active: "url" },
			{ text: "Support", url: DISCORD_URL },
		],
		themeSwitch: { enabled: true, mode: "light-dark-system" },
	};
}
