@php
    $current = app()->getLocale();
    $target = $current === 'ar' ? 'en' : 'ar';
@endphp

<a href="{{ request()->fullUrlWithQuery(['lang' => $target]) }}"
   class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-semibold text-muted-foreground transition hover:border-primary/40 hover:text-foreground"
   hreflang="{{ $target }}"
   aria-label="{{ $target === 'ar' ? 'تغيير اللغة إلى العربية' : 'Switch language to English' }}">
    <span class="text-base leading-none">{{ $target === 'ar' ? 'ع' : 'EN' }}</span>
    <span>{{ $target === 'ar' ? 'العربية' : 'English' }}</span>
</a>