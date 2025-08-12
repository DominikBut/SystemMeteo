<button {{ $attributes->merge(['type' => 'submit', 'class' => 'whitespace-nowrap rounded-xl bg-blue-700 border border-blue-700 px-4 py-2 text-sm font-medium tracking-wide text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
