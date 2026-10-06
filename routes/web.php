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
// A distinct third argument gives each endpoint its own throttle bucket. Without
// it every throttled route shares one counter per IP, so the customer form and
// the public forms would eat into each other's budget.
Route::get('/isi/{token}/{step?}', [InvitationFormController::class, 'show'])
    ->middleware('throttle:60,1,invitation-form-show')->name('invitation-form.show');
Route::post('/isi/{token}/{step}', [InvitationFormController::class, 'update'])
    ->middleware('throttle:20,1,invitation-form-update')->name('invitation-form.update');

// Audience management from the same private link: import guests, export or
// delete an RSVP, and moderate a guestbook entry. Every action resolves the
// invitation from the token and scopes the record to it.
Route::post('/isi/{token}/tamu/impor', [InvitationFormController::class, 'importGuests'])
    ->middleware('throttle:10,1,invitation-guests-import')->name('invitation-form.guests-import');
Route::get('/isi/{token}/tamu/template', [InvitationFormController::class, 'guestCsvTemplate'])
    ->middleware('throttle:20,1,invitation-guests-template')->name('invitation-form.guests-template');
Route::get('/isi/{token}/rsvp/unduh', [InvitationFormController::class, 'exportRsvps'])
    ->middleware('throttle:20,1,invitation-rsvp-export')->name('invitation-form.rsvp-export');
Route::post('/isi/{token}/rsvp/{rsvp}/hapus', [InvitationFormController::class, 'deleteRsvp'])
    ->middleware('throttle:20,1,invitation-rsvp-delete')->name('invitation-form.rsvp-delete');
Route::post('/isi/{token}/ucapan/{entry}', [InvitationFormController::class, 'moderateGuestbook'])
    ->middleware('throttle:30,1,invitation-guestbook-moderate')->name('invitation-form.guestbook-moderate');
Route::get('/{slug}/g/{token}', [PublicInvitationController::class, 'show'])->name('invitations.guest');
Route::get('/{slug}/calendar/{event}', [InvitationCalendarController::class, 'download'])->name('invitations.calendar');
Route::post('/{slug}/rsvp', [InvitationInteractionController::class, 'rsvp'])->middleware('throttle:5,1,invitation-rsvp')->name('invitations.rsvp');
Route::post('/{slug}/konfirmasi', [InvitationInteractionController::class, 'confirmation'])->middleware('throttle:5,1,invitation-confirmation')->name('invitations.confirmation');
Route::post('/{slug}/guestbook', [InvitationInteractionController::class, 'guestbook'])->middleware('throttle:3,1,invitation-guestbook')->name('invitations.guestbook');
Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('invitations.show');
