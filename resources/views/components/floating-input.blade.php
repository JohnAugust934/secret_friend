@props(['disabled' => false, 'label' => 'Label', 'name', 'type' => 'text'])

<div class="relative group">
    <input
        {{ $disabled ? 'disabled' : '' }}
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder=" "
        {!! $attributes->merge(['class' => 'block px-4 pb-2.5 pt-5 w-full text-sm text-gray-900 bg-white/50 dark:bg-gray-900/50 dark:text-white rounded-xl border border-gray-300 dark:border-gray-600 appearance-none focus:outline-none focus:ring-0 focus:border-indigo-600 dark:focus:border-indigo-500 peer backdrop-blur-sm transition-all shadow-sm']) !!}
    />
    <label for="{{ $name }}"
        class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 peer-focus:text-indigo-600 dark:peer-focus:text-indigo-500 cursor-text bg-transparent">
        {{ $label }}
    </label>
</div>