<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Services\CatalogDemos;
use App\Services\TemplateRegistry;
use App\Services\WhatsAppLink;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Public catalogue of invitation templates.
 *
 * Every card on this page is derived from a template manifest, so adding a
 * template directory is enough for it to appear here. The page never queries
 * invitation content beyond the slug of the single sample: it reads manifests
 * and preview files, then points every design at that one sample.
 */
class LandingController extends Controller
{
    /**
     * Ink colour used when a manifest does not declare a usable accent.
     */
    private const FALLBACK_ACCENT = '#191713';

    private const GENERIC_MESSAGE = 'Halo, saya ingin membuat undangan digital. Boleh minta informasi paketnya?';

    public function __invoke(
        Request $request,
        TemplateRegistry $registry,
        WhatsAppLink $whatsapp,
        CatalogDemos $demos,
    ): View {
        $manifests = collect($registry->all());

        $eventTypes = collect(EventType::cases())
            ->mapWithKeys(fn (EventType $type): array => [$type->value => $type->label()]);

        $activeType = $this->activeType($request, $eventTypes);

        $demoSlug = $demos->slug();

        $presented = $manifests
            ->map(fn (array $manifest): array => $this->present($manifest, $registry, $whatsapp, $demoSlug))
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $catalogue = $activeType === null
            ? $presented
            : $presented->filter(fn (array $template): bool => $this->supportsType($template, $activeType))->values();

        return view('landing', [
            'brand' => (string) config('invitation.brand', 'JALL Invitation'),
            'catalogue' => $catalogue,
            'featured' => $presented->filter(fn (array $template): bool => $template['preview_url'] !== null)->take(3),
            'total' => $presented->count(),
            'eventTypes' => $eventTypes,
            'filters' => $this->filters($presented, $eventTypes),
            'activeType' => $activeType,
            'whatsappUrl' => $whatsapp->to(self::GENERIC_MESSAGE),
            'contactEmail' => $this->contactEmail(),
            'ogImageUrl' => $presented->firstWhere('preview_is_raster', true)['preview_url'] ?? null,
        ]);
    }

    /**
     * Reduce a manifest to the fields the catalogue renders.
     *
     * @param  array<string, mixed>  $manifest
     * @param  string|null  $demoSlug  Slug of the single sample invitation, if one is published.
     * @return array<string, mixed>
     */
    private function present(
        array $manifest,
        TemplateRegistry $registry,
        WhatsAppLink $whatsapp,
        ?string $demoSlug,
    ): array {
        $id = (string) $manifest['id'];
        $previewPath = $registry->previewPath($id);

        return [
            'id' => $id,
            'name' => (string) $manifest['name'],
            'accent' => $this->accent($manifest),
            'event_types' => array_values(array_filter(
                (array) ($manifest['event_types'] ?? []),
                'is_string',
            )),
            'preview_url' => $previewPath === null
                ? null
                : route('templates.preview', $id),
            'preview_is_raster' => $previewPath !== null
                && preg_match('/\.(png|jpe?g|webp)$/i', $previewPath) === 1,
            'demo_url' => is_string($demoSlug) && $demoSlug !== ''
                ? route('invitations.show', ['slug' => $demoSlug, 'template' => $id])
                : null,
            'contact_url' => $whatsapp->to(sprintf(
                'Halo, saya ingin membuat undangan digital. Desain pilihan saya: %s.',
                $manifest['name'],
            )),
        ];
    }

    /**
     * The accent colour is written straight into a CSS custom property, so it is
     * only accepted when it is a plain six digit hex value.
     *
     * @param  array<string, mixed>  $manifest
     */
    private function accent(array $manifest): string
    {
        $accent = $manifest['settings_schema']['accent_color']['default'] ?? null;

        return is_string($accent) && preg_match('/^#[0-9a-f]{6}$/i', $accent) === 1
            ? $accent
            : self::FALLBACK_ACCENT;
    }

    /**
     * @param  array<string, mixed>  $template
     */
    private function supportsType(array $template, string $type): bool
    {
        return $template['event_types'] === [] || in_array($type, $template['event_types'], true);
    }

    /**
     * @param  Collection<string, string>  $eventTypes
     */
    private function activeType(Request $request, Collection $eventTypes): ?string
    {
        $requested = $request->query('acara');

        return is_string($requested) && $eventTypes->has($requested) ? $requested : null;
    }

    /**
     * Only offer a filter when at least one template supports that event type.
     *
     * @param  Collection<int, array<string, mixed>>  $presented
     * @param  Collection<string, string>  $eventTypes
     * @return array<int, array<string, mixed>>
     */
    private function filters(Collection $presented, Collection $eventTypes): array
    {
        return $eventTypes
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
                'count' => $presented->filter(fn (array $t): bool => $this->supportsType($t, $value))->count(),
            ])
            ->filter(fn (array $filter): bool => $filter['count'] > 0)
            ->values()
            ->all();
    }

    private function contactEmail(): ?string
    {
        $email = config('invitation.email');

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
