@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 dt-heading">Edit WeChat Contact</h6>
        <a href="{{ route('admin.contact.wechat.index') }}" class="btn btn-sm btn-light">Back to List</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.contact.wechat.update', $wechat) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $wechat->name }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">WeChat ID</label>
                    <input type="text" name="wechat_id" class="form-control" value="{{ $wechat->wechat_id }}">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Purpose / Department</label>
                    <input type="text" name="purpose" class="form-control" value="{{ $wechat->purpose }}" placeholder="e.g., Business Inquiries, After-Sales Support">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $wechat->sort_order }}" min="0">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">WeChat ID</label>
                    <input type="text" name="wechat_id" class="form-control" value="{{ $wechat->wechat_id }}" placeholder="WeChat ID">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">QR Code Image</label>
                    @if($wechat->qr_code)
                        <div class="mb-2">
                            <img src="{{ $wechat->qr_code_url }}" alt="Current QR Code" style="max-width: 150px; max-height: 150px;">
                        </div>
                    @endif
                    <input type="file" name="qr_code" class="form-control @error('qr_code') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
                    @error('qr_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Allowed formats: JPG, PNG, GIF, WEBP. Max size: 5MB</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $wechat->description }}</textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $wechat->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $wechat->sort_order }}" min="0">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.contact.wechat.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection