@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">Contact Us Settings</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.contact.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.page_title') }}</label>
                    <input type="text" name="page_title" class="form-control" value="{{ $settings->page_title }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.page_subtitle') }}</label>
                    <textarea name="page_subtitle" class="form-control" rows="2">{{ $settings->page_subtitle }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.wechat_section_title') }}</label>
                    <input type="text" name="wechat_section_title" class="form-control" value="{{ $settings->wechat_section_title }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.wechat_section_description') }}</label>
                    <textarea name="wechat_section_description" class="form-control" rows="2">{{ $settings->wechat_section_description }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.whatsapp_section_title') }}</label>
                    <input type="text" name="whatsapp_section_title" class="form-control" value="{{ $settings->whatsapp_section_title }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.whatsapp_section_description') }}</label>
                    <textarea name="whatsapp_section_description" class="form-control" rows="2">{{ $settings->whatsapp_section_description }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.email_section_title') }}</label>
                    <input type="text" name="email_section_title" class="form-control" value="{{ $settings->email_section_title }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.email_section_description') }}</label>
                    <textarea name="email_section_description" class="form-control" rows="2">{{ $settings->email_section_description }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label">{{ __('cms.contact.bottom_message') }}</label>
                    <textarea name="bottom_message" class="form-control" rows="3">{{ $settings->bottom_message }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('cms.contact.response_time_text') }}</label>
                    <input type="text" name="response_time_text" class="form-control" value="{{ $settings->response_time_text }}" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('cms.common.save') }}</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light">{{ __('cms.common.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection