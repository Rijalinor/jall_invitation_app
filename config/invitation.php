<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | Shown as the wordmark and page title on the public catalogue. Declared
    | separately from APP_NAME because the application name is also used by
    | mail, sessions and the admin panel.
    |
    */

    'brand' => env('INVITATION_BRAND', 'JALL Invitation'),

    /*
    |--------------------------------------------------------------------------
    | Public Contact Channels
    |--------------------------------------------------------------------------
    |
    | The public catalogue is the only place a prospective customer can reach
    | the operator. Both values are optional; a channel that is not configured
    | is simply hidden on the page instead of rendering a broken action.
    |
    | `whatsapp` accepts a local (08...) or international (+62...) number and is
    | normalised to wa.me format by App\Services\WhatsAppLink.
    |
    */

    'whatsapp' => env('INVITATION_WHATSAPP'),

    'email' => env('INVITATION_CONTACT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Display Locale
    |--------------------------------------------------------------------------
    |
    | Language used for dates inside the rendered invitation. This is kept
    | separate from APP_LOCALE on purpose: the admin panel and framework
    | messages may run in English while every guest-facing date must read as
    | Indonesian ("Sabtu, 10 Oktober 2026", not "Saturday, 10 October 2026").
    |
    */

    'locale' => env('INVITATION_LOCALE', 'id'),

];
