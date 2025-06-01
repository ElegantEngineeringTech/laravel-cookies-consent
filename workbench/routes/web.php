<?php

declare(strict_types=1);

use Carbon\CarbonInterval;
use Elegantly\CookiesConsent\CookieDefinition;
use Elegantly\CookiesConsent\CookieGroupDefinition;
use Elegantly\CookiesConsent\Facades\CookiesConsent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    CookiesConsent::registerEssentials()
        ->register(new CookieGroupDefinition(
            key: 'helpdesk',
            required: true,
            name: __('cookies-consent::cookies.helpdesk.name'),
            description: __('cookies-consent::cookies.helpdesk.description'),
            items: [
                new CookieDefinition(
                    name: 'crisp-client/*',
                    lifetime: CarbonInterval::months(6),
                    description: __('cookies.crisp-client/*.description')
                ),
            ]
        ))
        ->register(new CookieGroupDefinition(
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
                new CookieDefinition(
                    name: '_gid',
                    lifetime: CarbonInterval::days(1),
                    description: __('cookies-consent::cookies._gid.description')
                ),
                new CookieDefinition(
                    name: '_gat',
                    lifetime: CarbonInterval::minutes(1),
                    description: __('cookies-consent::cookies._gat.description')
                ),
            ],
            onAccepted: function () {
                return <<<'JS'
                            if(typeof gtag === 'function'){
                                gtag('consent', 'update', {
                                    'analytics_storage': 'granted'
                                });
                            }
                        JS;
            },
        ))
        ->register(new CookieGroupDefinition(
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
                    new CookieDefinition(
                        name: '_ttp',
                        lifetime: CarbonInterval::months(13),
                        description: __('cookies._ttp.description')
                    ),
                ],
                onAccepted: function () {
                    return <<<'JS'
                            if(typeof ttq === 'object'){
                                ttq.grantConsent();
                            }
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

    return view('demo');
});
