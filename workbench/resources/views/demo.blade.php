<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{ Vite::useHotFile('vendor/cookies-consent/cookies-consent.hot')->useBuildDirectory('vendor/cookies-consent')->withEntryPoints('workbench/resources/css/demo.css') }}

    @cookiesConsentScripts

    <script defer src="https://cdn.jsdelivr.net/npm/@ryangjchandler/alpine-tooltip@1.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.min.css" />

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/resize@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/anchor@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/iconify-icon@2.3.0/dist/iconify-icon.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="bg-gray-100">
    <div class="py-8" x-data="{
        dark: false,
    }">

        <div class="*:has-checked:shadow-sm mx-auto flex w-min rounded-lg bg-zinc-200 p-1 *:rounded-md">
            <x-kit::button color-checked="white">
                <x-slot:input>
                    <input type="radio" name="segment" :value="0" x-model="dark" />
                </x-slot:input>
                <x-slot:icon>
                    <iconify-icon icon="lucide:sun"></iconify-icon>
                </x-slot:icon>
            </x-kit::button>

            <x-kit::button color-checked="white">
                <x-slot:input>
                    <input type="radio" name="segment" :value="1" x-model="dark" />
                </x-slot:input>
                <x-slot:icon>
                    <iconify-icon icon="lucide:moon"></iconify-icon>
                </x-slot:icon>
            </x-kit::button>
        </div>

        <x-cookies-consent::banner class="relative! flex! mx-auto"
            x-bind:class="{
                'dark': dark === '1'
            }" />

    </div>
</body>

</html>
