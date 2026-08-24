/**
 * A push notification as it lands on a phone, with the post's featured image.
 * Pure CSS/SVG — no screenshot to go stale.
 */
export function PhoneNotification() {
	return (
		<div className="mrdw-phone mx-auto w-full max-w-[300px]" aria-hidden="true">
			<div className="mrdw-bg-surface relative overflow-hidden rounded-[22px] px-3 pt-6 pb-8">
				<div className="mrdw-text-2 mb-1 text-center text-[0.7rem] font-medium tracking-wide">
					now
				</div>
				<div className="mrdw-text-1 mb-6 text-center text-5xl font-semibold tabular-nums">9:41</div>

				<div className="mrdw-notif">
					{/* The featured image, as the Notification Service Extension attaches it. */}
					<div
						className="h-24 w-full"
						style={{
							background:
								"linear-gradient(135deg, var(--brand-500) 0%, var(--brand-600) 45%, #091533 100%)",
						}}
					/>
					<div className="flex gap-2.5 p-3">
						<div
							className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-md"
							style={{ backgroundColor: "var(--brand-50)" }}
						>
							<svg viewBox="0 0 15 15" className="size-4" fill="var(--brand-600)">
								<circle cx="7.5" cy="7.5" r="6" />
							</svg>
						</div>
						<div className="min-w-0">
							<p className="mrdw-text-1 truncate text-[0.8rem] font-semibold">New post published</p>
							<p className="mrdw-text-2 text-[0.75rem] leading-snug">
								Building a WordPress plugin that talks to an Expo app
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
