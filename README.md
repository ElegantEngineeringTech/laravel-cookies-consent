# This is my package laravel-cookies-consent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)

Cookies consent manager for Laravel.

The default cookie banner design require Tailwind & Alpine, but you can publish the component and customize it with your own stack.

## Installation

You can install the package via composer:

```bash
composer require elegantly/laravel-cookies-consent
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="cookies-consent-config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | URL configuration
    |--------------------------------------------------------------------------
    |
    | These values determine the package's API route URLs. Both values are
    | nullable and represent the same concepts as Laravel's routing parameters.
    |
    */

    'url' => [
        'domain' => null,
        'prefix' => 'cookiesconsent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent cookie configuration
    |--------------------------------------------------------------------------
    |
    | In order to keep track of the user's preferences, this package stores
    | an anonymized cookie. You do not need to register this cookie in the
    | package's cookie manager as it is done automatically (under "essentials").
    |
    | The duration parameter represents the cookie's lifetime in minutes.
    |
    | The domain parameter, when defined, determines the cookie's activity domain.
    | For multiple sub-domains, prefix your domain with "." (eg: ".mydomain.com").
    |
    */

    'cookie' => [
        'name' => Str::slug(env('APP_NAME', 'laravel'), '_').'_cookiesconsent',
        'lifetime' => 60 * 24 * 365,
        'domain' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Legal page configuration
    |--------------------------------------------------------------------------
    |
    | Most cookie notices display a link to a dedicated page explaining
    | the extended cookies usage policy. If your application has such a page
    | you can add its route name here.
    |
    */

    'policy' => null,

];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="cookies-consent-views"
```

## Usage

This package rely on two concepts:

-   You register all the cookies you want to manage with the banner.
-   You include the `<x-cookies-consent::banner />` component on your pages

### Register your cookies

Cookies registration should be done in a middleware. This will give you access to the app/request context, and it will let you chose the routes relying on cookies.

Create a new middleware `App\Http\Middleware\RegisterCookiesConsent` and register your cookies like in this example:

```php
namespace App\Http\Middleware;

use Carbon\CarbonInterval;
use Closure;
use Elegantly\CookiesConsent\CookieDefinition;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterCookiesConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        CookiesConsent::registerEssentials()
            ->register(new CookieGroupDefinition(
                key: 'marketing',
                name: __('cookiesconsent.marketing.name'),
                description: __('cookiesconsent.marketing.description'),
                items: [
                    new CookieDefinition(
                        name: '_fbc',
                        lifetime: CarbonInterval::years(2),
                        description: __('cookiesconsent._fbc.description')
                    ),
                    new CookieDefinition(
                        name: '_fbp',
                        lifetime: CarbonInterval::years(3),
                        description: __('cookiesconsent._fbp.description')
                    ),
                ],
                onAccepted: function () {
                    return <<<'JS'
                            if(typeof fbq === 'function'){
                                fbq('consent', 'grant');
                            }
                            if(typeof gtag === 'function'){
                                gtag('consent', 'update', {
                                    'ad_storage': 'granted',
                                    'ad_user_data': 'granted',
                                    'ad_personalization': 'granted',
                                });
                            }
                        JS;
                },
            ));

        return $next($request);
    }
}
```

#### Execute code when the consent is granted

Using `onAccepted` param, you can freely define the javascript code to execute when the consent is granted to a specific cookie group.

In the previous example, we are granting consent using the facebook pixel and google tag manager.

```php
new CookieGroupDefinition(
    //...
    onAccepted: function () {
        return <<<'JS'
                if(typeof fbq === 'function'){
                    fbq('consent', 'grant');
                }
                if(typeof gtag === 'function'){
                    gtag('consent', 'update', {
                        'ad_storage': 'granted',
                        'ad_user_data': 'granted',
                        'ad_personalization': 'granted',
                    });
                }
            JS;
    },
)
```

### Include

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Quentin Gabriele](https://github.com/40128136+QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
