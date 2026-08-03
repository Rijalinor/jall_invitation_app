<?php

use App\Http\Controllers\InvitationCalendarController;
use App\Http\Controllers\InvitationInteractionController;
use App\Http\Controllers\InvitationPreviewController;
use App\Http\Controllers\PublicInvitationController;
use App\Services\TemplateRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/{invitation}', [InvitationPreviewController::class, 'show'])
    ->middleware(['auth', 'signed'])->name('invitations.preview');
Route::get('/template-previews/{template}', function (TemplateRegistry $templates, string $template) {
    abort_unless($path = $templates->previewPath($template), 404);

    return response()->file($path);
})->middleware('auth')->name('templates.preview');
Route::get('/{slug}/g/{token}', [PublicInvitationController::class, 'show'])->name('invitations.guest');
Route::get('/{slug}/calendar/{event}', [InvitationCalendarController::class, 'download'])->name('invitations.calendar');
Route::post('/{slug}/rsvp', [InvitationInteractionController::class, 'rsvp'])->middleware('throttle:5,1')->name('invitations.rsvp');
Route::post('/{slug}/guestbook', [InvitationInteractionController::class, 'guestbook'])->middleware('throttle:3,1')->name('invitations.guestbook');
Route::get('/{slug}', [PublicInvitationController::class, 'show'])->name('invitations.show');
