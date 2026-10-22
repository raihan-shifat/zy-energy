@extends('admin.layouts.admin')
@section('content')
@php
    $languages = $activeLanguages;
    $currentLang = in_array(request('lang'), $languages->pluck('code')->all(), true) ? request('lang') : 'en';
    $imageMax = \App\Http\Controllers\Admin\EditorImageController::maxBytes(\App\Http\Controllers\Admin\EditorImageController::ABOUT_IMAGE_MAX_BYTES);
    $videoMax = \App\Http\Controllers\Admin\EditorImageController::maxBytes(\App\Http\Controllers\Admin\EditorImageController::ABOUT_VIDEO_MAX_BYTES);
@endphp
<div class="card mt-4 ab-ed" data-lang="{{ $currentLang }}">
    <div class="card-header card-header-bg text-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0 dt-heading">{{ __('cms.about_editor.title') }}</h6>
        <a href="{{ url('/about') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light">{{ __('cms.about_editor.view_page') }} <i class="fas fa-external-link-alt ms-1"></i></a>
    </div>
    <div class="card-body">
        <p class="text-muted">{{ __('cms.about_editor.intro') }}</p>

        <div class="alert alert-danger" id="abEdErrors" role="alert" tabindex="-1" hidden></div>

        {{-- Language for all translated fields --}}
        <ul class="nav nav-tabs mb-1" role="tablist" aria-label="{{ __('cms.about_editor.language') }}">
            @foreach ($languages as $language)
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link {{ $language->code === $currentLang ? 'active' : '' }}" data-ab-lang="{{ $language->code }}" role="tab" aria-selected="{{ $language->code === $currentLang ? 'true' : 'false' }}">{{ ucwords($language->name) }}</button>
                </li>
            @endforeach
        </ul>
        <p class="small text-muted mb-3">{{ __('cms.about_editor.lang_note') }}</p>

        <form id="aboutEditorForm" action="{{ route('admin.about.update') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div id="abEdSections">
                @foreach ($sections as $section)
                    @include('admin.about._section', ['section' => $section, 'type' => $section->type, 'index' => $section->id])
                @endforeach
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center border-top pt-3 mt-2">
                <select class="form-select w-auto" id="abEdNewType" aria-label="{{ __('cms.about_editor.add_section') }}">
                    @foreach (\App\Models\AboutSection::TYPES as $type)
                        <option value="{{ $type }}">{{ __('cms.about_editor.types.'.$type) }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-primary" id="abEdAdd"><i class="fas fa-plus me-1"></i>{{ __('cms.about_editor.add_section') }}</button>
                <button type="submit" class="btn btn-primary ms-auto" id="abEdSave">{{ __('cms.about_editor.save') }}</button>
            </div>
        </form>

        {{-- Empty section per type, used by "Add section" --}}
        @foreach (\App\Models\AboutSection::TYPES as $type)
            <template id="ab-tpl-{{ $type }}">@include('admin.about._section', ['section' => null, 'type' => $type, 'index' => '__I__'])</template>
        @endforeach

        <input type="file" id="abEdFile" class="d-none">
    </div>
</div>

<style>
    .ab-ed-thumb { width: 96px; height: 72px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6; background: #f8f9fa; }
    .ab-ed-section .card-header { cursor: grab; }
    .ab-ed-section.is-dragging { opacity: .5; }
    .ab-ed-section.is-over { outline: 2px dashed #0d6efd; }
    .ab-ed-preview { max-width: 28rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ab-ed :focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; }
</style>
@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
{{-- Same editor upload pipeline as the product description. --}}
@include('admin.partials.ckeditor-upload-adapter')
<script>
(function () {
    const root = document.querySelector('.ab-ed');
    const form = document.getElementById('aboutEditorForm');
    const list = document.getElementById('abEdSections');
    const errorsBox = document.getElementById('abEdErrors');
    const fileInput = document.getElementById('abEdFile');
    const cfg = {
        token: @json(csrf_token()),
        imageUrl: @json(route('admin.about.upload-image')),
        videoUrl: @json(route('admin.about.upload-video')),
        imageMax: @json($imageMax),
        videoMax: @json($videoMax),
        imageTypes: ['image/jpeg', 'image/png', 'image/webp'],
        videoTypes: ['video/mp4', 'video/webm'],
        t: {
            uploading: @json(__('cms.about_editor.uploading')),
            wait: @json(__('cms.about_editor.upload_wait')),
            imageType: @json(__('cms.about_editor.image_bad_type')),
            videoType: @json(__('cms.about_editor.video_bad_type')),
            imageLarge: @json(__('cms.editor.upload_too_large', ['max' => \App\Http\Controllers\Admin\EditorImageController::humanLimit(\App\Http\Controllers\Admin\EditorImageController::ABOUT_IMAGE_MAX_BYTES)])),
            videoLarge: @json(__('cms.editor.upload_too_large', ['max' => \App\Http\Controllers\Admin\EditorImageController::humanLimit(\App\Http\Controllers\Admin\EditorImageController::ABOUT_VIDEO_MAX_BYTES)])),
            sessionExpired: @json(__('cms.editor.upload_session_expired')),
            failed: @json(__('cms.editor.upload_failed_status')),
            galleryMax: @json(__('cms.about_editor.gallery_too_many', ['max' => \App\Http\Controllers\Admin\AboutPageController::MAX_GALLERY])),
            removeConfirm: @json(__('cms.about_editor.remove_confirm')),
            saving: @json(__('cms.about_editor.saving')),
            save: @json(__('cms.about_editor.save')),
            saved: @json(__('cms.about_editor.saved')),
            saveFailed: @json(__('cms.about_editor.save_failed')),
            fixErrors: @json(__('cms.about_editor.fix_errors')),
        },
    };
    let seq = 0;
    let pendingUploads = 0;

    function notify(type, message) {
        if (window.toastr) { toastr[type](message); } else { alert(message); }
    }

    // ---------- Language ----------
    function applyLang() {
        const lang = root.dataset.lang;
        root.querySelectorAll('.ab-ed-lang').forEach(el => { el.hidden = el.dataset.lang !== lang; });
        root.querySelectorAll('[data-ab-lang]').forEach(b => {
            b.classList.toggle('active', b.dataset.abLang === lang);
            b.setAttribute('aria-selected', b.dataset.abLang === lang ? 'true' : 'false');
        });
        initEditors(list);
    }
    root.querySelectorAll('[data-ab-lang]').forEach(b => b.addEventListener('click', () => { root.dataset.lang = b.dataset.abLang; applyLang(); }));

    // ---------- Rich text (only for visible, opened sections; created on first use) ----------
    const editors = [];
    function initEditors(scope) {
        if (typeof ClassicEditor === 'undefined') return;
        scope.querySelectorAll('textarea.ab-ed-rich').forEach(ta => {
            if (ta.dataset.ready || ta.closest('[hidden]')) return;
            ta.dataset.ready = '1';
            ClassicEditor.create(ta, { extraPlugins: [ZyEditorUploadPlugin] })
                .then(ed => editors.push(ed))
                .catch(err => { console.error(err); delete ta.dataset.ready; });
        });
    }

    // ---------- Sections ----------
    function section(el) { return el.closest('.ab-ed-section'); }

    list.addEventListener('click', function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const sec = section(btn);
        if (btn.matches('[data-ab-move]')) {
            const sibling = btn.dataset.abMove === '-1' ? sec.previousElementSibling : sec.nextElementSibling;
            if (sibling) btn.dataset.abMove === '-1' ? list.insertBefore(sec, sibling) : list.insertBefore(sibling, sec);
            btn.focus();
        } else if (btn.matches('[data-ab-toggle]')) {
            const body = sec.querySelector('.ab-ed-body');
            body.hidden = !body.hidden;
            btn.setAttribute('aria-expanded', body.hidden ? 'false' : 'true');
            initEditors(sec);
        } else if (btn.matches('[data-ab-remove]')) {
            if (confirm(cfg.t.removeConfirm)) sec.remove();
        } else if (btn.matches('[data-ab-add-item]')) {
            const box = sec.querySelector('.ab-ed-items');
            if (box.children.length >= Number(box.dataset.max)) return;
            const html = sec.querySelector('.ab-ed-item-tpl').innerHTML.replaceAll('__K__', 'n' + (++seq));
            box.insertAdjacentHTML('beforeend', html);
            applyLang();
        } else if (btn.matches('[data-ab-remove-item]')) {
            btn.closest('.ab-ed-item').remove();
        } else if (btn.matches('[data-ab-gmove]')) {
            const row = btn.closest('.ab-ed-gitem');
            const sibling = btn.dataset.abGmove === '-1' ? row.previousElementSibling : row.nextElementSibling;
            if (sibling) btn.dataset.abGmove === '-1' ? row.parentNode.insertBefore(row, sibling) : row.parentNode.insertBefore(sibling, row);
            btn.focus();
        } else if (btn.matches('[data-ab-gremove]')) {
            btn.closest('.ab-ed-gitem').remove();
        } else if (btn.matches('[data-ab-clear]')) {
            const slot = btn.closest('.ab-ed-media-single');
            slot.querySelector('.ab-ed-path').value = '';
            const img = slot.querySelector('.ab-ed-thumb'); if (img) { img.hidden = true; img.removeAttribute('src'); }
            const name = slot.querySelector('.ab-ed-file'); if (name) name.textContent = '';
            btn.hidden = true;
        } else if (btn.matches('[data-ab-upload]')) {
            pickFiles(btn);
        }
    });

    list.addEventListener('change', function (e) {
        if (e.target.matches('.ab-ed-active')) {
            section(e.target).querySelector('.ab-ed-hidden-badge').hidden = e.target.checked;
        }
    });
    list.addEventListener('input', function (e) {
        if (e.target.matches('.ab-ed-title') && e.target.closest('.ab-ed-lang').dataset.lang === 'en') {
            section(e.target).querySelector('.ab-ed-preview').textContent = e.target.value;
        }
    });

    // Drag a section by its header.
    let dragged = null;
    list.addEventListener('dragstart', e => { const h = e.target.closest('.card-header'); if (!h) return; dragged = section(h); dragged.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; });
    list.addEventListener('dragend', () => { if (dragged) dragged.classList.remove('is-dragging'); dragged = null; list.querySelectorAll('.is-over').forEach(x => x.classList.remove('is-over')); });
    list.addEventListener('dragover', e => { const s = e.target.closest('.ab-ed-section'); if (dragged && s && s !== dragged) { e.preventDefault(); s.classList.add('is-over'); } });
    list.addEventListener('dragleave', e => { const s = e.target.closest('.ab-ed-section'); if (s) s.classList.remove('is-over'); });
    list.addEventListener('drop', e => {
        const s = e.target.closest('.ab-ed-section');
        if (!dragged || !s || s === dragged) return;
        e.preventDefault();
        s.classList.remove('is-over');
        const after = [...list.children].indexOf(dragged) < [...list.children].indexOf(s);
        list.insertBefore(dragged, after ? s.nextSibling : s);
    });

    document.getElementById('abEdAdd').addEventListener('click', function () {
        const type = document.getElementById('abEdNewType').value;
        const html = document.getElementById('ab-tpl-' + type).innerHTML.replaceAll('__I__', 'new' + (++seq));
        list.insertAdjacentHTML('beforeend', html);
        const sec = list.lastElementChild;
        applyLang();
        sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        sec.querySelector('.card-header button').focus({ preventScroll: true });
    });

    // ---------- Uploads (one file per request, with progress) ----------
    function upload(file, url, onProgress) {
        return new Promise(function (resolve, reject) {
            const xhr = new XMLHttpRequest();
            const data = new FormData();
            data.append('upload', file);
            xhr.open('POST', url);
            xhr.setRequestHeader('X-CSRF-TOKEN', cfg.token);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = e => { if (e.lengthComputable) onProgress(Math.round(e.loaded / e.total * 100)); };
            xhr.onload = function () {
                let body = {};
                try { body = JSON.parse(xhr.responseText); } catch (e) {}
                if (xhr.status >= 200 && xhr.status < 300 && body.path) return resolve(body);
                if (body.error && body.error.message) return reject(body.error.message);
                if (xhr.status === 413) return reject(url === cfg.videoUrl ? cfg.t.videoLarge : cfg.t.imageLarge);
                if (xhr.status === 419 || xhr.status === 401 || xhr.responseURL.indexOf('/login') !== -1) return reject(cfg.t.sessionExpired);
                reject(cfg.t.failed.replace(':status', xhr.status));
            };
            xhr.onerror = () => reject(cfg.t.failed.replace(':status', 0));
            xhr.send(data);
        });
    }

    function checkFile(file, kind) {
        const video = kind === 'video';
        if ((video ? cfg.videoTypes : cfg.imageTypes).indexOf(file.type) === -1) return file.name + ': ' + (video ? cfg.t.videoType : cfg.t.imageType);
        if (file.size > (video ? cfg.videoMax : cfg.imageMax)) return file.name + ': ' + (video ? cfg.t.videoLarge : cfg.t.imageLarge);
        return null;
    }

    function track(promise) {
        pendingUploads++;
        return promise.finally(() => { pendingUploads--; });
    }

    let pickTarget = null;
    function pickFiles(btn) {
        pickTarget = btn;
        const kind = btn.dataset.abUpload;
        fileInput.multiple = kind === 'gallery';
        fileInput.accept = kind === 'video' ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp';
        fileInput.value = '';
        fileInput.click();
    }

    fileInput.addEventListener('change', function () {
        const btn = pickTarget;
        if (!btn || !fileInput.files.length) return;
        const kind = btn.dataset.abUpload;
        const sec = section(btn);
        const files = [...fileInput.files];

        if (kind === 'gallery') {
            const box = sec.querySelector('.ab-ed-gallery');
            files.forEach(file => {
                if (box.children.length >= Number(box.dataset.max)) { notify('error', cfg.t.galleryMax); return; }
                const problem = checkFile(file, 'image');
                if (problem) { notify('error', problem); return; }
                box.insertAdjacentHTML('beforeend', sec.querySelector('.ab-ed-gallery-tpl').innerHTML.replaceAll('__J__', 'n' + (++seq)));
                const row = box.lastElementChild;
                const thumb = row.querySelector('.ab-ed-thumb');
                const progress = row.querySelector('.ab-ed-progress');
                thumb.src = URL.createObjectURL(file);
                applyLang();
                track(upload(file, cfg.imageUrl, p => { progress.textContent = cfg.t.uploading.replace(':percent', p); }))
                    .then(body => { row.querySelector('.ab-ed-path').value = body.path; progress.textContent = ''; })
                    .catch(message => { row.remove(); notify('error', file.name + ': ' + message); });
            });
            return;
        }

        const file = files[0];
        const slot = btn.closest('.ab-ed-media-single');
        const problem = checkFile(file, kind === 'video' ? 'video' : 'image');
        if (problem) { notify('error', problem); return; }
        const progress = slot.querySelector('.ab-ed-progress');
        track(upload(file, kind === 'video' ? cfg.videoUrl : cfg.imageUrl, p => { progress.textContent = cfg.t.uploading.replace(':percent', p); }))
            .then(body => {
                slot.querySelector('.ab-ed-path').value = body.path;
                progress.textContent = '';
                const thumb = slot.querySelector('.ab-ed-thumb'); if (thumb) { thumb.src = body.url; thumb.hidden = false; }
                const name = slot.querySelector('.ab-ed-file'); if (name) name.textContent = file.name;
                const url = slot.querySelector('.ab-ed-video-url'); if (url) url.value = '';
                const clear = slot.querySelector('[data-ab-clear]'); if (clear) clear.hidden = false;
            })
            .catch(message => { progress.textContent = ''; notify('error', file.name + ': ' + message); });
    });

    // ---------- Save ----------
    // Sent as JSON, not as a form: with every language the editor has far more fields than
    // PHP's max_input_vars (1000) allows, and PHP would silently drop the rest.
    function sectionData(sec) {
        const data = {};
        sec.querySelectorAll('input[name], select[name], textarea[name]').forEach(el => {
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
            // sections[KEY][a][b]… -> path a, b, … inside this section
            const path = [...el.name.matchAll(/\[([^\]]*)\]/g)].map(m => m[1]).slice(1);
            let node = data;
            path.forEach((key, i) => {
                // Empty fields are sent as null (e.g. the id of a new row).
                if (i === path.length - 1) { node[key] = el.value === '' ? null : el.value; return; }
                node[key] = node[key] && typeof node[key] === 'object' ? node[key] : {};
                node = node[key];
            });
        });
        // Rows (items, media) are sent as arrays in the order shown on screen.
        if (data.items) data.items = Object.values(data.items);
        if (data.media) data.media = Object.values(data.media);
        Object.values(data.tr || {}).forEach(t => { if (t.items) t.items = Object.values(t.items); });
        return data;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const editorBusy = editors.some(ed => ed.plugins.has('PendingActions') && ed.plugins.get('PendingActions').hasAny);
        if (pendingUploads > 0 || editorBusy) { alert(cfg.t.wait); return; }
        editors.forEach(ed => ed.updateSourceElement());

        const saveBtn = document.getElementById('abEdSave');
        saveBtn.disabled = true;
        saveBtn.textContent = cfg.t.saving;
        errorsBox.hidden = true;
        const payload = { sections: [...list.querySelectorAll(':scope > .ab-ed-section')].map(sectionData) };

        fetch(form.action, {
            method: 'PUT',
            body: JSON.stringify(payload),
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': cfg.token },
        })
            .then(r => r.json().catch(() => ({})).then(body => ({ status: r.status, body })))
            .then(({ status, body }) => {
                if (status >= 200 && status < 300) {
                    notify('success', body.message || cfg.t.saved);
                    setTimeout(() => { location.href = location.pathname + '?lang=' + encodeURIComponent(root.dataset.lang); }, 700);
                    return;
                }
                if (status === 422 && body.errors) {
                    const items = [...new Set(Object.values(body.errors).flat())].map(m => '<li>' + String(m).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])) + '</li>').join('');
                    errorsBox.innerHTML = '<strong>' + cfg.t.fixErrors + '</strong><ul class="mb-0 mt-2">' + items + '</ul>';
                } else if (status === 419) {
                    errorsBox.textContent = cfg.t.sessionExpired;
                } else {
                    errorsBox.textContent = cfg.t.saveFailed + ' (' + status + ')';
                }
                errorsBox.hidden = false;
                errorsBox.focus();
                errorsBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            })
            .catch(() => { errorsBox.textContent = cfg.t.saveFailed; errorsBox.hidden = false; })
            .finally(() => { saveBtn.disabled = false; saveBtn.textContent = cfg.t.save; });
    });

    applyLang();
})();
</script>
@endsection
