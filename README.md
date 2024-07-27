# Laravel Cookies Consent Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-cookies-consent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-cookies-consent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)

![laravel-cookies-consent](https://repository-images.githubusercontent.com/828522335/9fa1ca96-5277-4d84-ac2d-85d0fee19c50)

This package provides a simple yet extremely flexible way to manage cookie consent in your Laravel application.

The default cookie banner design requires Tailwind CSS and Alpine.js, but you can publish the component and customize it with your own stack.

## Requirements

### Backend

-   Laravel

### Frontend

The default cookie consent banner included in this package requires:

-   Blade components
-   [Alpine.js](https://alpinejs.dev/)
-   [Tailwind CSS](https://tailwindcss.com/)
-   [js-cookie](https://github.com/js-cookie/js-cookie)

## Installation

You can install the package via Composer:

```bash
composer require elegantly/laravel-cookies-consent
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="cookies-consent-config"
```

This is the content of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | URL Configuration
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
    | Consent Cookie Configuration
    |--------------------------------------------------------------------------
    |
    | To keep track of the user's preferences, this package stores
    | an anonymized cookie. You do not need to register this cookie in the
    | package's cookie manager as it is done automatically (under "essentials").
    |
    | The duration parameter represents the cookie's lifetime in minutes.
    |
    | The domain parameter, when defined, determines the cookie's activity domain.
    | For multiple sub-domains, prefix your domain with "." (e.g., ".mydomain.com").
    |
    */

    'cookie' => [
        'name' => Str::slug(env('APP_NAME', 'laravel'), '_').'_cookiesconsent',
        'lifetime' => 60 * 24 * 365,
        'domain' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Legal Page Configuration
    |--------------------------------------------------------------------------
    |
    | Most cookie notices display a link to a dedicated page explaining
    | the extended cookies usage policy. If your application has such a page,
    | you can add its route name here.
    |
    */

    'policy' => null,

];
```

## Usage

This package covers both backend and frontend cookie consent management.

You can choose to use the package only for backend capabilities or for both.

### Backend

In the backend, you will register the cookies and a callback associated with each of them.
This callback will be a JavaScript script to run when the consent is granted.

#### Register Your Cookies

First, you should register all the cookies requiring user consent.

To manage cookies, the package provides a service accessible via the Facade: `Elegantly\CookiesConsent\Facades\CookiesConsent`.

Cookie registration should be done in middleware to access the app and request context. This also allows you to choose the routes relying on those cookies.

To register your cookies, create a new middleware `App\Http\Middleware\RegisterCookiesConsent`.
In this middleware, call `CookiesConsent::register` to register groups of cookies.

-   Cookies are always registered in groups.
-   A cookie is defined by its `name`, `lifetime`, and an optional `description`.
-   A cookie group can be defined as `required`. Such cookies cannot be rejected by the user, which is useful for essential cookies like the session cookie.

For example, all cookies related to "Marketing" can be registered together:

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
        // Register cookies related to the Facebook pixel
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
                    if (typeof fbq === 'function') {
                        fbq('consent', 'grant');
                    }
                JS;
            },
        ));

        return $next($request);
    }
}
```

#### Registering Essential Cookies

The package provides a preset for essential cookies. Essential cookies are those that cannot be removed without compromising the application.
By default, Laravel includes 2 essential cookies:

-   `XSRF-TOKEN`
-   Session cookie

This package adds a third one:

-   Consents (a cookie to store consents).

You can automatically register these three essential cookies using:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::registerEssentials()
    ->register(
        // ... custom cookie definition
    )
```

#### Registering Cookie Callbacks

Using the `onAccepted` parameter, you can define the JavaScript code to execute when consent is granted to a specific cookie group.

In the previous example, we grant consent using the Facebook pixel.

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::register(new CookieGroupDefinition(
    // ...
    onAccepted: function () {
        return <<<'JS'
            // This JavaScript code will be executed when consent is granted
            if (typeof fbq === 'function') {
                fbq('consent', 'grant');
            }
        JS;
    },
));
```

### Frontend

#### Using the Default Cookie Banner

You can use the default cookie banner included with this package.

##### js-cookie Requirement

The default banner implementation requires the [js-cookie](https://github.com/js-cookie/js-cookie) library to parse cookies in the browser.

Add it to your project using the CDN:

```html
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3/dist/js.cookie.min.js"></script>
```

Or see [their documentation](https://github.com/js-cookie/js-cookie) to install it via npm.

##### Alpine.js Requirement

The default banner implementation requires Alpine.js for reactivity. Ensure it is included in your page.

Simply put the banner component `<x-cookies-consent::banner />` at the end of your HTML page, and you are ready to go!

```php
    <!-- ... -->
    <x-cookies-consent::banner />
</body>
```

#### Customizing the Default Component

You can customize the default component by publishing the views:

```bash
php artisan vendor:publish --tag="cookies-consent-views"
```

#### Using a Custom Component

You can design your own frontend cookie banner.

To retrieve all the cookie definitions, simply call:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::getDefinition();
```

## Facebook Pixel cookie consent

### About

The Facebook Pixel track users and conversions on the client side. [Documentation available here](https://developers.facebook.com/docs/meta-pixel).

This is the historic way to track conversions, Facebook & Meta now also provides a way to track your conversions directly from your backend. It is called "API conversions" and [the documentation is avaible here](https://developers.facebook.com/docs/marketing-api/conversions-api/).

This example will only cover the The Facebook Pixel as the "API conversions" do not need cookie consent.

### Example

The Pixel provide a built-in manager for consent. This example rely on this.

### 1. Revoke consent on load

Before calling `fbq('init', ...)` and immediatly after the Pixel script, **revoke** the consent:

```html
<!-- Facebook Pixel Code -->
<!-- prettier-ignore -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');

  // Revoke consent before init
  fbq("consent", "revoke"); 
  
  // Then call your logic as usual
  fbq('init', '{your-pixel-id-goes-here}');
  fbq('track', 'PageView');
</script>
<!-- End Facebook Pixel Code -->
```

### 2. Grant consent

In your middleware, register a cookie group and call `fbq('consent', 'grant')` in the `onAccepted` callback.
Every call to `fbq` done before the consent will be triggered after `fbq('consent', 'grant')` is called.

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\CookieDefinition;
use Carbon\CarbonInterval;

CookiesConsent::register(new CookieGroupDefinition(
    key: 'marketing', // custoomize this value if you want
    name: __('cookies-consent::translations.marketing.name'), // custoomize this value if you want
    description: __('cookies-consent::translations.marketing.description'), // custoomize this value if you want
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
```

### References

[Facebook Guide: General Data Protection Regulation](https://developers.facebook.com/docs/meta-pixel/implementation/gdpr)

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
