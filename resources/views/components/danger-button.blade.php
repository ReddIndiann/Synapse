<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-red-600 border border-red-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 hover:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150']) }}>
    {{ $slot }}
</button>
