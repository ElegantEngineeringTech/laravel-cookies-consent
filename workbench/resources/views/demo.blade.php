<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @custom-variant dark (&:where(.dark, .dark *));

        html {
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            text-size-adjust: 100%;
        }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/iconify-icon@2.3.0/dist/iconify-icon.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/@ryangjchandler/alpine-tooltip@1.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.min.css" />

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/resize@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/anchor@3.x.x/dist/cdn.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        <x-kit::segments color="custom" class="mx-auto w-min rounded-full bg-zinc-200">
            <x-kit::segments.button x-model="dark" class="rounded-full font-semibold" name="segment" :value="0">
                <x-slot:icon>
                    <iconify-icon icon="lucide:sun"></iconify-icon>
                </x-slot:icon>
            </x-kit::segments.button>
            <x-kit::segments.button x-model="dark" class="rounded-full font-semibold" name="segment" :value="1"
                color="black">
                <x-slot:icon>
                    <iconify-icon icon="lucide:moon"></iconify-icon>
                </x-slot:icon>
            </x-kit::segments.button>
        </x-kit::segments>

        <x-cookies-consent::banner class="!relative mx-auto !flex"
            x-bind:class="{
                'dark': dark === '1'
            }" />

    </div>
</body>

</html>
