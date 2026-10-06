{{--
    Shared host-detail dialog.

    A host card stays compact and opens this dialog for the full profile. The
    markup is template-neutral (`invitation-*` / `host-card__*`) so every theme
    styles it through its own `--inv-*` tokens, exactly like the shared forms.
    The wiring lives in resources/js/invitation-hosts.js.
--}}
<dialog class="invitation-host-dialog" data-host-dialog aria-labelledby="invitation-host-dialog-name">
    <div class="invitation-host-dialog__card">
        <button type="button" class="invitation-host-dialog__close" data-host-dialog-close aria-label="Tutup">✕</button>
        <span class="invitation-host-dialog__role" data-host-dialog-role></span>
        <h3 class="invitation-host-dialog__name" id="invitation-host-dialog-name" data-host-dialog-name></h3>
        <div class="invitation-host-dialog__body" data-host-dialog-body></div>
    </div>
</dialog>
