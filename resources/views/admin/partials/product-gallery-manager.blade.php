{{-- Product image gallery manager (admin create + edit).
     Each picked file is uploaded on its own (products: admin.products.upload-image; same pipeline
     as the description editor), so one big form POST never hits the server upload limit.
     The form then submits the list in display order: gallery[i][id] for existing images,
     gallery[i][path] for new uploads, gallery_primary = index of the cover image.
     Expects $existingImages (collection of ProductImage, may be empty). --}}
@php
    // Defaults are the product settings; the news form passes its own.
    $galleryMax = $galleryMax ?? \App\Http\Controllers\Admin\ProductController::GALLERY_MAX;
    $galleryUploadUrl = $galleryUploadUrl ?? route('admin.products.upload-image');
    $galleryFolder = $galleryFolder ?? 'products';
    $galleryPathField = $galleryPathField ?? 'image_url';
    $galleryCap = $galleryCap ?? 5 * 1024 * 1024;
    $galleryMaxBytes = \App\Http\Controllers\Admin\EditorImageController::maxBytes($galleryCap);
    $galleryLimit = \App\Http\Controllers\Admin\EditorImageController::humanLimit($galleryCap);
    $galleryLabel = $galleryLabel ?? __('cms.products.images');
    $galleryHint = $galleryHint ?? __('cms.products.gallery_hint', ['max' => $galleryMax, 'size' => $galleryLimit]);
    $existingImages = collect($existingImages ?? []);
    $galleryItems = [];
    $galleryPrimary = 0;

    if (is_array(old('gallery'))) {
        // Back from a validation error: rebuild what the admin had.
        foreach (old('gallery') as $item) {
            if (! empty($item['id']) && ($image = $existingImages->firstWhere('id', (int) $item['id']))) {
                $galleryItems[] = ['id' => $image->id, 'url' => asset('storage/'.$image->{$galleryPathField})];
            } elseif (! empty($item['path']) && preg_match('#^'.preg_quote($galleryFolder, '#').'/[A-Za-z0-9._-]+$#', $item['path'])) {
                $galleryItems[] = ['path' => $item['path'], 'url' => asset('storage/'.$item['path'])];
            }
        }
        $galleryPrimary = (int) old('gallery_primary', 0);
    } else {
        foreach ($existingImages as $index => $image) {
            $galleryItems[] = ['id' => $image->id, 'url' => asset('storage/'.$image->{$galleryPathField})];
            if ($image->is_primary) {
                $galleryPrimary = count($galleryItems) - 1;
            }
        }
    }
@endphp

<div class="mt-4 pgm" id="productGalleryManager">
    <label class="form-label d-block" for="pgmFile">{{ $galleryLabel }}</label>
    <p class="text-muted small mb-2">{{ $galleryHint }}</p>

    <ul class="pgm-grid list-unstyled" id="pgmGrid" aria-label="{{ $galleryLabel }}"></ul>
    <p class="text-muted small pgm-empty" id="pgmEmpty">{{ __('cms.products.gallery_empty') }}</p>

    <label class="btn btn-primary mt-2" for="pgmFile" id="pgmAddLabel">{{ __('cms.products.choose_file') }}</label>
    <input type="file" id="pgmFile" class="d-none" accept="image/jpeg,image/png,image/webp" multiple>

    <div class="text-danger small mt-2" id="pgmErrors" role="alert" aria-live="polite"></div>
    @error('gallery')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('gallery.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

    <input type="hidden" name="gallery_present" value="1">
    <div id="pgmInputs"></div>
</div>

<style>
    .pgm-grid { display: flex; flex-wrap: wrap; gap: 12px; margin: 0; padding: 0; }
    .pgm-item { width: 150px; border: 1px solid #dee2e6; border-radius: 8px; padding: 6px; background: #fff; position: relative; cursor: grab; }
    .pgm-item.is-cover { border-color: #0d6efd; box-shadow: 0 0 0 2px rgba(13, 110, 253, .25); }
    .pgm-item.is-dragging { opacity: .4; }
    .pgm-item.is-over { outline: 2px dashed #0d6efd; outline-offset: 2px; }
    .pgm-thumb { aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 6px; background: #f8f9fa; }
    .pgm-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .pgm-badge { position: absolute; top: 10px; left: 10px; font-size: 11px; }
    .pgm-remove { position: absolute; top: 8px; right: 8px; width: 30px; height: 30px; padding: 0; border-radius: 50%; }
    .pgm-actions { display: flex; gap: 4px; margin-top: 6px; }
    .pgm-actions .btn { padding: 2px 6px; font-size: 12px; }
    .pgm-actions .pgm-cover-btn { flex: 1; }
    .pgm-progress { position: absolute; inset: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, .75); border-radius: 6px; font-size: 12px; }
    .pgm-item :focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; }
</style>

<script>
(function () {
    const cfg = {
        uploadUrl: @json($galleryUploadUrl),
        token: @json(csrf_token()),
        max: @json($galleryMax),
        maxBytes: @json($galleryMaxBytes),
        types: ['image/jpeg', 'image/png', 'image/webp'],
        t: {
            cover: @json(__('cms.products.gallery_cover')),
            setCover: @json(__('cms.products.gallery_set_cover')),
            left: @json(__('cms.products.gallery_move_left')),
            right: @json(__('cms.products.gallery_move_right')),
            remove: @json(__('cms.products.gallery_remove')),
            uploading: @json(__('cms.products.gallery_uploading')),
            imageN: @json(__('cms.products.gallery_image_n')),
            tooMany: @json($galleryTooMany ?? __('cms.products.gallery_too_many', ['max' => $galleryMax])),
            badType: @json(__('cms.products.gallery_bad_type')),
            tooLarge: @json(__('cms.editor.upload_too_large', ['max' => $galleryLimit])),
            sessionExpired: @json(__('cms.editor.upload_session_expired')),
            failed: @json(__('cms.editor.upload_failed_status')),
            wait: @json(__('cms.products.gallery_wait')),
        },
    };

    // { id?, path?, url, uploading?, key }
    let items = @json($galleryItems).map((it, i) => Object.assign({ key: 'k' + i }, it));
    let primary = Math.min(Math.max(@json($galleryPrimary), 0), Math.max(items.length - 1, 0));
    let keySeq = items.length;
    let dragKey = null;

    const root = document.getElementById('productGalleryManager');
    const grid = document.getElementById('pgmGrid');
    const empty = document.getElementById('pgmEmpty');
    const fileInput = document.getElementById('pgmFile');
    const errors = document.getElementById('pgmErrors');
    const inputs = document.getElementById('pgmInputs');
    const addLabel = document.getElementById('pgmAddLabel');

    function showError(message) {
        const line = document.createElement('div');
        line.textContent = message;
        errors.appendChild(line);
    }

    function move(from, to) {
        if (to < 0 || to >= items.length || from === to) return;
        const coverKey = items[primary] && items[primary].key;
        const [moved] = items.splice(from, 1);
        items.splice(to, 0, moved);
        primary = Math.max(0, items.findIndex(it => it.key === coverKey));
        render();
    }

    function button(cls, label, html, onClick) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = cls;
        b.setAttribute('aria-label', label);
        b.title = label;
        b.innerHTML = html;
        b.addEventListener('click', onClick);
        return b;
    }

    function render() {
        grid.innerHTML = '';
        inputs.innerHTML = '';
        empty.style.display = items.length ? 'none' : '';
        addLabel.classList.toggle('disabled', items.length >= cfg.max);

        items.forEach((it, index) => {
            const li = document.createElement('li');
            li.className = 'pgm-item' + (index === primary ? ' is-cover' : '');
            li.draggable = !it.uploading;
            li.dataset.key = it.key;
            li.setAttribute('aria-label', cfg.t.imageN.replace(':n', index + 1) + (index === primary ? ' (' + cfg.t.cover + ')' : ''));

            const thumb = document.createElement('div');
            thumb.className = 'pgm-thumb';
            const img = document.createElement('img');
            img.src = it.url;
            img.alt = cfg.t.imageN.replace(':n', index + 1);
            thumb.appendChild(img);
            li.appendChild(thumb);

            if (index === primary) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary pgm-badge';
                badge.textContent = cfg.t.cover;
                li.appendChild(badge);
            }

            li.appendChild(button('btn btn-light border pgm-remove', cfg.t.remove, '<i class="fa-solid fa-xmark text-danger"></i>', () => {
                const coverKey = items[primary] && items[primary].key;
                items.splice(index, 1);
                const keep = items.findIndex(x => x.key === coverKey);
                primary = keep === -1 ? 0 : keep;
                render();
            }));

            const actions = document.createElement('div');
            actions.className = 'pgm-actions';
            actions.appendChild(button('btn btn-outline-secondary', cfg.t.left, '<i class="fa-solid fa-arrow-left"></i>', () => move(index, index - 1)));
            const coverBtn = button('btn ' + (index === primary ? 'btn-primary' : 'btn-outline-primary') + ' pgm-cover-btn',
                index === primary ? cfg.t.cover : cfg.t.setCover, index === primary ? cfg.t.cover : cfg.t.setCover, () => { primary = index; render(); });
            coverBtn.setAttribute('aria-pressed', index === primary ? 'true' : 'false');
            actions.appendChild(coverBtn);
            actions.appendChild(button('btn btn-outline-secondary', cfg.t.right, '<i class="fa-solid fa-arrow-right"></i>', () => move(index, index + 1)));
            li.appendChild(actions);

            if (it.uploading) {
                const progress = document.createElement('div');
                progress.className = 'pgm-progress';
                progress.textContent = cfg.t.uploading;
                li.appendChild(progress);
            }

            // Drag to reorder (mouse); the arrow buttons do the same from the keyboard.
            li.addEventListener('dragstart', e => { dragKey = it.key; li.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; });
            li.addEventListener('dragend', () => { dragKey = null; li.classList.remove('is-dragging'); });
            li.addEventListener('dragover', e => { if (dragKey) { e.preventDefault(); li.classList.add('is-over'); } });
            li.addEventListener('dragleave', () => li.classList.remove('is-over'));
            li.addEventListener('drop', e => {
                e.preventDefault();
                li.classList.remove('is-over');
                const from = items.findIndex(x => x.key === dragKey);
                if (from !== -1) move(from, index);
            });

            grid.appendChild(li);

            if (!it.uploading) {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = 'gallery[' + inputs.children.length + ']' + (it.id ? '[id]' : '[path]');
                field.value = it.id || it.path;
                inputs.appendChild(field);
            }
        });

        // Cover index counted among saved (non-uploading) items only.
        const saved = items.filter(it => !it.uploading);
        const coverKey = items[primary] && items[primary].key;
        const cover = document.createElement('input');
        cover.type = 'hidden';
        cover.name = 'gallery_primary';
        cover.value = Math.max(0, saved.findIndex(it => it.key === coverKey));
        inputs.appendChild(cover);
    }

    function upload(it, file) {
        const data = new FormData();
        data.append('upload', file);
        return fetch(cfg.uploadUrl, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': cfg.token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        }).then(response => response.json().catch(() => ({})).then(body => {
            if (response.ok && body.url && body.path) {
                return body;
            }
            if (body.error && body.error.message) throw body.error.message;
            if (response.status === 413) throw cfg.t.tooLarge;
            if (response.status === 419 || response.status === 401 || response.redirected) throw cfg.t.sessionExpired;
            throw cfg.t.failed.replace(':status', response.status);
        }));
    }

    fileInput.addEventListener('change', function () {
        errors.innerHTML = '';
        Array.from(fileInput.files).forEach(file => {
            if (items.length >= cfg.max) { showError(cfg.t.tooMany); return; }
            if (cfg.types.indexOf(file.type) === -1) { showError(file.name + ': ' + cfg.t.badType); return; }
            if (file.size > cfg.maxBytes) { showError(file.name + ': ' + cfg.t.tooLarge); return; }

            const it = { key: 'k' + (keySeq++), url: URL.createObjectURL(file), uploading: true };
            items.push(it);
            upload(it, file).then(body => {
                URL.revokeObjectURL(it.url);
                it.uploading = false;
                it.path = body.path;
                it.url = body.url;
                render();
            }).catch(message => {
                const coverKey = items[primary] && items[primary].key;
                items = items.filter(x => x !== it);
                const keep = items.findIndex(x => x.key === coverKey);
                primary = keep === -1 ? 0 : keep;
                showError(file.name + ': ' + message);
                render();
            });
        });
        fileInput.value = '';
        render();
    });

    // Do not save while an image is still uploading (it would be missing from the product).
    root.closest('form').addEventListener('submit', function (event) {
        if (items.some(it => it.uploading)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            alert(cfg.t.wait);
        }
    }, true);

    render();
})();
</script>
