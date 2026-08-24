/**
 * How a submission travels from the app to the site, and what gates it.
 */
export function FormFlow() {
	const steps = [
		{ label: "App", detail: "Contact form" },
		{ label: "App Check", detail: "Firebase token" },
		{ label: "WordPress", detail: "Divi · WPForms · GF" },
	];

	return (
		<div className="mrdw-window w-full p-5" aria-hidden="true">
			<div className="flex items-stretch gap-2">
				{steps.map((s, i) => (
					<div key={s.label} className="flex flex-1 items-center gap-2">
						<div className="mrdw-hairline flex-1 rounded-lg border p-3 text-center">
							<p className="mrdw-text-1 text-[0.8rem] font-semibold">{s.label}</p>
							<p className="mrdw-text-2 mt-0.5 text-[0.7rem]">{s.detail}</p>
						</div>
						{i < steps.length - 1 && (
							<svg viewBox="0 0 12 12" className="size-3 shrink-0" fill="var(--brand-600)">
								<path d="M4 2l4 4-4 4z" />
							</svg>
						)}
					</div>
				))}
			</div>

			<pre className="mrdw-code mt-4 text-[0.72rem]">
				<span className="mrdw-text-brand">POST</span> /wp-json/mrdw/v1/submit/42{"\n"}
				{"{ "}
				<span className="mrdw-text-2">&quot;app_check_token&quot;</span>: &quot;…&quot;,{" "}
				<span className="mrdw-text-2">&quot;fields&quot;</span>: {"{ … } }"}
			</pre>
		</div>
	);
}
