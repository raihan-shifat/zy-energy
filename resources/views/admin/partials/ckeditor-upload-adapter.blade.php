{{-- CKEditor 5 upload adapter for the "Insert image" button.
     The CDN classic build has no adapter configured, so picked images had nowhere to go.
     Use: ClassicEditor.create(el, { extraPlugins: [ZyEditorUploadPlugin] })
     Also blocks form submit while an upload is running: the editor shows a local preview
     during upload, but saving then stores an <img> without src. --}}
<script>
    const ZY_EDITOR_UPLOAD = {
        url: @json(route('admin.editor.upload-image')),
        token: @json(csrf_token()),
        maxBytes: @json(\App\Http\Controllers\Admin\EditorImageController::maxBytes()),
        messages: {
            tooLarge: @json(__('cms.editor.upload_too_large', ['max' => \App\Http\Controllers\Admin\EditorImageController::humanLimit()])),
            sessionExpired: @json(__('cms.editor.upload_session_expired')),
            failed: @json(__('cms.editor.upload_failed_status')),
            wait: @json(__('cms.editor.upload_wait')),
        },
    };

    class ZyEditorUploadAdapter {
        constructor(loader) {
            this.loader = loader;
            this.controller = new AbortController();
        }

        upload() {
            return this.loader.file.then(file => {
                if (file.size > ZY_EDITOR_UPLOAD.maxBytes) {
                    throw ZY_EDITOR_UPLOAD.messages.tooLarge;
                }

                const data = new FormData();
                data.append('upload', file);

                return fetch(ZY_EDITOR_UPLOAD.url, {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': ZY_EDITOR_UPLOAD.token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: this.controller.signal,
                }).then(response => response.json().catch(() => ({})).then(body => {
                    if (response.ok && body.url) {
                        return { default: body.url };
                    }
                    if (body.error && body.error.message) {
                        throw body.error.message;
                    }
                    // Non-JSON answers come from the web server or an expired login.
                    if (response.status === 413) {
                        throw ZY_EDITOR_UPLOAD.messages.tooLarge;
                    }
                    if (response.status === 419 || response.status === 401 || response.redirected) {
                        throw ZY_EDITOR_UPLOAD.messages.sessionExpired;
                    }
                    throw ZY_EDITOR_UPLOAD.messages.failed.replace(':status', response.status);
                }));
            });
        }

        abort() {
            this.controller.abort();
        }
    }

    function ZyEditorUploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = loader => new ZyEditorUploadAdapter(loader);
    }

    // Do not save while any editor on the page is still uploading an image.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            if (!form.querySelector('.ck-editor-multi-languages')) {
                return;
            }
            // Capture phase: runs before the page's own submit handlers (e.g. "Saving..." lock).
            form.addEventListener('submit', function (event) {
                const uploading = Array.from(form.querySelectorAll('.ck-editor__editable')).some(function (el) {
                    const editor = el.ckeditorInstance;
                    return editor && editor.plugins.has('PendingActions') && editor.plugins.get('PendingActions').hasAny;
                });
                if (uploading) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    alert(ZY_EDITOR_UPLOAD.messages.wait);
                }
            }, true);
        });
    });
</script>
