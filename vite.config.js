import path from "path";
import { fileURLToPath } from "url";
import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/ts"),
            "@workbench": path.resolve(__dirname, "./workbench/resources/js"),
        },
    },
    plugins: [
        tailwindcss(),
        laravel({
            hotFile: "public/vendor/cookies-consent/cookies-consent.hot",
            buildDirectory: "vendor/cookies-consent",
            input: ["workbench/resources/css/demo.css"],
            refresh: true,
        }),
    ],
});
