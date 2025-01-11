<?php

declare(strict_types=1);

namespace Elegantly\CookiesConsent;

use Carbon\CarbonInterval;

class CookieDefinition
{
    public function __construct(
        public string $name,
        public CarbonInterval $lifetime,
        public ?string $description = null,
    ) {}

    public function formattedLifetime(): string
    {
        return $this->lifetime->cascade()->forHumans();
    }
}
