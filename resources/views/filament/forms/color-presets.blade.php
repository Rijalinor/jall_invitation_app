{{--
    Recommended colour swatches for a colour setting.

    Rendered under the matching colour picker in the invitation form. A click
    writes straight into the same Livewire form state the picker uses, so the
    value is saved exactly as if it had been typed, and the picker reflects it.
--}}
@php
    $presets = is_array($presets ?? null) ? $presets : [];
    $statePath = $statePath ?? null;
@endphp

@if ($statePath && count($presets))
    <div
        class="fi-color-presets"
        x-data="{ chosen: $wire.$entangle(@js($statePath)) }"
    >
        <span class="fi-color-presets__title">Rekomendasi warna</span>
        <div class="fi-color-presets__row">
            @foreach ($presets as $preset)
                <button
                    type="button"
                    class="fi-color-presets__swatch"
                    x-on:click="chosen = @js($preset['value'])"
                    x-bind:class="{ 'is-active': chosen === @js($preset['value']) }"
                    style="--preset: {{ $preset['value'] }}"
                    title="{{ $preset['label'] }}"
                >
                    <span class="fi-color-presets__dot" aria-hidden="true"></span>
                    <span>{{ $preset['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <style>
        .fi-color-presets { display: grid; gap: .45rem; }
        .fi-color-presets__title { font-size: .72rem; font-weight: 600; color: var(--gray-500, #6b7280); }
        .fi-color-presets__row { display: flex; flex-wrap: wrap; gap: .5rem; }
        .fi-color-presets__swatch {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .32rem .7rem .32rem .45rem;
            border: 1px solid var(--gray-300, #d1d5db);
            border-radius: 999px;
            background: var(--white, #fff);
            color: var(--gray-700, #374151);
            font-size: .75rem;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        .fi-color-presets__swatch:hover { background: var(--gray-50, #f9fafb); border-color: var(--gray-400, #9ca3af); }
        .fi-color-presets__swatch.is-active {
            border-color: var(--gray-900, #111827);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--gray-900, #111827) 18%, transparent);
        }
        .fi-color-presets__dot {
            width: 1.05rem;
            height: 1.05rem;
            border-radius: 999px;
            background: var(--preset);
            box-shadow: inset 0 0 0 1px rgb(0 0 0 / .14);
        }
        .dark .fi-color-presets__swatch { background: var(--gray-900, #111827); color: var(--gray-100, #f3f4f6); border-color: var(--gray-700, #374151); }
        .dark .fi-color-presets__swatch:hover { background: var(--gray-800, #1f2937); }
        .dark .fi-color-presets__swatch.is-active { border-color: var(--gray-100, #f3f4f6); box-shadow: 0 0 0 2px color-mix(in srgb, var(--gray-100, #f3f4f6) 30%, transparent); }
    </style>
@endif
