<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-lime-300 border border-white rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-lime-300-50 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
