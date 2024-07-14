@props([
    'disabled' => false,
    'type' => 'button',
    'tag' => 'button',
    'color' => 'white',
])

<{!! $tag !!} {!! $attributes->class([
    'bg-white ring-1 ring-inset ring-black/10 text-black hover:text-black hover:bg-white/95 outline-white' =>
        $color === 'white',
    'bg-black text-white hover:text-white hover:bg-black outline-black ring-black/20' => $color === 'black',
    'text-sm',
    'px-3 py-2',
    'shrink-0 cursor-pointer relative inline-flex items-center',
    'outline-2 outline-offset-2',
    'focus-visible:outline active:outline active:ring',
]) !!} type="{{ $type }}" @disabled($disabled)>

    {{ $slot }}

    </{!! $tag !!}>
