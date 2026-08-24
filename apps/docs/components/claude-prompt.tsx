"use client";

import { Check, Copy } from "lucide-react";
import { useState } from "react";

/**
 * Copies a ready-made prompt for a single setup step.
 *
 * Paste it into Claude in your browser, or into Claude Code, and it has enough
 * context to carry out that step on its own. Each prompt has to stand alone —
 * whoever runs it will not have this page in front of them.
 */
export function ClaudePrompt({ prompt, label }: { prompt: string; label?: string }) {
	const [copied, setCopied] = useState(false);

	return (
		<div className="mrdw-card my-5 p-4">
			<div className="flex flex-wrap items-center justify-between gap-3">
				<div>
					<p className="mrdw-text-1 text-sm font-semibold">{label ?? "Let Claude do this step"}</p>
					<p className="mrdw-text-2 mt-0.5 text-[0.8rem]">
						Copy the prompt, paste it into Claude, and follow along.
					</p>
				</div>
				<button
					type="button"
					className="mrdw-btn mrdw-btn-primary shrink-0"
					onClick={() => {
						void navigator.clipboard.writeText(prompt.trim()).then(() => {
							setCopied(true);
							setTimeout(() => setCopied(false), 1600);
						});
					}}
				>
					{copied ? <Check className="size-4" /> : <Copy className="size-4" />}
					{copied ? "Copied" : "Copy prompt"}
				</button>
			</div>

			<details className="mt-3">
				<summary className="mrdw-text-2 cursor-pointer text-[0.8rem] select-none">
					See the prompt
				</summary>
				<pre className="mrdw-code mt-2 text-[0.75rem] whitespace-pre-wrap">{prompt.trim()}</pre>
			</details>
		</div>
	);
}
