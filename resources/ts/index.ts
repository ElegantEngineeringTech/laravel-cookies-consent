import { CookiesConsent } from "./CookiesConsent";

declare global {
    interface Window {
        /**
         * Defined by `@cookiesConsentScripts`.
         */
        CookiesConsent: typeof CookiesConsent;
        /**
         * Instance configured with the registered groups, created by `@cookiesConsentScripts`
         * and used by the `<x-cookies-consent::banner />` component.
         */
        cookiesConsent: CookiesConsent;
    }
}

export { CookiesConsent };

export type {
    Consents,
    CookiesConsentCallback,
    CookiesConsentOptions,
    CookiesConsentValue,
} from "./CookiesConsent";
