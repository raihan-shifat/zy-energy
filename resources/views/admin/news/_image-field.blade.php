{{-- News cover image field (create + edit).
     JPG/PNG/WebP only, same size limit as the other admin image uploads; the file is
     checked in the browser before submit and again by NewsController.
     Optional: $currentImage (storage path) on the edit page — kept unless a new file is chosen. --}}
@php
    $newsImageMaxBytes = \App\Http\Controllers\Admin\EditorImageController::maxBytes();
    $newsImageMax = \App\Http\Controllers\Admin\EditorImageController::humanLimit();
    $currentImage = $currentImage ?? null;
@endphp
<div class="form-group">
    <label class="form-label d-block" for="image_file">{{ __('cms.news.image') }}</label>

    @if ($currentImage)
        <div class="mb-2">
            <img src="{{ asset('storage/'.$currentImage) }}" class="img-thumbnail" style="max-width:160px" alt="{{ __('cms.news.image_current') }}">
            <div class="small text-muted mt-1">{{ __('cms.news.image_current_hint') }}</div>
        </div>
    @endif

    <label class="btn btn-primary mb-0" for="image_file">{{ __('cms.news.choose_file') }}</label>
    <span class="ms-2 small text-muted" id="image_file_name">{{ __('cms.news.no_file_chosen') }}</span>
    <input type="file" name="image_url" id="image_file" class="d-none" accept="image/jpeg,image/png,image/webp">
    <div class="small text-muted mt-1">{{ __('cms.news.image_hint', ['size' => $newsImageMax]) }}</div>
    <div class="text-danger small mt-1" id="image_file_error" role="alert" aria-live="polite"></div>
    @error('image_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

    <div class="mt-2 d-none" id="image_preview">
        <img id="image_preview_img" class="img-thumbnail" style="max-width:160px" alt="">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('image_file');
    const nameEl = document.getElementById('image_file_name');
    const errorEl = document.getElementById('image_file_error');
    const preview = document.getElementById('image_preview');
    const previewImg = document.getElementById('image_preview_img');
    const maxBytes = @json($newsImageMaxBytes);
    const types = ['image/jpeg', 'image/png', 'image/webp'];
    const t = {
        none: @json(__('cms.news.no_file_chosen')),
        badType: @json(__('cms.news.image_bad_type')),
        tooLarge: @json(__('cms.news.image_too_large', ['size' => $newsImageMax])),
    };

    input.addEventListener('change', function () {
        const file = input.files[0];
        errorEl.textContent = '';
        preview.classList.add('d-none');
        if (previewImg.src.startsWith('blob:')) URL.revokeObjectURL(previewImg.src);

        if (!file) { nameEl.textContent = t.none; return; }
        if (types.indexOf(file.type) === -1 || file.size > maxBytes) {
            errorEl.textContent = file.name + ': ' + (types.indexOf(file.type) === -1 ? t.badType : t.tooLarge);
            input.value = '';            // do not submit an invalid file
            nameEl.textContent = t.none;
            return;
        }
        nameEl.textContent = file.name;
        previewImg.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });
});
</script>
