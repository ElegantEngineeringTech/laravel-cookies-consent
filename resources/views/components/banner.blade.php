@use(\Elegantly\CookiesConsent\Facades\CookiesConsent)

@props([
    'cookies' => CookiesConsent::getDefinition(),
    'policy' => config('cookies-consent.policy'),
])

<div wire:ignore
    {{ $attributes->class(['fixed bottom-0 right-0 z-50 flex max-h-screen w-full max-w-full flex-col p-4 md:w-96']) }}
    x-data="{
        expanded: false,
        show: false,
        consent: null,
        init() {
            if (!window.cookiesConsent) {
                throw new Error('[cookies-consent] window.cookiesConsent is not defined, add @@cookiesConsentScripts to your head.');
            }
    
            this.consent = window.cookiesConsent;
            this.show = this.consent.isPending();
    
            if (this.show === false) {
                this.consent.runCallbacks();
            }
        },
        acceptAll() {
            this.consent.acceptAll();
            this.show = false;
        },
        acceptEssentials() {
            this.consent.acceptEssentials();
            this.show = false;
        },
        save() {
            this.consent.save();
            this.show = false;
        },
    }" x-show="show" x-cloak x-on:cookies-consent.window="show = true">
    <div class="min-h-0 overflow-auto rounded-md bg-white shadow-md dark:bg-zinc-900 dark:text-white">
        <div class="p-4">
            <h2 class="mb-1 text-lg font-bold">
                {{ __('cookies-consent::cookies.title') }}
            </h2>
            <p class="mb-3 text-sm dark:text-white/50">
                {{ __('cookies-consent::cookies.intro') }}
                @if ($policy)
                    <br>
                    {!! __('cookies-consent::cookies.link', ['url' => $policy]) !!}
                @endif
            </p>
            <div class="grid grid-cols-2 gap-1">
                <x-kit::button color="white" class="justify-center rounded-md font-semibold ring-1 ring-inset"
                    x-on:click="acceptEssentials">
                    {{ __('cookies-consent::cookies.accept_required') }}
                </x-kit::button>

                <x-kit::button color="black" class="justify-center rounded-md font-semibold ring-1 ring-inset"
                    x-on:click="acceptAll">
                    {{ __('cookies-consent::cookies.accept_all') }}
                </x-kit::button>

                <x-kit::button color="white" x-show="!expanded"
                    class="col-span-2 justify-center rounded-md font-semibold ring-1 ring-inset"
                    x-on:click="expanded = !expanded">
                    {{ __('cookies-consent::cookies.customize') }}
                </x-kit::button>

                <x-kit::button color="black" x-show="expanded" x-cloak
                    class="col-span-2 justify-center rounded-md font-semibold ring-1 ring-inset" x-on:click="save">
                    {{ __('cookies-consent::cookies.save') }}
                </x-kit::button>
            </div>
        </div>
        <div x-show="expanded" x-collapse x-cloak>
            <div
                class="divide-y divide-gray-200 border-t border-gray-200 text-sm dark:divide-white/20 dark:border-white/20">
                @foreach ($cookies as $group)
                    <div class="p-4" x-data="{ expanded: false }">
                        <div class="mb-0.5 flex items-center text-base">
                            <p class="grow font-semibold">
                                {{ $group->name }}
                            </p>

                            <x-kit::switch id="consents.{{ $group->key }}"
                                x-model="consent.consents.{{ $group->key }}" :disabled="$group->required" />
                        </div>

                        <p class="mb-1 text-black/50 dark:text-white/50">
                            {{ $group->description }}
                        </p>

                        <button type="button" x-on:click="expanded = !expanded">
                            <span x-show="!expanded">
                                {{ __('cookies-consent::cookies.details.more') }}
                            </span>
                            <span x-show="expanded" x-cloak>
                                {{ __('cookies-consent::cookies.details.less') }}
                            </span>
                        </button>

                        <div class="flex flex-col gap-1" x-show="expanded" x-collapse x-cloak>
                            @foreach ($group->items as $cookie)
                                <div>
                                    <div class="flex gap-1">
                                        <p class="grow truncate">{{ $cookie->name }}</p>
                                        <p>{{ $cookie->formattedLifetime() }}</p>
                                    </div>

                                    <p class="text-xs text-black/50 dark:text-white/50">{{ $cookie->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-200 p-4 dark:border-white/20">
                <x-kit::button color="black" class="w-full justify-center rounded-md font-semibold ring-1 ring-inset"
                    x-on:click="save">
                    {{ __('cookies-consent::cookies.save') }}
                </x-kit::button>
            </div>

        </div>

    </div>
</div>
