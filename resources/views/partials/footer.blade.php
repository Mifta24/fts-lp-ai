<footer class="bg-[#030712] py-12 text-slate-300">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 md:flex-row md:items-end md:justify-between lg:px-8">
        <div class="max-w-md">
            <a href="#home" class="flex items-center gap-2 text-white">
                <img src="{{ asset('images/logo.webp') }}" alt="" width="32" height="32" class="h-8 w-8 rounded-lg">
                <span class="text-lg font-bold">Fujiyama</span>
            </a>
            <p class="mt-4 text-sm leading-relaxed">{{ __('landing.footer.tagline') }}</p>
        </div>
        <p class="text-sm text-slate-400">{{ __('landing.footer.rights') }}</p>
    </div>
</footer>
