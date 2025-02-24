@use(\Elegantly\CookiesConsent\Facades\CookiesConsent)

@props([
    'policy' => config('cookies-consent.policy'),
    'name' => CookiesConsent::getCookieName(),
    'lifetime' => config('cookies-consent.cookie.lifetime') / (24 * 60),
    'defaults' => CookiesConsent::getDefaultConsents(),
    'cookies' => CookiesConsent::getDefinition(),
])

<div wire:ignore
    {{ $attributes->class(['fixed bottom-0 right-0 z-50 flex max-h-screen w-full max-w-full flex-col p-4 md:w-96']) }}
    x-data="{
        cookieName: @js($name),
        lifetime: @js($lifetime),
        consents: null,
        expanded: false,
        show: false,
        init() {
            this.consents = this.getConsents();
            this.show = this.shouldShow();
    
            if (!this.show) {
                this.runCallbacks();
            }
        },
        shouldShow() {
            const cookie = this.getValue();
    
            if (!cookie) {
                return true;
            }
    
            const defaultKeys = Object.keys(this.getDefaultConsents());
            const currentKeys = Object.keys(cookie['consents']);
    
            const diffKeys = defaultKeys.filter(x => !currentKeys.includes(x));
    
            return diffKeys.length > 0;
        },
        getCookie() {
            return Cookies.get(this.cookieName);
        },
        getValue() {
            const cookie = this.getCookie();
            return cookie ? JSON.parse(cookie) : null;
        },
        getDefaultConsents() {
            return @js($defaults);
        },
        getConsents() {
            const value = this.getValue();
            const defaultConsents = this.getDefaultConsents();
    
            if (value) {
                return {
                    ...defaultConsents,
                    ...value['consents'],
                };
            }
    
            // if no cookie have been set yet
            // grant all consent by default
            for (key in defaultConsents) {
                defaultConsents[key] = true;
            }
    
            return defaultConsents;
        },
        setCookie() {
            Cookies.set(
                this.cookieName,
                JSON.stringify({
                    set_at: Date.now(),
                    consents: this.consents,
                }), { expires: this.lifetime }
            );
        },
        runCallbacks() {
            for (const [key, value] of Object.entries(this.consents)) {
                if (value && Object.hasOwn(this.callbacks, key)) {
                    this.callbacks[key]();
                }
            }
        },
        acceptAll() {
            for (key in this.consents) {
                this.consents[key] = true;
            }
            this.save();
        },
        acceptEssentials() {
            this.consents = this.getDefaultConsents();
    
            this.save();
        },
        decline() {
            for (key in this.consents) {
                this.consents[key] = false;
            }
            this.save();
        },
        save() {
            this.setCookie();
            this.runCallbacks();
            this.show = false;
        },
        callbacks: {
            {{-- Do not use @foreach of @if or it will break when used with Livewire --}}
            {!! $cookies->map(fn($group) => "'{$group->key}': function(){\n" . value($group->onAccepted) . "\n}")->join(",\n") !!}
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
            <div class="divide-y border-t text-sm dark:divide-white/20 dark:border-white/20">
                @foreach ($cookies as $group)
                    <div class="p-4" x-data="{ expanded: false }">
                        <div class="mb-0.5 flex items-center text-base">
                            <p class="grow font-semibold">
                                {{ $group->name }}
                            </p>

                            <x-kit::switch id="consents.{{ $group->key }}" x-model="consents.{{ $group->key }}"
                                :disabled="$group->required" />
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
                            @foreach ($group as $cookie)
                                <div class="">
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
            <div class="border-t p-4 dark:border-white/20">
                <x-kit::button color="black" class="w-full justify-center rounded-md font-semibold ring-1 ring-inset"
                    x-on:click="save">
                    {{ __('cookies-consent::cookies.save') }}
                </x-kit::button>
            </div>

        </div>

    </div>
</div>
