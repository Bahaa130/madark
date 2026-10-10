<button type="button"
        @click="$store.theme.toggle()"
        class="inline-flex size-10 items-center justify-center rounded-xl border border-border bg-card text-muted-foreground transition hover:border-primary/40 hover:text-foreground"
        :aria-label="$store.theme.isDark() ? 'الوضع الفاتح' : 'الوضع الداكن'"
        :title="$store.theme.isDark() ? 'الوضع الفاتح' : 'الوضع الداكن'">
    {{-- Sun: shown while dark, moon: shown while light --}}
    <svg x-show="$store.theme.isDark()" x-cloak class="size-5" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
    </svg>
    <svg x-show="!$store.theme.isDark()" class="size-5" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
</button>