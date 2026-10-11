<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'guest_id',
        'ip_hash',
        'user_agent',
        'referrer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * A coarse device bucket so the admin list reads "HP" or "Desktop" instead
     * of a full user-agent string. Bots are named so they can be recognised,
     * not hidden.
     */
    public function device(): string
    {
        $agent = strtolower((string) $this->user_agent);

        return match (true) {
            $agent === '' => 'Tidak diketahui',
            str_contains($agent, 'bot') || str_contains($agent, 'crawl') || str_contains($agent, 'spider') => 'Bot',
            str_contains($agent, 'ipad') || str_contains($agent, 'tablet') => 'Tablet',
            str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone') => 'HP',
            default => 'Desktop',
        };
    }

    /**
     * Whether the open came from a personal guest link or the general link.
     */
    public function source(): string
    {
        return $this->guest_id ? 'Link pribadi' : 'Link umum';
    }
}
