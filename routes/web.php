<?php

use App\Http\Controllers\InvitationCalendarController;
use App\Http\Controllers\InvitationFormController;
use App\Http\Controllers\InvitationInteractionController;
use App\Http\Controllers\InvitationPreviewController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicInvitationController;
use App\Services\TemplateRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::get('/preview/{invitation}', [InvitationPreviewController::class, 'show'])
    ->middleware(['auth', 'signed'])->name('invitations.preview');

// Public on purpose: the catalogue on the landing page shows these previews to
// prospective customers. previewPath() still rejects anything outside the
// registered template directories.
Route::get('/template-previews/{template}', function (TemplateRegistry $templates, string $template) {
    abort_unless($path = $templates->previewPath($template), 404);

    return response()->file($path);
})->name('templates.preview');

// Customer self-service content form, reached through a link issued in the
// admin panel. Throttled because the URL is the only credential.
Route::get('/isi/{token}/{step?}', [InvitationFormController::class, 'show'])
    ->middleware('throttle:60,1')->name('invitation-form.show');
Route::post('/isi/{token}/{step}', [InvitationFormController::class, 'update'])
    ->middleware('throttle:20,1')->name('invitation-form.update');
Route::get('/{slug}/g/{token}', [PublicInvitationController::class, 'show'])->name('invitations.guest');
Route::get('/{slug}/calendar/{event}', [InvitationCalendarController::class, 'download'])->name('invitations.calendar');
Route::post('/{slug}/rsvp', [InvitationInteractionController::class, 'rsvp'])->middleware('throttle:5,1')->name('invitations.rsvp');
Route::post('/{slug}/guestbook', [InvitationInteractionController::class, 'guestbook'])->middleware('throttle:3,1')->name('invitations.guestbook');
Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('invitations.show');
