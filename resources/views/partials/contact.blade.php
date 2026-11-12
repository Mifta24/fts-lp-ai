@php
    $field = 'w-full rounded-xl border border-line bg-white px-4 py-3 text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 aria-invalid:border-red-500';
    $phone = config('fts.phone');
    $phoneHref = 'tel:+'.preg_replace('/\D+/', '', $phone);
@endphp

<section id="contact" class="bg-tint py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading
            :eyebrow="__('landing.contact.eyebrow')"
            :title="__('landing.contact.title')"
            :sub="__('landing.contact.sub')"
        />

        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="card flex flex-col p-6 sm:p-8">
                <dl class="space-y-5">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('landing.contact.address_label') }}</dt>
                        <dd class="mt-1">
                            Fujiyama Technology Solutions<br>
                            Neo Soho Mall, Jalan Let. Jend. S. Parman Kav. 28 Unit 2011, Tanjung Duren Selatan, Grogol Petamburan, West Jakarta, DKI Jakarta 11470
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('landing.contact.hours_label') }}</dt>
                        <dd class="mt-1">{{ __('landing.contact.hours') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('landing.contact.phone_label') }}</dt>
                        <dd class="mt-1"><a class="hover:text-primary" href="{{ $phoneHref }}">{{ $phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('landing.contact.email_label') }}</dt>
                        <dd class="mt-1 break-all"><a class="hover:text-primary" href="mailto:{{ config('fts.email') }}">{{ config('fts.email') }}</a></dd>
                    </div>
                </dl>
                <a href="https://wa.me/{{ config('fts.whatsapp') }}" target="_blank" rel="noopener" class="mt-8 inline-flex w-fit items-center rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">{{ __('landing.contact.wa') }}</a>
            </div>

            <form id="contact-form" class="card grid gap-4 p-6 sm:p-8" novalidate data-mail-to="{{ config('fts.email') }}">
                <label class="grid gap-1.5 text-sm font-medium">
                    <span>{{ __('landing.contact.name') }}</span>
                    <input type="text" name="name" autocomplete="name" required class="{{ $field }}">
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    <span>{{ __('landing.contact.email') }}</span>
                    <input type="email" name="email" autocomplete="email" required class="{{ $field }}">
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    <span>{{ __('landing.contact.company') }}</span>
                    <input type="text" name="company" autocomplete="organization" class="{{ $field }}">
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    <span>{{ __('landing.contact.subject') }}</span>
                    <input type="text" name="subject" class="{{ $field }}">
                </label>
                <label class="grid gap-1.5 text-sm font-medium">
                    <span>{{ __('landing.contact.message') }}</span>
                    <textarea name="message" rows="5" required class="{{ $field }}"></textarea>
                </label>
                <p id="form-error" role="alert" hidden class="text-sm font-medium text-red-600">{{ __('landing.contact.error') }}</p>
                <button type="submit" class="inline-flex w-fit items-center rounded-full bg-primary px-7 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">{{ __('landing.contact.send') }}</button>
            </form>
        </div>
    </div>
</section>
