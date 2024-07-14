@props([
    'disabled' => false,
    'checked' => false,
    'required' => false,
])

<input {!! $attributes->class([
    'tw-appearance-none',
    'tw-w-10 tw-h-0 tw-cursor-pointer tw-inline-block',
    'tw-border-0 dark:tw-border-0',
    'focus:tw-ring-offset-transparent dark:focus:tw-ring-offset-transparent',
    'focus:tw-ring-transparent dark:focus:tw-ring-transparent',
    'focus-within:tw-ring-0 dark:focus-within:tw-ring-0',
    'focus:tw-shadow-none dark:focus:tw-shadow-none',
    'focus:before:tw-ring',
    'after:tw-absolute before:tw-absolute',
    'after:tw-top-0 before:tw-top-0',
    'after:tw-block before:tw-inline-block',
    'before:tw-rounded-full after:tw-rounded-full',
    "after:tw-content-[''] after:tw-w-5 after:tw-h-5 after:tw-mt-0.5 after:tw-ml-0.5",
    'after:tw-shadow-md after:tw-duration-100',
    "before:tw-content-[''] before:tw-w-10 before:tw-h-full",
    'before:tw-shadow-[inset_0_0_#000]',
    'after:tw-bg-white dark:after:tw-bg-gray-50',
    'before:tw-bg-gray-300 dark:before:tw-bg-gray-600',
    'checked:after:tw-duration-300 checked:after:tw-translate-x-4',
    'disabled:after:tw-bg-opacity-75 disabled:tw-cursor-not-allowed',
    'disabled:checked:before:tw-bg-opacity-40',
    'before:checked:tw-bg-emerald-500 dark:before:checked:tw-bg-emerald-500',
]) !!} type="checkbox" @disabled($disabled) @checked($checked)
    @required($required)>
