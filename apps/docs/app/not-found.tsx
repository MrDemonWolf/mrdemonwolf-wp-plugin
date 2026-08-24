import Link from "next/link";

export default function NotFound() {
	return (
		<main className="mrdw-font mrdw-bg-base flex min-h-screen flex-col items-center justify-center px-6 text-center">
			<p className="mrdw-text-brand mrdw-mono text-sm font-semibold">404</p>
			<h1 className="mrdw-display mrdw-text-1 mt-3 text-4xl sm:text-5xl">
				That page moved, or never existed.
			</h1>
			<p className="mrdw-text-2 mt-4 max-w-md text-lg">
				The docs were reorganised recently, so an old link may be pointing somewhere that is gone.
			</p>
			<div className="mt-8 flex flex-col gap-3 sm:flex-row">
				<Link className="mrdw-btn mrdw-btn-primary" href="/docs">
					Browse the docs
				</Link>
				<Link className="mrdw-btn mrdw-btn-secondary" href="/">
					Back home
				</Link>
			</div>
		</main>
	);
}
