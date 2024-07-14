@props([
    'disabled' => false,
    'type' => 'button',
    'tag' => 'button',
    'color' => 'white',
])

<{!! $tag !!} {!! $attributes->class([
    'tw-bg-white tw-ring-1 tw-ring-inset tw-ring-black/10 tw-text-black hover:tw-text-black hover:tw-bg-white/95 tw-outline-white' =>
        $color === 'white',
    'tw-bg-black tw-text-white hover:tw-text-white hover:tw-bg-black tw-outline-black tw-ring-black/20' =>
        $color === 'black',
    'tw-text-sm',
    'tw-px-3 tw-py-2',
    'tw-shrink-0 tw-cursor-pointer tw-relative tw-inline-flex tw-items-center',
    'tw-outline-2 tw-outline-offset-2',
    'focus-visible:tw-outline active:tw-outline active:tw-ring',
]) !!} type="{{ $type }}" @disabled($disabled)>

    {{ $slot }}

    </{!! $tag !!}>
