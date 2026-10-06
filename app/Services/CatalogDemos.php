<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use Illuminate\Database\QueryException;

/**
 * Resolves the public sample invitation for the catalogue.
 *
 * One sample represents the whole catalogue: an operator fills in a single
 * invitation once and every template renders that same content in its own
 * design, so there is no per-template sample to keep in sync. A sample is an
 * ordinary invitation flagged with `is_catalog_demo` and published.
 */
class CatalogDemos
{
    /**
     * The slug of the newest published sample invitation, if any.
     *
     * When several samples are flagged, the newest wins, so publishing a fresh
     * sample takes over without unpublishing the old one.
     */
    public function slug(): ?string
    {
        try {
            return Invitation::query()
                ->where('is_catalog_demo', true)
                ->where('status', InvitationStatus::PUBLISHED)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderByDesc('id')
                ->value('slug');
        } catch (QueryException $exception) {
            // The catalogue is the public storefront, so a database problem must
            // degrade to "no sample yet" rather than take the page down. This
            // also covers the window during a deploy before migrations run.
            report($exception);

            return null;
        }
    }
}
