import { RootProvider } from "fumadocs-ui/provider/next";
import type { ReactNode } from "react";
import "./global.css";

const description =
	"The official MrDemonWolf WordPress plugin. Accept form submissions over the REST API and send Expo push notifications to the MrDemonWolf app.";

export const metadata = {
	title: {
		default: "MrDemonWolf for WordPress",
		template: "%s | MrDemonWolf for WordPress",
	},
	description,
	openGraph: {
		title: "MrDemonWolf for WordPress",
		description,
		type: "website",
		siteName: "MrDemonWolf for WordPress",
	},
	twitter: {
		card: "summary_large_image",
		title: "MrDemonWolf for WordPress",
		description,
	},
};

export default function RootLayout({ children }: { children: ReactNode }) {
	return (
		<html lang="en" suppressHydrationWarning={true}>
			<body className="flex min-h-screen flex-col">
				{/* Search is disabled because the site is a static export. */}
				<RootProvider
					search={{
						enabled: false,
					}}
					theme={{
						defaultTheme: "dark",
					}}
				>
					{children}
				</RootProvider>
			</body>
		</html>
	);
}
