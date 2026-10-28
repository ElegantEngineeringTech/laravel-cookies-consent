export type Consents = Record<string, boolean>;

/**
 * Shape of the consent cookie, shared with `Elegantly\CookiesConsent\CookiesConsent::getValue()`.
 */
export type CookiesConsentValue = {
    set_at: number;
    consents: Consents;
};

export type CookiesConsentCallback = () => void;

export type CookiesConsentOptions = {
    /**
     * See `cookies-consent.cookie.name`.
     */
    cookieName: string;
    /**
     * In minutes, see `cookies-consent.cookie.lifetime`.
     */
    lifetime?: number;
    /**
     * See `cookies-consent.cookie.domain`.
     */
    domain?: string | null;
    /**
     * Every registered group key, `true` when required. See `CookiesConsent::getDefaultConsents()`.
     */
    defaultConsents: Consents;
    /**
     * The `onAccepted` callback of each group, keyed by group key.
     */
    callbacks?: Record<string, CookiesConsentCallback>;
};

/**
 * Members are kept public or `protected` (not `#private`) so the class keeps
 * working when wrapped in a reactive Proxy, like Alpine's `x-data`.
 */
export class CookiesConsent {
    cookieName: string;
    lifetime: number;
    domain: string | null;
    defaultConsents: Consents;
    callbacks: Record<string, CookiesConsentCallback>;

    /**
     * The working state of the consents, persisted by `save()`.
     */
    consents: Consents;

    protected executed = new Set<string>();

    constructor(options: CookiesConsentOptions) {
        this.cookieName = options.cookieName;
        this.lifetime = options.lifetime ?? 60 * 24 * 365;
        this.domain = options.domain ?? null;
        this.defaultConsents = { ...options.defaultConsents };
        this.callbacks = { ...options.callbacks };
        this.consents = this.getConsents();
    }

    /**
     * The raw value of the consent cookie.
     */
    getCookie(): string | null {
        for (const cookie of document.cookie.split(";")) {
            const separator = cookie.indexOf("=");

            if (separator === -1) {
                continue;
            }

            try {
                const name = decodeURIComponent(cookie.slice(0, separator).trim());

                if (name === this.cookieName) {
                    return decodeURIComponent(cookie.slice(separator + 1));
                }
            } catch {
                // Skip cookies that are not URI encoded
            }
        }

        return null;
    }

    /**
     * The parsed consent cookie, or `null` when missing or malformed.
     */
    getValue(): CookiesConsentValue | null {
        const cookie = this.getCookie();

        if (!cookie) {
            return null;
        }

        try {
            const value = JSON.parse(cookie);

            if (isObject(value) && isObject(value.consents)) {
                return value as CookiesConsentValue;
            }
        } catch {
            // Treat invalid JSON as a missing cookie
        }

        return null;
    }

    getDefaultConsents(): Consents {
        return { ...this.defaultConsents };
    }

    /**
     * The consents stored in the cookie, completed with the defaults.
     */
    getConsents(): Consents {
        return this.normalize(this.getValue()?.consents ?? {});
    }

    hasConsent(key: string): boolean {
        return this.consents[key] === true;
    }

    isRequired(key: string): boolean {
        return this.defaultConsents[key] === true;
    }

    /**
     * Whether the user has yet to make a choice: nothing stored, or groups registered since the last choice.
     */
    isPending(): boolean {
        const consents = this.getValue()?.consents;

        if (!consents) {
            return true;
        }

        return Object.keys(this.defaultConsents).some((key) => !Object.hasOwn(consents, key));
    }

    /**
     * Unknown groups are ignored and required groups can't be refused.
     */
    set(key: string, value: boolean): this {
        if (Object.hasOwn(this.defaultConsents, key)) {
            this.consents[key] = this.isRequired(key) || value;
        }

        return this;
    }

    setConsents(consents: Consents): this {
        for (const [key, value] of Object.entries(consents)) {
            this.set(key, value);
        }

        return this;
    }

    acceptAll(): this {
        for (const key of Object.keys(this.defaultConsents)) {
            this.consents[key] = true;
        }

        return this.save();
    }

    acceptEssentials(): this {
        this.consents = this.getDefaultConsents();

        return this.save();
    }

    /**
     * Persists the working consents and runs the callbacks of the accepted groups.
     */
    save(): this {
        this.consents = this.normalize(this.consents);

        this.setCookie({
            set_at: Date.now(),
            consents: this.consents,
        });

        return this.runCallbacks();
    }

    setCookie(value: CookiesConsentValue): void {
        this.writeCookie(encodeURIComponent(JSON.stringify(value)), Math.round(this.lifetime * 60));
    }

    /**
     * Deletes the consent cookie and resets the consents. Callbacks already run can't be undone.
     */
    forget(): this {
        this.writeCookie("", 0);
        this.consents = this.getDefaultConsents();

        return this;
    }

    /**
     * Runs the callbacks of the accepted groups, each at most once per page load.
     */
    runCallbacks(): this {
        for (const [key, callback] of Object.entries(this.callbacks)) {
            if (!this.hasConsent(key) || this.executed.has(key)) {
                continue;
            }

            this.executed.add(key);

            try {
                callback();
            } catch (error) {
                console.error(`[cookies-consent] The "${key}" callback failed.`, error);
            }
        }

        return this;
    }

    protected normalize(consents: Consents): Consents {
        const normalized: Consents = {};

        for (const [key, required] of Object.entries(this.defaultConsents)) {
            normalized[key] = required || consents[key] === true;
        }

        return normalized;
    }

    protected writeCookie(value: string, maxAge: number): void {
        const attributes = [
            `${encodeURIComponent(this.cookieName)}=${value}`,
            "path=/",
            `max-age=${maxAge}`,
            "SameSite=Lax",
        ];

        if (this.domain) {
            attributes.push(`domain=${this.domain}`);
        }

        if (globalThis.location?.protocol === "https:") {
            attributes.push("Secure");
        }

        document.cookie = attributes.join("; ");
    }
}

function isObject(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null && !Array.isArray(value);
}
