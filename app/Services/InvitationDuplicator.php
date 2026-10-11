<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use Illuminate\Support\Str;

/**
 * Deep-copies an invitation's content into a fresh draft.
 *
 * The copy exists so an operator can start the next order from a similar one
 * instead of re-entering everything. Content is copied; audience data is not —
 * guests, RSVP, the guestbook, and visit stats belong to the original and carry
 * personal tokens or private responses.
 */
class InvitationDuplicator
{
    /**
     * Relations that hold displayable content. Blocks are handled on their own
     * because each block's section_id has to point at the copied section.
     *
     * @var list<string>
     */
    private const CONTENT_RELATIONS = ['hosts', 'events', 'stories', 'media', 'giftMethods', 'contacts'];

    public function duplicate(Invitation $source): Invitation
    {
        $copy = $source->replicate();

        // Assigned directly rather than through fill(), because the form-token
        // columns are guarded: a copy must not inherit the source's credential.
        $copy->title = Str::limit($source->title, 240, '').' (Salinan)';
        $copy->slug = $this->uniqueSlug($source->slug);
        $copy->status = InvitationStatus::DRAFT;
        $copy->is_catalog_demo = false;
        $copy->published_at = null;
        $copy->expires_at = null;
        $copy->form_token_hash = null;
        $copy->form_token_expires_at = null;
        $copy->form_token_used_at = null;
        $copy->save();

        foreach (self::CONTENT_RELATIONS as $relation) {
            foreach ($source->{$relation} as $item) {
                $copy->{$relation}()->create($item->replicate(['invitation_id'])->getAttributes());
            }
        }

        // Sections first, so the blocks can be pointed at their copies. The id
        // map is why blocks are not copied through the generic loop above.
        $sectionMap = [];

        foreach ($source->sections as $section) {
            $sectionMap[$section->id] = $copy->sections()
                ->create($section->replicate(['invitation_id'])->getAttributes())
                ->id;
        }

        foreach ($source->blocks as $block) {
            $attributes = $block->replicate(['invitation_id'])->getAttributes();
            $attributes['section_id'] = $sectionMap[$block->section_id] ?? null;

            $copy->blocks()->create($attributes);
        }

        return $copy;
    }

    /**
     * A slug nothing else uses, soft-deleted rows included, because the unique
     * index spans them too.
     */
    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base).'-salinan';

        if (! Invitation::withTrashed()->where('slug', $slug)->exists()) {
            return $slug;
        }

        $number = 2;

        while (Invitation::withTrashed()->where('slug', $slug.'-'.$number)->exists()) {
            $number++;
        }

        return $slug.'-'.$number;
    }
}
