import { RootProvider } from "fumadocs-ui/provider/next";
import { Instrument_Sans } from "next/font/google";
import type { ReactNode } from "react";
import { assetPath, SITE_URL } from "@/lib/constants.ts";
import "./global.css";

const instrument = Instrument_Sans({
	subsets: ["latin"],
	variable: "--font-instrument",
	display: "swap",
});

const description =
	"The free WordPress plugin that connects your site to the MrDemonWolf app. Accept form submissions over the REST API and send Expo push notifications with featured images.";

export const metadata = {
	metadataBase: new URL(SITE_URL),
	title: {
		default: "MrDemonWolf for WordPress",
		template: "%s | MrDemonWolf for WordPress",
	},
	description,
	icons: {
		icon: [
			{ url: assetPath("/icon.svg"), type: "image/svg+xml" },
			{ url: assetPath("/favicon.png"), type: "image/png" },
		],
		apple: assetPath("/favicon.png"),
	},
	openGraph: {
		title: "MrDemonWolf for WordPress",
		description,
		type: "website",
		siteName: "MrDemonWolf for WordPress",
		url: SITE_URL,
	},
	twitter: {
		card: "summary_large_image",
		title: "MrDemonWolf for WordPress",
		description,
	},
};

export default function RootLayout({ children }: { children: ReactNode }) {
	return (
		<html lang="en" className={instrument.variable} suppressHydrationWarning={true}>
			<body className="mrdw-font mrdw-bg-base flex min-h-screen flex-col">
				<a href="#nd-page" className="skip-nav">
					Skip to content
				</a>
				{/* Search is off because this is a static export with no search server. */}
				<RootProvider search={{ enabled: false }} theme={{ defaultTheme: "dark" }}>
					{children}
				</RootProvider>
			</body>
		</html>
	);
}
