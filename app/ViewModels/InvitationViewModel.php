<?php

namespace App\ViewModels;

use App\Enums\BlockType;
use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final readonly class InvitationViewModel
{
    public function __construct(public array $data) {}

    public static function from(Invitation $invitation, string $recipient, array $manifest, ?Guest $guest = null): self
    {
        $safeUrl = fn (?string $url): ?string => $url && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
        $asset = fn (?string $path): ?string => $path ? Storage::disk('public')->url($path) : null;
        $primaryEvent = $invitation->events->firstWhere('is_primary', true) ?? $invitation->events->first();
        $configuredSections = $invitation->sections->where('enabled', true)->pluck('key')->all();
        $sections = array_values(array_intersect($configuredSections ?: $manifest['sections'], $manifest['sections']));
        // Per section height, chosen in the section editor. Values are relative to
        // the guest's viewport, never pixels. Kept separate from $sections so
        // templates that do not read it keep working unchanged.
        $sectionHeights = [];

        foreach ($invitation->sections->where('enabled', true) as $section) {
            $height = $section->content_json['height'] ?? null;

            if (in_array($height, ['auto', 'half', 'tall'], true)) {
                $sectionHeights[$section->key] = $height;
            }
        }
        $settings = $invitation->settings_json ?? [];
        $accentDefault = $manifest['settings_schema']['accent_color']['default'] ?? '#7b2639';
        $motionDefault = $manifest['settings_schema']['motion']['default'] ?? 'calm';
        $motionOptions = $manifest['settings_schema']['motion']['options'] ?? ['calm', 'expressive', 'off'];
        $settingDefault = fn (string $key, mixed $fallback = null): mixed => $manifest['settings_schema'][$key]['default'] ?? $fallback;
        $settingOptions = fn (string $key): array => $manifest['settings_schema'][$key]['options'] ?? [];
        $safeSettingUrl = fn (string $key): ?string => $safeUrl($settings[$key] ?? null) ?: $safeUrl($settingDefault($key));
        $safeSettingMedia = fn (string $key): ?string => $safeSettingUrl($key) ?: $asset($settings[$key] ?? null);
        $rangeSetting = function (string $key, int|float $fallback, int|float $min, int|float $max) use ($settings, $settingDefault): int|float {
            $value = is_numeric($settings[$key] ?? null) ? $settings[$key] : $settingDefault($key, $fallback);

            return max($min, min($max, (float) $value));
        };
        $shareUrl = $guest
            ? route('invitations.guest', [$invitation->slug, $guest->token])
            : route('invitations.show', $invitation->slug);
        $shareMessage = str_replace('[nama]', $recipient, $invitation->share_message ?: 'Kepada Yth. [nama], kami mengundang Anda ke acara kami.');

        $events = $invitation->events->map(function ($event) use ($safeUrl, $invitation) {
            $startsAt = Carbon::parse(
                $event->date->format('Y-m-d').' '.($event->start_time ?: '00:00'),
                $event->timezone,
            );
            $endsAt = $startsAt && $event->end_time
                ? Carbon::parse($event->date->format('Y-m-d').' '.$event->end_time, $event->timezone)
                : $startsAt?->copy()->addHours(2);
            $location = implode(', ', array_filter([$event->venue_name, $event->address]));
            $destination = $event->latitude !== null && $event->longitude !== null
                ? $event->latitude.','.$event->longitude
                : $location;

            return [
                'label' => $event->label,
                'date' => $event->date->locale((string) config('invitation.locale', 'id'))->translatedFormat('l, j F Y'),
                'start_time' => $event->start_time ? substr($event->start_time, 0, 5) : null,
                'end_time' => $event->end_time ? substr($event->end_time, 0, 5) : null,
                'timezone' => $event->timezone,
                'venue' => $event->venue_name,
                'address' => $event->address,
                'notes' => array_values(array_filter([$event->parking_notes, $event->entrance_notes, $event->landmark_notes])),
                'directions_url' => $destination ? 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($destination) : $safeUrl($event->map_url),
                'map_embed_url' => $destination ? 'https://maps.google.com/maps?q='.rawurlencode($destination).'&output=embed' : null,
                'calendar_url' => $startsAt ? 'https://calendar.google.com/calendar/render?'.http_build_query([
                    'action' => 'TEMPLATE',
                    'text' => $event->label.' — '.$invitation->title,
                    'dates' => $startsAt->copy()->utc()->format('Ymd\THis\Z').'/'.$endsAt->copy()->utc()->format('Ymd\THis\Z'),
                    'location' => $location,
                ], '', '&', PHP_QUERY_RFC3986) : null,
                'ics_url' => route('invitations.calendar', [$invitation->slug, $event->id]),
                'timestamp' => $startsAt?->toIso8601String(),
                'is_primary' => $event->is_primary,
            ];
        })->all();

        $theme = [
            'accent_color' => preg_match('/^#[0-9a-f]{6}$/i', $settings['accent_color'] ?? '') ? $settings['accent_color'] : $accentDefault,
            'motion' => in_array($settings['motion'] ?? null, $motionOptions, true) ? $settings['motion'] : $motionDefault,
        ];

        foreach ([
            'font_pairing' => in_array($settings['font_pairing'] ?? null, $settingOptions('font_pairing'), true) ? $settings['font_pairing'] : $settingDefault('font_pairing', 'editorial-serif'),
            'bg_color' => preg_match('/^#[0-9a-f]{6}$/i', $settings['bg_color'] ?? '') ? $settings['bg_color'] : $settingDefault('bg_color', null),
            'cover_video_enabled' => filter_var(
                $settings['cover_video_enabled'] ?? $settingDefault('cover_video_enabled', false),
                FILTER_VALIDATE_BOOLEAN,
            ),
            'opening_video_enabled' => filter_var(
                $settings['opening_video_enabled'] ?? $settingDefault('opening_video_enabled', false),
                FILTER_VALIDATE_BOOLEAN,
            ),
            'cover_video_desktop' => $safeSettingMedia('cover_video_desktop'),
            'cover_video_mobile' => $safeSettingMedia('cover_video_mobile'),
            'cover_poster_image' => $safeSettingMedia('cover_poster_image'),
            'cover_focal_x' => $rangeSetting('cover_focal_x', 50, 0, 100),
            'cover_focal_y' => $rangeSetting('cover_focal_y', 50, 0, 100),
            'cover_overlay_opacity' => $rangeSetting('cover_overlay_opacity', 56, 30, 78),
            'cover_text_position' => in_array($settings['cover_text_position'] ?? null, $settingOptions('cover_text_position'), true) ? $settings['cover_text_position'] : $settingDefault('cover_text_position', 'left'),
            'ornament_style' => in_array($settings['ornament_style'] ?? null, $settingOptions('ornament_style'), true) ? $settings['ornament_style'] : $settingDefault('ornament_style', 'olive-line'),
            'music_enabled' => (bool) ($settings['music_enabled'] ?? $settingDefault('music_enabled', true)),
        ] as $key => $value) {
            if (array_key_exists($key, $manifest['settings_schema'] ?? [])) {
                $theme[$key] = $value;
            }
        }

        return new self([
            'title' => $invitation->title,
            'recipient' => $recipient,
            'guest_token' => $guest?->token,
            'invitation_limit' => $guest?->invitation_limit ?? 2,
            'rsvp_url' => route('invitations.rsvp', $invitation->slug),
            'guestbook_url' => route('invitations.guestbook', $invitation->slug),
            'share_url' => $shareUrl,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($shareMessage."\n".$shareUrl),
            'opening_text' => $invitation->opening_text,
            'closing_message' => $invitation->closing_message,
            'music_url' => $asset($invitation->music_path),
            'livestream_url' => $safeUrl($invitation->livestream_url),
            'livestream_label' => $invitation->livestream_label ?: 'Saksikan Live Streaming',
            'sections' => $sections,
            'hosts' => $invitation->hosts->map(fn ($host) => [
                'name' => $host->name,
                'nickname' => $host->nickname,
                'role' => $host->role,
                'photo_url' => $asset($host->photo_path),
                'bio' => $host->bio,
                'family' => implode(' & ', array_filter([$host->parent_father, $host->parent_mother])),
                'birth_order' => $host->birth_order,
                'instagram' => $host->social_instagram ? 'https://instagram.com/'.preg_replace('/[^a-z0-9._]/i', '', $host->social_instagram) : null,
            ])->all(),
            'events' => $events,
            'primary_event' => collect($events)->firstWhere('is_primary', true) ?? ($events[0] ?? null),
            'stories' => $invitation->stories->map(fn ($story) => [
                'date' => $story->date, 'title' => $story->title, 'body' => $story->body, 'image_url' => $asset($story->image_path),
            ])->all(),
            'gallery' => $invitation->media->where('type', 'image')->map(fn ($media) => [
                'url' => $asset($media->path), 'alt' => $media->alt_text ?: $media->caption ?: 'Foto acara', 'caption' => $media->caption,
            ])->all(),
            'gifts' => $invitation->giftMethods->map(fn ($gift) => [
                'type' => $gift->type->value,
                'type_label' => $gift->type->label(),
                'provider' => $gift->provider,
                'account_name' => $gift->account_name,
                'account_number' => $gift->account_number,
                'delivery_address' => $gift->delivery_address,
                'notes' => $gift->notes,
            ])->all(),
            'contacts' => $invitation->contacts->map(fn ($contact) => [
                'label' => $contact->label, 'name' => $contact->name, 'phone' => $contact->phone,
                'whatsapp_url' => 'https://wa.me/'.preg_replace('/\D/', '', preg_replace('/^0/', '62', $contact->phone)),
                'phone_url' => 'tel:+'.preg_replace('/\D/', '', preg_replace('/^0/', '62', $contact->phone)),
            ])->all(),
            'wishes' => $invitation->guestbookEntries->map(fn ($entry) => [
                'name' => $entry->name, 'message' => $entry->message,
            ])->all(),
            'blocks' => self::blocks($invitation, $asset),
            'block_sections' => self::blockSections($invitation, $asset),
            'section_heights' => $sectionHeights,
            'labels' => self::labels($invitation, $manifest),
            'theme' => $theme,
        ]);
    }

    /**
     * Section wording for a single invitation.
     *
     * The template declares its own defaults in the manifest and one invitation
     * may override any of them without touching the other invitations using the
     * same template. A blank or unknown override is ignored, so clearing a field
     * in the admin panel falls back to the template instead of leaving an empty
     * heading in the published invitation.
     *
     * @return array<string, string>
     */
    private static function labels(Invitation $invitation, array $manifest): array
    {
        $labels = is_array($manifest['labels'] ?? null) ? $manifest['labels'] : [];
        $overrides = $invitation->settings_json['labels'] ?? [];

        if (! is_array($overrides)) {
            return $labels;
        }

        foreach ($overrides as $key => $value) {
            if (! isset($labels[$key]) || ! is_string($value)) {
                continue;
            }

            // Long enough for an intro paragraph, not just a heading.
            $value = mb_substr(trim(strip_tags($value)), 0, 400);

            if ($value !== '') {
                $labels[$key] = $value;
            }
        }

        return $labels;
    }

    /**
     * Operator-built blocks, normalised for the template.
     *
     * A block with nothing to show is dropped here rather than published as an
     * empty band or a broken image, so a template can render whatever it is
     * handed without guarding every field itself.
     *
     * @param  callable(?string): ?string  $asset
     * @return array<int, array<string, ?string>>
     */
    private static function blocks(Invitation $invitation, callable $asset): array
    {
        return $invitation->blocks
            ->map(fn ($block): ?array => self::normaliseBlock($block, $asset))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The same blocks, grouped by the section that holds them.
     *
     * A section renders its own group, which is what lets an operator build several
     * extra sections and place each one separately instead of moving them together.
     *
     * @param  callable(?string): ?string  $asset
     * @return array<string, array<int, array<string, ?string>>>
     */
    private static function blockSections(Invitation $invitation, callable $asset): array
    {
        $keys = $invitation->sections->pluck('key', 'id');
        $grouped = [];

        foreach ($invitation->blocks as $block) {
            $normalised = self::normaliseBlock($block, $asset);

            if ($normalised !== null) {
                $grouped[$keys[$block->section_id] ?? 'blocks'][] = $normalised;
            }
        }

        return $grouped;
    }

    /**
     * One block, ready to render, or null when it has nothing to show.
     *
     * @param  callable(?string): ?string  $asset
     * @return array<string, ?string>|null
     */
    private static function normaliseBlock($block, callable $asset): ?array
    {
        $content = is_array($block->content_json) ? $block->content_json : [];
        $text = function (string $key) use ($content): ?string {
            $value = $content[$key] ?? null;

            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        };

        $normalised = [
            'type' => $block->type->value,
            'title' => $text('title'),
            'body' => $text('body'),
            'quote' => $text('quote'),
            'source' => $text('source'),
            'caption' => $text('caption'),
            'url' => $asset($text('path')),
        ];

        $hasContent = match ($block->type) {
            BlockType::SECTION => $normalised['title'] !== null,
            BlockType::TEXT, BlockType::NOTE => $normalised['body'] !== null,
            BlockType::QUOTE => $normalised['quote'] !== null,
            BlockType::IMAGE => $normalised['url'] !== null,
        };

        return $hasContent ? $normalised : null;
    }
}
