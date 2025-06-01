<?php

declare(strict_types=1);

namespace Elegantly\CookiesConsent;

use Carbon\CarbonInterval;
use Elegantly\CookiesConsent\Support\MemoizeProperties;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

class CookiesConsent
{
    use MemoizeProperties;

    /**
     * @param  string  $cookieName  The name of the cookie containing the consent state
     * @param  Collection<string, CookieGroupDefinition>  $definition
     */
    public function __construct(
        public string $cookieName,
        public Collection $definition = new Collection,
    ) {
        //
    }

    /**
     * @return Collection<string, CookieGroupDefinition>
     */
    public function getDefinition(): Collection
    {
        return $this->definition;
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    public function getCookie(): ?string
    {
        $value = Cookie::get($this->cookieName);

        return is_string($value) ? $value : null;
    }

    /**
     * @return null|array{ set_at: int, consents: array<string, bool> }
     */
    public function getValue(): ?array
    {
        return $this->memoize('value', function () {
            if ($cookie = $this->getCookie()) {
                /** @var null|array{ set_at: int, consents: array<string, bool> } $value */
                $value = json_decode($cookie, true);

                return $value;
            }

            return null;
        });
    }

    /**
     * @return array<string, bool>
     */
    public function getDefaultConsents(): array
    {
        return $this
            ->definition
            ->map(fn ($group) => $group->required)
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    public function getConsents(): array
    {
        $default = $this->getDefaultConsents();

        if ($value = $this->getValue()) {
            return Arr::map(
                $default,
                fn ($defaultValue, $key) => $defaultValue ? true : ($value['consents'][$key] ?? false),
            );
        }

        return $default;
    }

    public function hasConsent(string $key): bool
    {
        return (bool) Arr::get(
            $this->getConsents(),
            $key
        );
    }

    public function register(CookieGroupDefinition $group): static
    {
        $this->definition->put($group->key, $group);

        return $this;
    }

    public function registerEssentials(): static
    {
        return $this->register(
            new CookieGroupDefinition(
                key: 'essentials',
                name: __('cookies-consent::cookies.essentials.name'),
                description: __('cookies-consent::cookies.essentials.description'),
                required: true,
                items: [
                    new CookieDefinition(
                        name: $this->cookieName,
                        lifetime: CarbonInterval::minutes(config()->integer('cookies-consent.cookie.lifetime', 0))
                    ),
                    new CookieDefinition(
                        name: config()->string('session.cookie'),
                        // @phpstan-ignore-next-line
                        lifetime: CarbonInterval::minutes((int) config('session.lifetime', 0))
                    ),
                    new CookieDefinition(
                        name: 'XSRF-TOKEN',
                        // @phpstan-ignore-next-line
                        lifetime: CarbonInterval::minutes((int) config('session.lifetime', 0))
                    ),
                ]
            )
        );
    }
}
