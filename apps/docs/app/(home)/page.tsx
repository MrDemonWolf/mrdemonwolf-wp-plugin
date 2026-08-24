import Link from "next/link";
import { Download, GitBranch, MessageSquare } from "lucide-react";
import { DISCORD_URL, DOWNLOAD_NIGHTLY, DOWNLOAD_STABLE, GITHUB_REPO } from "@/lib/constants.ts";

const modules = [
	{
		name: "Forms",
		body: "Accept form submissions from the MrDemonWolf app over the REST API, into Divi, WPForms or Gravity Forms. Requests are verified with Firebase App Check and constrained by form ID and origin allow-lists.",
		href: "/docs/forms",
	},
	{
		name: "Push",
		body: "Send Expo push notifications straight from WordPress. Devices register themselves, can be grouped, and sends can be scheduled or fired automatically when a post is published.",
		href: "/docs/push",
	},
];

export default function HomePage() {
	return (
		<main className="flex flex-1 flex-col items-center px-4 py-16">
			<div className="w-full max-w-3xl">
				<p className="text-fd-muted-foreground text-sm font-medium tracking-wide uppercase">
					Free and open source &middot; GPL-2.0-or-later
				</p>
				<h1 className="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">
					MrDemonWolf for WordPress
				</h1>
				<p className="text-fd-muted-foreground mt-4 text-lg">
					One plugin connecting the MrDemonWolf site to the MrDemonWolf app. It replaces the
					PackRelay and TailSignal plugins, which are now built in as the Forms and Push modules.
				</p>

				<div className="mt-8 flex flex-wrap gap-3">
					<a
						className="bg-fd-primary text-fd-primary-foreground inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-opacity hover:opacity-90"
						href={DOWNLOAD_STABLE}
					>
						<Download className="size-4" aria-hidden="true" />
						Download latest stable
					</a>
					<Link
						className="border-fd-border hover:bg-fd-accent inline-flex items-center gap-2 rounded-lg border px-5 py-2.5 text-sm font-semibold transition-colors"
						href="/docs/install"
					>
						Installation guide
					</Link>
				</div>
				<p className="text-fd-muted-foreground mt-3 text-sm">
					A <code>.zip</code> you upload under Plugins &rarr; Add New &rarr; Upload Plugin. Updates
					arrive through the normal WordPress updates screen.
				</p>

				<div className="mt-14 grid gap-4 sm:grid-cols-2">
					{modules.map((module) => (
						<Link
							key={module.name}
							href={module.href}
							className="border-fd-border hover:border-fd-primary/60 rounded-xl border p-5 transition-colors"
						>
							<h2 className="text-lg font-semibold">{module.name}</h2>
							<p className="text-fd-muted-foreground mt-2 text-sm">{module.body}</p>
						</Link>
					))}
				</div>
				<p className="text-fd-muted-foreground mt-4 text-sm">
					Either module can be switched off under <strong>MrDemonWolf &rarr; General</strong>{" "}
					without uninstalling the plugin or losing its data.
				</p>

				<h2 className="mt-14 text-xl font-semibold">Release channels</h2>
				<div className="mt-4 overflow-x-auto">
					<table className="w-full min-w-[34rem] text-left text-sm">
						<thead className="text-fd-muted-foreground">
							<tr className="border-fd-border border-b">
								<th className="py-2 pr-4 font-medium">Channel</th>
								<th className="py-2 pr-4 font-medium">What you get</th>
								<th className="py-2 font-medium">Download</th>
							</tr>
						</thead>
						<tbody>
							<tr className="border-fd-border border-b">
								<td className="py-3 pr-4 font-medium">Stable</td>
								<td className="text-fd-muted-foreground py-3 pr-4">
									Tagged, tested releases. The default for every site.
								</td>
								<td className="py-3">
									<a className="text-fd-primary hover:underline" href={DOWNLOAD_STABLE}>
										Latest zip
									</a>
								</td>
							</tr>
							<tr>
								<td className="py-3 pr-4 font-medium">Nightly</td>
								<td className="text-fd-muted-foreground py-3 pr-4">
									Built from <code>main</code> every day. Untested; for staging only.
								</td>
								<td className="py-3">
									<a className="text-fd-primary hover:underline" href={DOWNLOAD_NIGHTLY}>
										Pre-releases
									</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<p className="text-fd-muted-foreground mt-3 text-sm">
					Opt a site into nightly updates by adding{" "}
					<code>define( &apos;MRDW_UPDATE_CHANNEL&apos;, &apos;nightly&apos; );</code> to
					<code> wp-config.php</code>. See{" "}
					<Link className="text-fd-primary hover:underline" href="/docs/update-channels">
						update channels
					</Link>
					.
				</p>

				<div className="text-fd-muted-foreground mt-14 flex flex-wrap gap-6 text-sm">
					<a className="hover:text-fd-foreground inline-flex items-center gap-2" href={GITHUB_REPO}>
						<GitBranch className="size-4" aria-hidden="true" />
						Source on GitHub
					</a>
					<a className="hover:text-fd-foreground inline-flex items-center gap-2" href={DISCORD_URL}>
						<MessageSquare className="size-4" aria-hidden="true" />
						Get help on Discord
					</a>
				</div>
			</div>
		</main>
	);
}
