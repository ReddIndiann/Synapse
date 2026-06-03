<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-indigo-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 hover:border-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-[0.99] transition duration-150 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
