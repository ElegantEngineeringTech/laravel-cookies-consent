<?php

namespace Elegantly\CookiesConsent;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

class CookiesConsent
{
    /**
     * @var Collection<string, CookieGroupDefinition>
     */
    public Collection $definition;

    /**
     * @param  string  $cookieName  The name of the cookie containing the consent state
     */
    public function __construct(
        public string $cookieName,
    ) {
        $this->definition = new Collection;
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
        return Cookie::get($this->cookieName);
    }

    public function getValue(): ?array
    {
        $cookie = $this->getCookie();

        return $cookie ? json_decode($cookie, true) : null;
    }

    public function getDefaultConsents(): array
    {
        return $this
            ->definition
            ->map(fn (CookieGroupDefinition $group) => $group->required)
            ->toArray();
    }

    public function getConsents(): array
    {
        $value = $this->getValue() ?? [];

        return array_merge(
            $this->getDefaultConsents(),
            Arr::get($value, 'consents') ?? []
        );
    }

    public function hasConsent(string $key): bool
    {
        $group = $this->definition->get($key);

        if (! $group) {
            return false;
        }

        if ($group->required) {
            return true;
        }

        $consents = $this->getConsents();

        return (bool) Arr::get($consents, $key);
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
                        lifetime: CarbonInterval::minutes(config('cookies-consent.cookie.lifetime'))
                    ),
                    new CookieDefinition(
                        name: config('session.cookie'),
                        lifetime: CarbonInterval::minutes(config('session.lifetime'))
                    ),
                    new CookieDefinition(
                        name: 'XSRF-TOKEN',
                        lifetime: CarbonInterval::minutes(config('session.lifetime'))
                    ),
                ]
            )
        );
    }
}
