<style>
    @media (max-width: 1023px) {
        .fi-fo-file-upload-editor-window {
            overflow-y: auto !important;
        }

        .fi-fo-file-upload-editor-image-ctn {
            flex: 0 0 min(52dvh, 420px) !important;
            height: min(52dvh, 420px) !important;
            min-height: 260px;
        }

        .fi-fo-file-upload-editor-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
        }

        .fi-fo-file-upload-editor-control-panel {
            flex: none !important;
            height: auto !important;
            min-height: max-content;
            overflow: visible !important;
        }

        .fi-fo-file-upload-editor-control-panel-main {
            overflow: visible !important;
        }

        .fi-fo-file-upload-editor-control-panel-footer {
            position: sticky;
            bottom: 0;
            z-index: 1;
            background: inherit;
        }
    }
</style>
