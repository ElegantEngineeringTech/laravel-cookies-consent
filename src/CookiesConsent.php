<?php

declare(strict_types=1);

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
        $value = Cookie::get($this->cookieName);

        return is_array($value) ? null : $value;
    }

    /**
     * @return null|array{ set_at: int, consents: array<string, bool> }
     */
    public function getValue(): ?array
    {
        $cookie = $this->getCookie();

        if (! $cookie) {
            return null;
        }

        /** @var null|array{ set_at: int, consents: array<string, bool> } $value */
        $value = json_decode($cookie, true);

        return $value;
    }

    /**
     * @return array<string, bool>
     */
    public function getDefaultConsents(): array
    {
        return $this
            ->definition
            ->map(fn (CookieGroupDefinition $group) => $group->required)
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    public function getConsents(): array
    {

        if ($value = $this->getValue()) {
            return array_merge(
                $this->getDefaultConsents(),
                $value['consents']
            );
        }

        return [];
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
                        lifetime: CarbonInterval::minutes(config()->integer('cookies-consent.cookie.lifetime', 0))
                    ),
                    new CookieDefinition(
                        name: config()->string('session.cookie'),
                        lifetime: CarbonInterval::minutes(config()->integer('session.lifetime', 0))
                    ),
                    new CookieDefinition(
                        name: 'XSRF-TOKEN',
                        lifetime: CarbonInterval::minutes(config()->integer('session.lifetime', 0))
                    ),
                ]
            )
        );
    }
}
