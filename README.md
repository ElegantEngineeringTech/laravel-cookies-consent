# Laravel Cookies Consent Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-cookies-consent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-cookies-consent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ElegantEngineeringTech/laravel-cookies-consent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ElegantEngineeringTech/laravel-cookies-consent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-cookies-consent.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-cookies-consent)

![laravel-cookies-consent](https://repository-images.githubusercontent.com/828522335/63d6882a-3b8f-4229-88d6-1e2f0d2eacee)

This package provides a simple yet extremely flexible way to manage cookie consent in your Laravel application.

The default cookie banner design requires Tailwind CSS and Alpine.js, but you can publish the component and customize it with your own stack.

[Demo here](https://elegantly.dev/laravel-cookies-consent)

## Requirements

### Backend

-   Laravel

### Frontend

The default cookie consent banner included in this package requires:

-   Blade components
-   [Alpine.js](https://alpinejs.dev/)
-   [Tailwind CSS](https://tailwindcss.com/)

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

## Backend Usage

In the backend, you will register the cookies and a callback associated with each of them.
This callback will be a JavaScript script to run when the consent is granted.

### Register Your Cookies

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
            name: __('cookies-consent::cookies.marketing.name'),
            description: __('cookies-consent::cookies.marketing.description'),
            items: [
                new CookieDefinition(
                    name: '_fbc',
                    lifetime: CarbonInterval::years(2),
                    description: __('cookies-consent::cookies._fbc.description')
                ),
                new CookieDefinition(
                    name: '_fbp',
                    lifetime: CarbonInterval::years(3),
                    description: __('cookies-consent::cookies._fbp.description')
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

### Registering Essential Cookies

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

### Registering Cookie Callbacks

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

## Frontend Usage

### Using the Default Cookie Banner

You can use the default cookie banner included with this package. It requires Alpine.js and Tailwind CSS. Cookies are read and written using the browser's native `document.cookie` API.

#### Alpine.js Requirement

The default banner implementation requires Alpine.js for reactivity. Ensure it is included in your page.

Simply put the banner component `<x-cookies-consent::banner />` at the end of your HTML page, and you are ready to go!

```php
    <!-- ... -->
    <x-cookies-consent::banner />
</body>
```

#### JavaScript Requirement

The banner relies on the `CookiesConsent` JavaScript class included in this package. It reads and writes the consent cookie and runs your `onAccepted` callbacks.

Add `@cookiesConsentScripts` to your `<head>`:

```php
<head>
    <!-- ... -->
    @cookiesConsentScripts
</head>
```

It inlines the compiled class (about 1 kB gzipped) and creates `window.cookiesConsent`, an instance configured with your registered cookie groups and the `cookie` config. There is nothing to build or publish. Register your groups before the `<head>` renders, for example in a middleware as shown above.

If your application uses a Content Security Policy, the directive reuses the nonce set with `Vite::useCspNonce()`. You can also pass one yourself: `@cookiesConsentScripts(nonce: $nonce)`.

#### Tailwindcss Requirement

The default banner is based on `elegantly/blade-kit` which is styled with tailwindcss.
You must then add the following paths to your tailwind `app.css`.

```css
@import "../../vendor/elegantly/blade-kit/resources/css/styles.css";
@source "../../vendor/elegantly/blade-kit/resources/views/**/*.blade.php";
@source "../../vendor/elegantly/laravel-cookies-consent/resources/views/**/*.blade.php;
```

### Customizing the Default Component

You can customize the default component by publishing the views:

```bash
php artisan vendor:publish --tag="cookies-consent-views"
```

If you previously published the banner, update cookie loops from `@foreach ($group as $cookie)` to `@foreach ($group->items as $cookie)`.

### Using a Custom Component

You can design your own frontend cookie banner.

To retrieve all the cookie definitions, simply call:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;

CookiesConsent::getDefinition();
```

Your component can rely on the `window.cookiesConsent` instance created by `@cookiesConsentScripts`, just like the default banner:

```js
const consent = window.cookiesConsent;

if (consent.isPending()) {
    // Display your banner, then call consent.acceptAll(), consent.acceptEssentials()
    // or consent.set("analytics", true).save()
} else {
    consent.runCallbacks();
}
```

The TypeScript source of the class lives in `vendor/elegantly/laravel-cookies-consent/resources/ts`, if you want to import it or its types.

## Google Tag (gtag.js) Consent Mode

The Google tag powers Google Analytics and Google Ads. Its [consent mode](https://developers.google.com/tag-platform/security/guides/consent) lets you deny storage by default and grant it once the user accepts.

### Example

This example maps the `analytics` group to `analytics_storage`, and the `marketing` group to `ad_storage`, `ad_user_data` and `ad_personalization`.

#### 1. Initialize consent from the stored choice

In your `<head>`, before the Google tag, set the default consent from the choice stored in the consent cookie. Groups the user hasn't accepted, including on their first visit, default to `denied`:

```blade
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    {
        const analytics = window.cookiesConsent.hasConsent('analytics') ? 'granted' : 'denied';
        const marketing = window.cookiesConsent.hasConsent('marketing') ? 'granted' : 'denied';

        gtag('consent', 'default', {
            'ad_storage': marketing,
            'ad_user_data': marketing,
            'ad_personalization': marketing,
            'analytics_storage': analytics,
        });
    }
</script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtagId }}"></script>
<script>
    gtag('js', new Date());
    gtag('config', '{{ $gtagId }}');
</script>
```

This assumes `@cookiesConsentScripts` is placed above it, so `window.cookiesConsent` is defined.

The banner only runs your `onAccepted` callbacks once Alpine.js starts, after the Google tag has sent its first hits. Reading the stored choice here ensures those hits use the right consent.

#### 2. Grant consent

In your middleware, register the `analytics` and `marketing` groups and update the consent in their `onAccepted` callbacks. They run as soon as the user accepts a group in the banner:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\CookieDefinition;
use Carbon\CarbonInterval;

CookiesConsent::register(new CookieGroupDefinition(
    key: 'analytics',
    name: __('cookies-consent::cookies.analytics.name'),
    description: __('cookies-consent::cookies.analytics.description'),
    items: [
        new CookieDefinition(
            name: '_ga',
            lifetime: CarbonInterval::years(2),
            description: __('cookies-consent::cookies._ga.description')
        ),
        new CookieDefinition(
            name: '_ga_ID',
            lifetime: CarbonInterval::years(2),
            description: __('cookies-consent::cookies._ga_ID.description')
        ),
    ],
    onAccepted: function () {
        return <<<'JS'
                gtag('consent', 'update', {
                    'analytics_storage': 'granted',
                });
            JS;
    },
))->register(new CookieGroupDefinition(
    key: 'marketing',
    name: __('cookies-consent::cookies.marketing.name'),
    description: __('cookies-consent::cookies.marketing.description'),
    items: [
        new CookieDefinition(
            name: '_gcl_au',
            lifetime: CarbonInterval::days(90),
            description: 'Used by Google Ads to measure conversions.'
        ),
    ],
    onAccepted: function () {
        return <<<'JS'
                gtag('consent', 'update', {
                    'ad_storage': 'granted',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                });
            JS;
    },
));
```

## Meta Pixel (fbq) Consent

The Meta Pixel measures conversions from your Facebook and Instagram ads. Its [consent API](https://developers.facebook.com/docs/meta-pixel/implementation/gdpr) pauses the Pixel with `fbq('consent', 'revoke')` until you call `fbq('consent', 'grant')`.

This guide covers the Pixel, which runs in the browser and sets cookies. Meta's [Conversions API](https://developers.facebook.com/docs/marketing-api/conversions-api/) sends events from your server instead.

### Example

This example maps the `marketing` group to the Pixel.

#### 1. Revoke consent unless it was granted

In your `<head>`, revoke consent before `fbq('init', ...)` unless the user has accepted the `marketing` group:

```blade
<!-- Meta Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    if (!window.cookiesConsent.hasConsent('marketing')) {
        fbq('consent', 'revoke');
    }

    fbq('init', '{{ $pixelId }}');
    fbq('track', 'PageView');
</script>
<!-- End Meta Pixel Code -->
```

This assumes `@cookiesConsentScripts` is placed above it, so `window.cookiesConsent` is defined.

Leave out the `<noscript>` image from Meta's snippet: it sends a `PageView` without running any JavaScript, so without the user's consent.

#### 2. Grant consent

In your middleware, register the `marketing` group and grant consent in its `onAccepted` callback. It runs as soon as the user accepts the group in the banner:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\CookieDefinition;
use Carbon\CarbonInterval;

CookiesConsent::register(new CookieGroupDefinition(
    key: 'marketing',
    name: __('cookies-consent::cookies.marketing.name'),
    description: __('cookies-consent::cookies.marketing.description'),
    items: [
        new CookieDefinition(
            name: '_fbc',
            lifetime: CarbonInterval::days(90),
            description: __('cookies-consent::cookies._fbc.description')
        ),
        new CookieDefinition(
            name: '_fbp',
            lifetime: CarbonInterval::days(90),
            description: __('cookies-consent::cookies._fbp.description')
        ),
    ],
    onAccepted: function () {
        return <<<'JS'
                fbq('consent', 'grant');
            JS;
    },
));
```

## TikTok Pixel (ttq) Consent

The TikTok Pixel measures conversions from your TikTok ads. Its [consent mode](https://business-api.tiktok.com/portal/docs/pixel-cookie-consent-mode/v1.3) queues events after `ttq.holdConsent()`, and sends them, including the queued ones, once you call `ttq.grantConsent()`.

### Example

This example maps the `marketing` group to the Pixel.

#### 1. Hold consent unless it was granted

In your `<head>`, start from the Pixel base code given by TikTok Events Manager and make two changes:

1. Add `"holdConsent"`, `"revokeConsent"` and `"grantConsent"` to the `ttq.methods` array, so these methods are queued until the Pixel loads.
2. Hold consent before `ttq.load(...)` unless the user has accepted the `marketing` group.

```blade
<!-- TikTok Pixel Code -->
<script>
    !function (w, d, t) {
        w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track", /* ... */ ,"holdConsent","revokeConsent","grantConsent"], /* ... the rest of the base code */

        if (!window.cookiesConsent.hasConsent('marketing')) {
            ttq.holdConsent();
        }

        ttq.load('{{ $pixelId }}');
        ttq.page();
    }(window, document, 'ttq');
</script>
<!-- End TikTok Pixel Code -->
```

This assumes `@cookiesConsentScripts` is placed above it, so `window.cookiesConsent` is defined.

#### 2. Grant consent

In your middleware, register the `marketing` group and grant consent in its `onAccepted` callback. The `PageView` queued before the user accepts is sent then:

```php
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\CookieDefinition;
use Carbon\CarbonInterval;

CookiesConsent::register(new CookieGroupDefinition(
    key: 'marketing',
    name: __('cookies-consent::cookies.marketing.name'),
    description: __('cookies-consent::cookies.marketing.description'),
    items: [
        new CookieDefinition(
            name: '_ttp',
            lifetime: CarbonInterval::months(13),
            description: 'Used by TikTok to identify the user.'
        ),
        new CookieDefinition(
            name: 'ttclid',
            lifetime: CarbonInterval::months(13),
            description: 'Used by TikTok to identify the advertisement through which the user arrived on the site.'
        ),
    ],
    onAccepted: function () {
        return <<<'JS'
                ttq.grantConsent();
            JS;
    },
));
```

## Combining Several Tags

Registering a group replaces any group with the same key. If you use several of these tags, register a single `marketing` group with the cookies of every tag, and grant consent to all of them in its callback:

```php
onAccepted: function () {
    return <<<'JS'
            gtag('consent', 'update', {
                'ad_storage': 'granted',
                'ad_user_data': 'granted',
                'ad_personalization': 'granted',
            });
            fbq('consent', 'grant');
            ttq.grantConsent();
        JS;
},
```

## References

[Google Guide: Set up consent mode on websites](https://developers.google.com/tag-platform/security/guides/consent)

[Meta Guide: General Data Protection Regulation](https://developers.facebook.com/docs/meta-pixel/implementation/gdpr)

[TikTok Guide: Pixel cookie consent mode](https://business-api.tiktok.com/portal/docs/pixel-cookie-consent-mode/v1.3)

## Testing

```bash
composer test
npm test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Quentin Gabriele](https://github.com/QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
