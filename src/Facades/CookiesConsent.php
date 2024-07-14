<?php

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
 * @method static array|null getValue()
 * @method static array getDefaultConsents()
 * @method static array getConsents()
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
