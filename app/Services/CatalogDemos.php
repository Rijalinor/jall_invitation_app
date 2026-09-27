<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use Illuminate\Database\QueryException;

/**
 * Resolves the public sample invitation for each template.
 *
 * A sample is an ordinary invitation flagged with `is_catalog_demo` and
 * published, so the catalogue can link visitors to the real rendered
 * invitation instead of a static preview image.
 */
class CatalogDemos
{
    /**
     * Map of template id to the slug of its public sample invitation.
     *
     * When several samples exist for one template the newest wins, so
     * publishing a fresh sample takes over without unpublishing the old one.
     *
     * @return array<string, string>
     */
    public function slugsByTemplate(): array
    {
        try {
            return Invitation::query()
                ->where('is_catalog_demo', true)
                ->where('status', InvitationStatus::PUBLISHED)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderBy('id')
                ->pluck('slug', 'template_id')
                ->all();
        } catch (QueryException $exception) {
            // The catalogue is the public storefront, so a database problem must
            // degrade to "no samples yet" rather than take the page down. This
            // also covers the window during a deploy before migrations run.
            report($exception);

            return [];
        }
    }
}
