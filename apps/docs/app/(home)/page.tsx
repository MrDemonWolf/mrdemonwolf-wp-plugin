import Link from "next/link";
import { ArrowRight, Bell, Code2, Download, Inbox, MessageSquare } from "lucide-react";
import { AdminScreen } from "@/app/(home)/_mocks/AdminScreen.tsx";
import { FormFlow } from "@/app/(home)/_mocks/FormFlow.tsx";
import { PhoneNotification } from "@/app/(home)/_mocks/PhoneNotification.tsx";
import { DISCORD_URL, DOWNLOAD_NIGHTLY, DOWNLOAD_STABLE, GITHUB_REPO } from "@/lib/constants.ts";

function Kicker({ index, children }: { index: string; children: React.ReactNode }) {
	return (
		<span className="mrdw-kicker">
			<span className="mrdw-kicker-num">{index}</span>
			{children}
		</span>
	);
}

function FaqRow({ q, a }: { q: string; a: React.ReactNode }) {
	return (
		<details className="group mrdw-faq">
			<summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
				<span className="mrdw-text-1 text-base sm:text-lg">{q}</span>
				<span
					className="mrdw-text-brand text-2xl leading-none transition-transform group-open:rotate-45"
					aria-hidden="true"
				>
					+
				</span>
			</summary>
			<div className="mrdw-faq-body mrdw-text-2 text-base leading-relaxed">{a}</div>
		</details>
	);
}

export default function HomePage() {
	return (
		<main id="nd-page" tabIndex={-1} className="mrdw-font mrdw-bg-base">
			{/* Hero */}
			<section className="relative overflow-hidden">
				<div className="mrdw-hero-glow" aria-hidden="true" />
				<div className="relative z-10 px-[8%] pt-12 pb-16 sm:pt-20 sm:pb-24 md:px-6">
					<div className="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2 lg:gap-8">
						<div className="text-center lg:text-left">
							<p className="mrdw-reveal mrdw-reveal-1 mrdw-text-brand mb-5 text-sm font-semibold">
								Free and open source · GPL-2.0-or-later
							</p>
							<h1 className="mrdw-reveal mrdw-reveal-1 mrdw-hero-headline mrdw-text-1">
								Your WordPress site, <span className="mrdw-text-brand">in your app.</span>
							</h1>
							<p className="mrdw-reveal mrdw-reveal-2 mrdw-text-2 mx-auto mt-6 max-w-xl text-lg leading-relaxed sm:text-xl lg:mx-0">
								One plugin. Your app submits forms to your site, and your site pushes notifications
								back — with the post&apos;s featured image attached.
							</p>

							<div className="mrdw-reveal mrdw-reveal-3 mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
								<a className="mrdw-btn mrdw-btn-primary w-full sm:w-auto" href={DOWNLOAD_STABLE}>
									<Download className="size-4" />
									Download the plugin
								</a>
								<Link className="mrdw-btn mrdw-btn-secondary w-full sm:w-auto" href="/docs/setup">
									Set it up
									<ArrowRight className="size-4" />
								</Link>
							</div>

							<p className="mrdw-reveal mrdw-reveal-3 mrdw-text-2 mt-4 text-sm">
								A <code className="mrdw-mono">.zip</code> you upload in wp-admin. About 15 minutes.
							</p>

							<div className="mrdw-reveal mrdw-reveal-3 mt-6 flex flex-wrap items-center justify-center gap-2 lg:justify-start">
								<a className="mrdw-pill min-h-[36px]" href={GITHUB_REPO}>
									<Code2 className="size-3" />
									Open source
								</a>
								<span className="mrdw-pill">WordPress 6.0+ · PHP 8.3+</span>
								<span className="mrdw-pill">No licence key</span>
							</div>
						</div>

						<div className="mrdw-reveal mrdw-reveal-2">
							<PhoneNotification />
						</div>
					</div>
				</div>
			</section>

			{/* 01 — Modules */}
			<section id="modules" className="mrdw-bg-surface scroll-mt-20 px-[8%] py-16 sm:py-24 md:px-6">
				<div className="mx-auto max-w-6xl">
					<div className="mx-auto max-w-3xl text-center">
						<div className="mb-5 flex justify-center">
							<Kicker index="01">Two modules</Kicker>
						</div>
						<h2 className="mrdw-display mrdw-text-1 text-4xl sm:text-5xl">
							Turn on only what you need.
						</h2>
						<p className="mrdw-text-2 mt-5 text-lg leading-relaxed">
							Either module can be switched off without uninstalling the plugin or losing its data.
						</p>
					</div>

					<div className="mt-14 grid gap-5 md:grid-cols-2">
						{[
							{
								icon: Inbox,
								title: "Forms",
								body: "REST endpoints so your app can read a form's fields and submit to it. Submissions go to Divi, WPForms or Gravity Forms, and are recorded in a searchable entries table with CSV export.",
								href: "/docs/forms",
								cta: "Forms reference",
							},
							{
								icon: Bell,
								title: "Push",
								body: "A self-hosted Expo notification system. Devices register themselves, can be grouped, and sends can be scheduled or fired automatically when a post is published.",
								href: "/docs/push",
								cta: "Push reference",
							},
						].map(({ icon: Icon, title, body, href, cta }) => (
							<div key={title} className="mrdw-glass mrdw-card-hover flex flex-col p-8">
								<div
									className="mb-5 inline-flex size-11 items-center justify-center rounded-xl"
									style={{ backgroundColor: "var(--brand-50)", color: "var(--brand-600)" }}
								>
									<Icon className="size-5" />
								</div>
								<h3 className="mrdw-display mrdw-text-1 mb-2 text-xl">{title}</h3>
								<p className="mrdw-text-2 flex-1 text-base leading-relaxed">{body}</p>
								<Link
									href={href}
									className="mrdw-text-brand mt-5 inline-flex items-center gap-1.5 text-sm font-semibold"
								>
									{cta}
									<ArrowRight className="size-3.5" />
								</Link>
							</div>
						))}
					</div>

					<div className="mx-auto mt-10 max-w-2xl">
						<AdminScreen />
					</div>
				</div>
			</section>

			{/* 02 — Push */}
			<section className="mrdw-bg-base scroll-mt-20 px-[8%] py-16 sm:py-24 md:px-6">
				<div className="mx-auto grid max-w-6xl items-center gap-12 md:grid-cols-2 lg:gap-16">
					<div className="text-center md:text-left">
						<Kicker index="02">Push notifications</Kicker>
						<h2 className="mrdw-display mrdw-text-1 mt-5 text-4xl sm:text-5xl">
							Publish a post. Their phone lights up.
						</h2>
						<p className="mrdw-text-2 mt-5 text-lg leading-relaxed">
							The post&apos;s featured image rides along as a rich notification. Android renders it
							with no app changes; iOS needs a Notification Service Extension in your app.
						</p>
						<p className="mrdw-text-2 mt-3 text-base leading-relaxed">
							Sends can be scheduled, targeted at groups, or restricted to development devices while
							you test.
						</p>
						<Link
							href="/docs/push/sending"
							className="mrdw-text-brand mt-6 inline-flex items-center gap-1.5 text-sm font-semibold"
						>
							How sending works
							<ArrowRight className="size-3.5" />
						</Link>
					</div>
					<PhoneNotification />
				</div>
			</section>

			{/* 03 — Forms */}
			<section className="mrdw-bg-surface scroll-mt-20 px-[8%] py-16 sm:py-24 md:px-6">
				<div className="mx-auto grid max-w-6xl items-center gap-12 md:grid-cols-2 lg:gap-16">
					<div className="order-2 md:order-1">
						<FormFlow />
					</div>
					<div className="order-1 text-center md:order-2 md:text-left">
						<Kicker index="03">Forms</Kicker>
						<h2 className="mrdw-display mrdw-text-1 mt-5 text-4xl sm:text-5xl">
							Only your app gets through.
						</h2>
						<p className="mrdw-text-2 mt-5 text-lg leading-relaxed">
							Every submission carries a Firebase App Check token, verified on your server before
							anything is written. There is no API key to leak, because there is no API key.
						</p>
						<p className="mrdw-text-2 mt-3 text-base leading-relaxed">
							Form IDs and CORS origins are both allow-listed, so a stranger cannot walk your forms.
						</p>
						<Link
							href="/docs/forms/rest-api"
							className="mrdw-text-brand mt-6 inline-flex items-center gap-1.5 text-sm font-semibold"
						>
							REST API reference
							<ArrowRight className="size-3.5" />
						</Link>
					</div>
				</div>
			</section>

			{/* 04 — Channels */}
			<section className="mrdw-bg-base scroll-mt-20 px-[8%] py-16 sm:py-24 md:px-6">
				<div className="mx-auto max-w-4xl">
					<div className="text-center">
						<div className="mb-5 flex justify-center">
							<Kicker index="04">Updates</Kicker>
						</div>
						<h2 className="mrdw-display mrdw-text-1 text-4xl sm:text-5xl">
							Updates arrive where you expect them.
						</h2>
						<p className="mrdw-text-2 mt-5 text-lg leading-relaxed">
							Through the normal WordPress updates screen, straight from GitHub releases. No update
							server, no licence key.
						</p>
					</div>

					<div className="mt-10 grid gap-4 sm:grid-cols-2">
						<div className="mrdw-card p-6">
							<div className="mb-3 flex items-center gap-2">
								<h3 className="mrdw-text-1 text-lg font-semibold">Stable</h3>
								<span className="mrdw-pill">Default</span>
							</div>
							<p className="mrdw-text-2 text-sm leading-relaxed">
								Tagged, tested releases. What every production site should track.
							</p>
							<a className="mrdw-btn mrdw-btn-secondary mt-5 w-full" href={DOWNLOAD_STABLE}>
								<Download className="size-4" />
								Download zip
							</a>
						</div>

						<div className="mrdw-card p-6">
							<h3 className="mrdw-text-1 mb-3 text-lg font-semibold">Nightly</h3>
							<p className="mrdw-text-2 text-sm leading-relaxed">
								Built from <code className="mrdw-mono">main</code> daily. Untested — staging only.
							</p>
							<a className="mrdw-btn mrdw-btn-secondary mt-5 w-full" href={DOWNLOAD_NIGHTLY}>
								Browse pre-releases
							</a>
						</div>
					</div>

					<p className="mrdw-text-2 mt-6 text-center text-sm">
						Opt a site into nightly with{" "}
						<code className="mrdw-mono">
							define( &apos;MRDW_UPDATE_CHANNEL&apos;, &apos;nightly&apos; );
						</code>{" "}
						—{" "}
						<Link className="mrdw-text-brand hover:underline" href="/docs/update-channels">
							read more
						</Link>
						.
					</p>
				</div>
			</section>

			{/* 05 — FAQ */}
			<section id="faq" className="mrdw-bg-surface scroll-mt-20 px-[8%] py-16 sm:py-24 md:px-6">
				<div className="mx-auto max-w-3xl">
					<div className="text-center">
						<div className="mb-5 flex justify-center">
							<Kicker index="05">Questions</Kicker>
						</div>
						<h2 className="mrdw-display mrdw-text-1 text-4xl sm:text-5xl">Before you install.</h2>
					</div>

					<div className="mt-10">
						<FaqRow
							q="Do I need to know how to code?"
							a={
								<>
									No. The{" "}
									<Link className="mrdw-text-brand hover:underline" href="/docs/setup">
										setup guide
									</Link>{" "}
									walks through every screen, and each step has a button that copies a prompt you
									can hand to Claude to do it for you.
								</>
							}
						/>
						<FaqRow
							q="Is it really free?"
							a="Yes. GPL-2.0-or-later, the same licence as WordPress. No licence key, no activation, no usage tracking, no paid tier."
						/>
						<FaqRow
							q="Will the featured image show on iPhone?"
							a="Only if your app includes a Notification Service Extension. That is an Apple requirement, not a plugin limitation — the plugin always sends the image. Android shows it with no app changes."
						/>
						<FaqRow
							q="What leaves my server?"
							a="Push tokens and message text go to Expo, because that is how delivery works. GitHub is contacted to check for plugin updates. Form submissions and device records stay in your own database."
						/>
						<FaqRow
							q="Do I need both modules?"
							a="No. Turn either off under MrDemonWolf → General. A disabled module registers no hooks and no REST routes, and its data is left untouched."
						/>
					</div>
				</div>
			</section>

			{/* CTA */}
			<section className="mrdw-bg-base scroll-mt-20 px-[8%] py-28 sm:py-36 md:px-6">
				<div className="mx-auto max-w-4xl text-center">
					<div
						className="mx-auto mb-6 inline-flex size-12 items-center justify-center rounded-2xl"
						style={{ backgroundColor: "var(--brand-50)", color: "var(--brand-600)" }}
					>
						<Bell className="size-5" />
					</div>
					<h2 className="mrdw-display mrdw-text-1 text-5xl sm:text-6xl">
						Hit publish.
						<br />
						<span className="mrdw-text-brand">They&apos;ll know.</span>
					</h2>
					<div className="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
						<a className="mrdw-btn mrdw-btn-primary w-full sm:w-auto" href={DOWNLOAD_STABLE}>
							<Download className="size-4" />
							Download the plugin
						</a>
						<Link className="mrdw-btn mrdw-btn-secondary w-full sm:w-auto" href="/docs/setup">
							Read the setup guide
						</Link>
						<a className="mrdw-btn mrdw-btn-ghost w-full sm:w-auto" href={DISCORD_URL}>
							<MessageSquare className="size-4" />
							Get help
						</a>
					</div>
					<p className="mrdw-text-2 mt-5 text-sm">Free forever · WordPress 6.0+ · PHP 8.3+</p>
				</div>
			</section>
		</main>
	);
}
