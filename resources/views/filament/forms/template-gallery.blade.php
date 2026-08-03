@php($templates = app(\App\Services\TemplateRegistry::class)->all())

<div>
    <p class="mb-2 text-sm font-medium">Preview Template</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
        @foreach ($templates as $template)
            <figure style="margin:0;overflow:hidden;border:1px solid var(--gray-300);border-radius:.75rem">
                <img src="{{ route('templates.preview', $template['id']) }}" alt="Preview {{ $template['name'] }}" loading="lazy" style="display:block;width:100%;aspect-ratio:3/2;object-fit:cover">
                <figcaption style="padding:.75rem;font-size:.875rem;font-weight:600">{{ $template['name'] }}</figcaption>
            </figure>
        @endforeach
    </div>
</div>
