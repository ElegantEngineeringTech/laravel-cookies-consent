@use(\Elegantly\CookiesConsent\Facades\CookiesConsent)

<div wire:ignore class="max-h-screen-navbar fixed bottom-0 right-0 z-10 flex w-full max-w-full flex-col p-4 md:w-96"
    x-data="{
        cookieName: @js(config('cookies-consent::translations.cookie.name')),
        lifetime: @js(config('cookies-consent::translations.cookie.lifetime') / (24 * 60)),
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
            return @js(CookiesConsent::getDefaultConsents());
        },
        getConsents() {
            const value = this.getValue();
            const consents = value ? value['consents'] : {};
    
            return {
                ...this.getDefaultConsents(),
                ...consents,
            };
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
                console.log(`${key}: ${value}`);
                if (value) {
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
        <!-- prettier-ignore-start -->
        callbacks: {
            @foreach(CookiesConsent::getDefinition() as $group)
            '{{ $group->key }}': function() {
                {!! value($group->onAccepted) !!}
            },
            @endforeach
        },
        <!-- prettier-ignore-end -->
    }" x-show="show" x-cloak x-on:cookies-consent.window="show = true">
    <div class="min-h-0 overflow-auto rounded-md bg-white shadow-md">
        <div class="p-4">
            <h2 class="mb-1 text-lg font-bold">
                {{ __('cookies-consent::translations.title') }}
            </h2>
            <p class="mb-3 text-sm">
                {{ __('cookies-consent::translations.intro') }} <br>
                {!! __('cookies-consent::translations.link', ['url' => route('privacy', ['locale' => 'fr'])]) !!}
            </p>
            <div class="grid grid-cols-2 gap-1">
                <button type="button" class="justify-center rounded-md font-semibold" x-on:click="acceptEssentials">
                    {{ __('cookies-consent::translations.accept_required') }}
                </button>

                <button type="button" class="justify-center rounded-md font-semibold" x-on:click="acceptAll">
                    {{ __('cookies-consent::translations.accept_all') }}
                </button>

                <button type="button" x-show="!expanded" class="col-span-2 justify-center rounded-md font-semibold"
                    x-on:click="expanded = !expanded">
                    {{ __('cookies-consent::translations.customize') }}
                </button>

                <button type="button" x-show="expanded" x-cloak
                    class="col-span-2 justify-center rounded-md font-semibold" x-on:click="save">
                    {{ __('cookies-consent::translations.save') }}
                </button>
            </div>
        </div>
        <div x-show="expanded" x-collapse x-cloak>
            <div class="divide-y border-t text-sm">
                @foreach (CookiesConsent::getDefinition() as $group)
                    <div class="p-4" x-data="{ expanded: false }">
                        <div class="mb-0.5 flex items-center text-base">
                            <p class="grow font-semibold">
                                {{ $group->name }}
                            </p>
                            <input type="checkbox" x-model="consents.{{ $group->key }}"
                                @disabled($group->required) />
                        </div>

                        <p class="mb-1 text-gray-600">
                            {{ $group->description }}
                        </p>

                        <button type="button" x-on:click="expanded = !expanded">
                            <span x-show="!expanded">
                                {{ __('cookies-consent::translations.details.more') }}
                            </span>
                            <span x-show="expanded" x-cloak>
                                {{ __('cookies-consent::translations.details.less') }}
                            </span>
                        </button>

                        <div class="flex flex-col gap-1" x-show="expanded" x-collapse x-cloak>
                            @foreach ($group as $cookie)
                                <div class="">
                                    <div class="flex gap-1">
                                        <p class="grow truncate">{{ $cookie->name }}</p>
                                        <p>{{ $cookie->formattedLifetime() }}</p>
                                    </div>

                                    <p class="text-xs text-gray-500">{{ $cookie->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t p-4">
                <button type="button" class="w-full justify-center rounded-md font-semibold" x-on:click="save">
                    {{ __('cookies-consent::translations.save') }}
                </button>
            </div>

        </div>

    </div>
</div>
