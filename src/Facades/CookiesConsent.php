<?php

namespace Elegantly\CookiesConsent\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static CookiesConsent register(CookieGroupDefinition $group)
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
