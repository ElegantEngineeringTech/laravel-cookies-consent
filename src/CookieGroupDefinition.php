<?php

declare(strict_types=1);

namespace Elegantly\CookiesConsent;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class CookieGroupDefinition
{
    /**
     * @var Collection<array-key, CookieDefinition>
     */
    public Collection $items;

    /**
     * @param  Arrayable<array-key, CookieDefinition>|iterable<array-key, CookieDefinition>|null  $items
     */
    public function __construct(
        public string $key,
        public string $name,
        public ?string $description = null,
        public bool $required = false,
        public ?Closure $onAccepted = null,
        Arrayable|iterable|null $items = null,
    ) {
        $this->key = str_replace(['- .'], '_', $key);
        $this->items = new Collection($items);
    }
}
