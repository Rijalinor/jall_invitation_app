<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Str;

/**
 * Manages the customer self-service link for an invitation.
 *
 * The plaintext token is never stored: only its SHA-256 hash is, mirroring how
 * password reset tokens work. The token is shown once when it is issued, and
 * can be revoked or replaced at any time from the admin panel.
 *
 * Note: this is deliberately stronger than the guest link token, which is
 * stored in plaintext. A guest token only personalises a read-only page, while
 * this token grants write access to the whole invitation.
 */
class FormLinks
{
    public const DEFAULT_DAYS = 30;

    private const MIN_TOKEN_LENGTH = 32;

    /**
     * Issue a token for the invitation, replacing any previous one.
     *
     * @return string The plaintext token, shown to the operator exactly once.
     */
    public function issue(Invitation $invitation, int $days = self::DEFAULT_DAYS): string
    {
        $token = Str::random(48);

        $invitation->forceFill([
            'form_token_hash' => $this->hash($token),
            'form_token_expires_at' => now()->addDays(max(1, $days)),
            'form_token_used_at' => null,
        ])->save();

        return $token;
    }

    public function revoke(Invitation $invitation): void
    {
        $invitation->forceFill([
            'form_token_hash' => null,
            'form_token_expires_at' => null,
            'form_token_used_at' => null,
        ])->save();
    }

    public function url(string $token): string
    {
        return route('invitation-form.show', ['token' => $token]);
    }

    /**
     * Resolve a plaintext token to its invitation, or null when it is unknown,
     * malformed, or expired.
     */
    public function resolve(?string $token): ?Invitation
    {
        if (! is_string($token) || strlen($token) < self::MIN_TOKEN_LENGTH) {
            return null;
        }

        return Invitation::query()
            ->where('form_token_hash', $this->hash($token))
            ->where(fn ($query) => $query->whereNull('form_token_expires_at')
                ->orWhere('form_token_expires_at', '>', now()))
            ->first();
    }

    public function isActive(Invitation $invitation): bool
    {
        if ($invitation->form_token_hash === null) {
            return false;
        }

        return $invitation->form_token_expires_at === null
            || $invitation->form_token_expires_at->isFuture();
    }

    /**
     * Record that the customer opened their link.
     */
    public function touch(Invitation $invitation): void
    {
        $invitation->forceFill(['form_token_used_at' => now()])->saveQuietly();
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
