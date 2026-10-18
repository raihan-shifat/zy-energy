{{-- Optional hero video for this banner (shared by create and edit). --}}
@php
    $bannerVideo = isset($banner) ? (string) $banner->video_url : '';
    $bannerVideoIsUpload = $bannerVideo !== '' && ! preg_match('#^https?://#i', $bannerVideo);
@endphp
<div class="card mt-4">
    <div class="card-body">
        <h5 class="mb-3"><i class="bi bi-play-circle me-1"></i>{{ __('cms.banners.video') }}</h5>

        @if ($bannerVideo !== '')
            <div class="alert alert-light border py-2 mb-3">
                <strong>{{ __('cms.banners.video_current') }}:</strong>
                @if ($bannerVideoIsUpload)
                    <a href="{{ asset('storage/'.$bannerVideo) }}" target="_blank" rel="noopener">{{ basename($bannerVideo) }}</a>
                @else
                    <a href="{{ $bannerVideo }}" target="_blank" rel="noopener noreferrer">{{ $bannerVideo }}</a>
                @endif
                @if (! $banner->video_embed)
                    <div class="text-danger small mt-1">{{ __('cms.banners.video_unsupported') }}</div>
                @endif
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove_video">
                    <label class="form-check-label" for="remove_video">{{ __('cms.banners.video_remove') }}</label>
                </div>
            </div>
        @endif

        <div class="mb-3">
            <label for="video_url" class="form-label">{{ __('cms.banners.video_url') }}</label>
            <input type="url" name="video_url" id="video_url" maxlength="500"
                   class="form-control @error('video_url') is-invalid @enderror"
                   placeholder="https://www.youtube.com/watch?v=..."
                   value="{{ old('video_url', $bannerVideoIsUpload ? '' : $bannerVideo) }}">
            <div class="form-text">{{ __('cms.banners.video_url_help') }}</div>
            @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="video_file" class="form-label">{{ __('cms.banners.video_file') }}</label>
            <input type="file" name="video_file" id="video_file" accept="video/mp4,video/webm"
                   class="form-control @error('video_file') is-invalid @enderror">
            <div class="form-text">{{ __('cms.banners.video_file_help') }}</div>
            @error('video_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>
