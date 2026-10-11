<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Records one row per public invitation open.
 *
 * Tracking must never be the reason a guest sees an error, so every failure is
 * swallowed with a log entry: a failed insert leaves the invitation rendering
 * exactly as before.
 */
class VisitTracker
{
    public function track(Request $request, Invitation $invitation, ?Guest $guest = null): void
    {
        // The catalogue sample is not a real customer invitation: the landing
        // page opens it under every design, so counting it would only inflate
        // one row and mix the designs together.
        if ($invitation->is_catalog_demo) {
            return;
        }

        try {
            InvitationVisit::create([
                'invitation_id' => $invitation->id,
                'guest_id' => $guest?->id,
                'ip_hash' => $this->ipHash($request->ip()),
                'user_agent' => $this->trim($request->userAgent()),
                'referrer' => $this->trim($this->referrer($request)),
            ]);
        } catch (\Throwable $e) {
            // Losing a statistic is fine; losing the invitation page is not.
            Log::warning('Invitation visit could not be recorded', [
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * A salted hash, never the raw address. The app key is the salt, so the same
     * visitor hashes the same way on this install but not on another.
     */
    private function ipHash(?string $ip): ?string
    {
        return $ip ? hash('sha256', $ip.'|'.(string) config('app.key')) : null;
    }

    private function referrer(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');

        return is_string($referrer) && $referrer !== '' ? $referrer : null;
    }

    private function trim(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value !== null && $value !== '' ? mb_substr($value, 0, 255) : null;
    }
}
