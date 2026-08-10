<?php

namespace App\ViewModels;

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
            $startsAt = $event->start_time
                ? Carbon::parse($event->date->format('Y-m-d').' '.$event->start_time, $event->timezone)
                : null;
            $endsAt = $startsAt && $event->end_time
                ? Carbon::parse($event->date->format('Y-m-d').' '.$event->end_time, $event->timezone)
                : $startsAt?->copy()->addHours(2);
            $location = implode(', ', array_filter([$event->venue_name, $event->address]));
            $destination = $event->latitude !== null && $event->longitude !== null
                ? $event->latitude.','.$event->longitude
                : $location;

            return [
                'label' => $event->label,
                'date' => $event->date->translatedFormat('l, j F Y'),
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
            'cover_video_enabled' => (bool) ($settings['cover_video_enabled'] ?? $settingDefault('cover_video_enabled', false)),
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
            'theme' => $theme,
        ]);
    }
}
