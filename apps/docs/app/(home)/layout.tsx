import { HomeLayout } from "fumadocs-ui/layouts/home";
import Link from "next/link";
import type { ReactNode } from "react";
import { baseOptions } from "@/lib/layout.shared.tsx";
import { DISCORD_URL, GITHUB_REPO } from "@/lib/constants.ts";

export default function Layout({ children }: { children: ReactNode }) {
	return (
		<HomeLayout {...baseOptions()}>
			{children}
			<footer className="mrdw-bg-surface mrdw-hairline border-t px-[8%] py-10 md:px-6">
				<div className="mrdw-text-2 mx-auto flex max-w-6xl flex-col gap-4 text-sm sm:flex-row sm:items-center sm:justify-between">
					<p>
						Made with love by{" "}
						<a className="mrdw-text-brand hover:underline" href="https://www.mrdemonwolf.com">
							MrDemonWolf, Inc.
						</a>
					</p>
					<nav className="flex flex-wrap gap-x-5 gap-y-2">
						<a className="hover:mrdw-text-1" href={GITHUB_REPO}>
							GitHub
						</a>
						<a className="hover:mrdw-text-1" href={DISCORD_URL}>
							Discord
						</a>
						<Link className="hover:mrdw-text-1" href="/docs">
							Docs
						</Link>
						<Link className="hover:mrdw-text-1" href="/docs/legal">
							Licence
						</Link>
					</nav>
				</div>
			</footer>
		</HomeLayout>
	);
}
