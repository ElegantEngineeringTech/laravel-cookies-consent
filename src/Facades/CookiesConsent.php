<?php

declare(strict_types=1);

namespace Elegantly\CookiesConsent\Facades;

use Elegantly\CookiesConsent\CookieGroupDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getCookieName()
 * @method static Collection<string, CookieGroupDefinition> getDefinition()
 * @method static CookiesConsent register(CookieGroupDefinition $group)
 * @method static CookiesConsent registerEssentials()
 * @method static string|null getCookie()
 * @method static null|array{ set_at: int, consents: array<string, bool> } getValue()
 * @method static array<string, bool> getDefaultConsents()
 * @method static array<string, bool> getConsents()
 * @method static bool hasConsent(string $key)
 *
 * @see \Elegantly\CookiesConsent\CookiesConsent
 */
class CookiesConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Elegantly\CookiesConsent\CookiesConsent::class;
    }
}
