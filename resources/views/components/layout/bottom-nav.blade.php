@php
    use App\Support\Nav;

    $pathname = '/' . request()->path();
    $pathname = $pathname === '/' ? '/' : $pathname;
@endphp

<nav aria-label="{{ __('brand.name') }}"
     class="fixed inset-x-0 bottom-0 z-40 border-t border-sidebar-border bg-sidebar/95 backdrop-blur-xl lg:hidden">
    <ul class="grid grid-flow-col auto-cols-fr">
        @foreach ($nav as $item)
            @php $active = Nav::isActive($pathname, $item); @endphp

            <li>
                <a href="{{ $item['href'] }}"
                   @if ($active) aria-current="page" @endif
                   class="relative flex flex-col items-center gap-1 px-1 pt-2.5 pb-3">
                    <span class="absolute top-0 h-0.5 w-10 rounded-full bg-gold shadow-[0_0_8px_1px_rgba(212,175,55,0.7)]"
                          @if (! $active) hidden @endif></span>

                    <span @class([
                        'flex size-9 items-center justify-center rounded-xl transition-colors duration-200',
                        'bg-primary/10 text-primary' => $active,
                        'text-muted-foreground' => ! $active,
                    ])>
                        <x-icon :name="$item['icon']" class="size-5" />
                    </span>

                    <span @class([
                        'text-[10px] leading-none font-semibold',
                        'text-primary' => $active,
                        'text-muted-foreground' => ! $active,
                    ])>
                        {{ __($item['label']) }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>