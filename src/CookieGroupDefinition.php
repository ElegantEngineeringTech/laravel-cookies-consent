<?php

namespace Elegantly\CookiesConsent;

use Closure;
use Illuminate\Support\Collection;

/**
 * @extends \Illuminate\Support\Collection<array-key, CookieDefinition>
 */
class CookieGroupDefinition extends Collection
{
    public function __construct(
        public string|int $key,
        public string $name,
        public ?string $description = null,
        public bool $required = false,
        public ?Closure $onAccepted = null,
        $items = null,
    ) {
        parent::__construct($items);
    }
}
