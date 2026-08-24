/**
 * The MrDemonWolf → General screen, drawn rather than screenshotted so it
 * cannot drift out of date with the plugin's actual markup.
 */
export function AdminScreen() {
	const modules = [
		{ name: "Forms", desc: "Accept form submissions over the REST API", on: true },
		{ name: "Push", desc: "Send Expo push notifications", on: true },
	];

	return (
		<div className="mrdw-window w-full" aria-hidden="true">
			<div className="mrdw-window-bar">
				<span className="mrdw-dot" style={{ backgroundColor: "#ff5f57" }} />
				<span className="mrdw-dot" style={{ backgroundColor: "#febc2e" }} />
				<span className="mrdw-dot" style={{ backgroundColor: "#28c840" }} />
				<span className="mrdw-mono mrdw-text-2 ml-2 truncate text-[0.7rem]">
					/wp-admin/admin.php?page=mrdw
				</span>
			</div>

			<div className="p-5">
				<h3 className="mrdw-text-1 mb-1 text-lg font-semibold">MrDemonWolf</h3>
				<p className="mrdw-text-2 mb-4 text-[0.8rem]">
					Turning a module off unregisters its hooks and REST routes. Stored data is left alone.
				</p>

				<div className="space-y-2.5">
					{modules.map((m) => (
						<div
							key={m.name}
							className="mrdw-hairline flex items-start gap-3 rounded-lg border p-3"
						>
							<span
								className="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-[4px]"
								style={{ backgroundColor: "var(--brand-fill)" }}
							>
								<svg
									viewBox="0 0 12 12"
									className="size-3"
									fill="none"
									stroke="#fff"
									strokeWidth="2"
								>
									<path d="M2.5 6.2l2.3 2.3 4.7-5" strokeLinecap="round" strokeLinejoin="round" />
								</svg>
							</span>
							<div>
								<p className="mrdw-text-1 text-[0.85rem] font-semibold">{m.name}</p>
								<p className="mrdw-text-2 text-[0.78rem]">{m.desc}</p>
							</div>
						</div>
					))}
				</div>

				<div className="mrdw-hairline mt-4 border-t pt-3">
					<span className="mrdw-pill">Update channel: stable</span>
				</div>
			</div>
		</div>
	);
}
