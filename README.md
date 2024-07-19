# Laravel cookies consent manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)

![laravel-cookies-consent](https://repository-images.githubusercontent.com/828522335/9fa1ca96-5277-4d84-ac2d-85d0fee19c50)

This package gives you a simple yet extremly flexible way to manage cookie consent in your Laravel app.

The default cookie banner design require Tailwind & Alpine, but you can publish the component and customize it with your own stack.

## Requirements

### Backend:

-   Laravel

### Frontend:

The default cookie consent banner included in this package require:

-   Blade component
-   [Alpine.js](https://alpinejs.dev/)
-   [tailwindcss](https://tailwindcss.com/)
-   [js-cookie](https://github.com/js-cookie/js-cookie)

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

## Usage

This package cover both backend and frontend cookie consent management.

You can chose to use the package only for the backend capabilities or for both.

### Backend

In the backend you will register the cookies and a callback associated with each of them.
This callback will be a javascript script to run when the consent is granted.

#### Register your cookies

First you should register all the cookies requiring the consent of the user.

To manage cookies, the package provides a service accessible via the Facade: `Elegantly\CookiesConsent\Facades\CookiesConsent`.

Cookies registration should be done in a middleware.
Doing it in a middleware will give you access to the app and request context. It will also let you chose the routes relying on those cookies.

To register your cookies, create a new middleware `App\Http\Middleware\RegisterCookiesConsent`.
In this middleware call `CookiesConsent::register` to register groups of cookies.

-   Cookies are always registered in groupes.
-   A cookie is always defined by its `name`, its `lifetime` and an optional `description`.
-   A cookie group can be defined as `required`. That cookies cannot be rejected by the user. It is usefull for cookies required to make the app work, like the session cookie for example.

For example, all cookies related to "Marketing" can be registered together like that:

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
        // Let's register cookies related to the Facebook pixel
        CookiesConsent::register(new CookieGroupDefinition(
                key: 'marketing',
                name: __('cookies-consent::translations.marketing.name'),
                description: __('cookies-consent::translations.marketing.description'),
                items: [
                    new CookieDefinition(
                        name: '_fbc',
                        lifetime: CarbonInterval::years(2),
                        description: __('cookies-consent::translations._fbc.description')
                    ),
                    new CookieDefinition(
                        name: '_fbp',
                        lifetime: CarbonInterval::years(3),
                        description: __('cookies-consent::translations._fbp.description')
                    ),
                ],
                onAccepted: function () {
                    return <<<'JS'
                            if(typeof fbq === 'function'){
                                fbq('consent', 'grant');
                            }
                        JS;
                },
            ));

        return $next($request);
    }
}
```

#### Registering essentials cookies

The package provides a preset for essantials cookies. Essantials cookies are cookies that can't be removed without compromising the application.
By default, Laravel includes 2 essantials cookies:

-   XSRF-TOKEN
-   session cookie

This package adds a third one:

-   consents (a cookie to store consents).

You can automatically register these three essantials cookies using:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::registerEssentials()
    ->register(
        // ... custom cookie definition
    )
```

#### Registering cookies callback

Using `onAccepted` param, you can freely define the javascript code to execute when the consent is granted to a specific cookie group.

In the previous example, we are granting consent using the facebook pixel.

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::register(new CookieGroupDefinition(
    // ...
    onAccepted: function () {
        return <<<'JS'
                // This javascript code will be executed when the consent is granted
                // do whatever you want here
                if(typeof fbq === 'function'){
                    fbq('consent', 'grant');
                }
            JS;
    },
));
```

### Frontend

#### Using the default Cookie banner

You can use the default cookie banner included with this package.

##### js-cookie Requirement

The default banner implementation require the [js-cookie](https://github.com/js-cookie/js-cookie) library to parse cookies in the browser.

Add it to your project using the cdn:

```html
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3/dist/js.cookie.min.js"></script>
```

Or see [their documentation](https://github.com/js-cookie/js-cookie) to install it via npm.

##### Alpine Requirement

The default banner implementation require Alpine for reactivity. ensure it is included in your page.

Simply put the banner component `<x-cookies-consent::banner />` at the end of your html page and you are ready to go !

```php
    //...
    <x-cookies-consent::banner />
</body>
```

#### Customize the default component

You can customer the default component by publishing the views:

```bash
php artisan vendor:publish --tag="cookies-consent-views"
```

#### Using a custom component

You could totally design your own frontend cookie banner.

To retreive all the cookies definitions simply call:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::getDefinition();
```

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
