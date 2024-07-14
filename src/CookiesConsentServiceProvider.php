<?php

namespace Elegantly\CookiesConsent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CookiesConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cookies-consent')
            ->hasConfigFile()
            ->hasViews();
    }

    public function registeringPackage()
    {
        $this->app->scoped(CookiesConsent::class, function () {
            return new CookiesConsent(
                cookieName: config('cookiesconsent.cookie.name')
            );
        });
    }
}
