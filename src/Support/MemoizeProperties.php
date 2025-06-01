<?php

declare(strict_types=1);

namespace Elegantly\CookiesConsent\Support;

use Closure;

trait MemoizeProperties
{
    /**
     * @var array<string, mixed>
     */
    protected array $memoized = [];

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function memoize(string $property, Closure $callback)
    {

        if ($this->isMemoized($property)) {
            return $this->memoized[$property];
        }

        return $this->memoized[$property] = $callback();
    }

    public function isMemoized(string $property): bool
    {
        return array_key_exists($property, $this->memoized);
    }

    public function forgetMemoized(string $property): void
    {
        if ($this->isMemoized($property)) {
            unset($this->memoized[$property]);
        }
    }
}
