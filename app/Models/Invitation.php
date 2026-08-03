<?php

namespace App\Models;

use App\Enums\EventType;
use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'slug',
        'title',
        'event_type',
        'template_id',
        'template_version',
        'status',
        'settings_json',
        'opening_text',
        'closing_message',
        'music_path',
        'music_autoplay',
        'livestream_url',
        'livestream_label',
        'share_message',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'status' => InvitationStatus::class,
            'settings_json' => 'array',
            'music_autoplay' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class)->orderBy('position');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class)->orderBy('position');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('position');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class)->orderBy('position');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function guestbookEntries(): HasMany
    {
        return $this->hasMany(GuestbookEntry::class);
    }

    public function giftMethods(): HasMany
    {
        return $this->hasMany(GiftMethod::class)->orderBy('position');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class)->orderBy('position');
    }

    public function missingPreviewRequirements(): array
    {
        return array_values(array_filter([
            $this->hosts()->exists() ? null : 'minimal satu mempelai atau tuan rumah',
            $this->events()->where('is_primary', true)->exists() ? null : 'minimal satu acara utama',
        ]));
    }
}
