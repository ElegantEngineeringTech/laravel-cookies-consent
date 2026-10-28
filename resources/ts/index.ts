import { CookiesConsent } from "./CookiesConsent";

declare global {
    interface Window {
        /**
         * Expected by the `<x-cookies-consent::banner />` component.
         */
        CookiesConsent: typeof CookiesConsent;
    }
}

export { CookiesConsent };

export type {
    Consents,
    CookiesConsentCallback,
    CookiesConsentOptions,
    CookiesConsentValue,
} from "./CookiesConsent";
