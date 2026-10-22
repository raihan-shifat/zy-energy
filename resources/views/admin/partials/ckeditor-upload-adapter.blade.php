{{-- CKEditor 5 upload adapter for the "Insert image" button.
     The CDN classic build has no adapter configured, so picked images had nowhere to go.
     Use: ClassicEditor.create(el, { extraPlugins: [ZyEditorUploadPlugin] }) --}}
<script>
    class ZyEditorUploadAdapter {
        constructor(loader) {
            this.loader = loader;
            this.controller = new AbortController();
        }

        upload() {
            return this.loader.file.then(file => {
                const data = new FormData();
                data.append('upload', file);

                return fetch(@json(route('admin.editor.upload-image')), {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: this.controller.signal,
                }).then(response => response.json().catch(() => ({})).then(body => {
                    if (!response.ok || !body.url) {
                        throw (body.error && body.error.message) || @json(__('cms.editor.upload_failed'));
                    }
                    return { default: body.url };
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
</script>
