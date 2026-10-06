{{--
    Social preview for a shared invitation link. WhatsApp, Facebook, and the rest
    read these tags instead of the page body, so the link shows a photo, a title,
    and a short line. The image is chosen per invitation in the admin panel
    (settings_json.share_image) and falls back to the cover, then the first photo.
--}}
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="Anda diundang ke {{ $title }}.">
@isset ($share_image)
    <meta property="og:image" content="{{ $share_image }}">
    <meta property="og:image:alt" content="{{ $title }}">
    <meta name="twitter:card" content="summary_large_image">
@endisset
