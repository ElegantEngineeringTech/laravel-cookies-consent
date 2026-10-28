<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;

it('inlines the compiled script', function () {
    $html = Blade::render('@cookiesConsentScripts');

    expect($html)
        ->toStartWith('<script>')
        ->toEndWith('</script>')
        ->toContain('window.CookiesConsent=');
});

it('adds the given nonce', function () {
    expect(Blade::render('@cookiesConsentScripts(nonce: \'a"b\')'))
        ->toStartWith('<script nonce="a&quot;b">');
});

it('uses the Vite CSP nonce by default', function () {
    Vite::useCspNonce('vite-nonce');

    expect(Blade::render('@cookiesConsentScripts'))
        ->toStartWith('<script nonce="vite-nonce">');
});
