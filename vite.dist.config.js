import path from "path";
import { fileURLToPath } from "url";
import { defineConfig } from "vite";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Builds the script inlined by `@cookiesConsentScripts`. The output is committed.
 */
export default defineConfig({
    publicDir: false,
    build: {
        outDir: "dist",
        emptyOutDir: true,
        lib: {
            entry: path.resolve(__dirname, "./resources/ts/global.ts"),
            name: "CookiesConsent",
            formats: ["iife"],
            fileName: () => "cookies-consent.js",
        },
    },
});
