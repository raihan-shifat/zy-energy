@extends('admin.layouts.admin')

@section('content')

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h6>Edit Site Settings</h6>
        </div>
        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                <!-- Site Name / Company Name -->
                <div class="form-group">
                    <label for="site_name">Company / Brand Name</label>
                    <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings->site_name ?? '') }}" required>
                    <small class="text-muted">Displayed beside the main logo when present.</small>
                </div>

                <!-- Main Header Logo -->
                <div class="form-group">
                    <label for="logo">Main Header Logo</label>
                    <input type="file" name="logo" accept="image/*" class="form-control">
                    @if($settings->logo)
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Current main logo" class="mt-2" style="max-width:180px; max-height:70px; object-fit:contain;">
                    @endif
                    <small class="text-muted">Used in the site header and as the default footer logo.</small>
                </div>

                <!-- Header Brand Name Image -->
                <div class="form-group">
                    <label for="header_brand_name_image">Header Brand Name Image</label>
                    <input type="file" name="header_brand_name_image" accept="image/*" class="form-control">
                    @if($settings->header_brand_name_image)
                        <img src="{{ asset('storage/' . $settings->header_brand_name_image) }}" alt="Current header brand name" class="mt-2" style="max-width:240px; max-height:50px; object-fit:contain;">
                    @endif
                    <small class="text-muted">Optional image displayed beside the main header logo. Leave empty to show the company name as text.</small>
                </div>

                <!-- Footer Logo -->
                <div class="form-group">
                    <label for="footer_logo">Footer Logo</label>
                    <input type="file" name="footer_logo" accept="image/*" class="form-control">
                    @if($settings->footer_logo)
                        <img src="{{ asset('storage/' . $settings->footer_logo) }}" alt="Current footer logo" class="mt-2" style="max-width:180px; max-height:70px; object-fit:contain;">
                    @endif
                    <small class="text-muted">Optional separate logo. Leave empty to share the main header logo.</small>
                </div>

                <!-- Tagline -->
                <div class="form-group">
                    <label for="tagline">Tagline</label>
                    <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $settings->tagline ?? '') }}">
                </div>

                <!-- Footer Description -->
                <div class="form-group">
                    <label for="footer_description">Footer Description</label>
                    <textarea name="footer_description" class="form-control" rows="4">{{ old('footer_description', $settings->footer_description ?? $settings->footer_text ?? '') }}</textarea>
                    <small class="text-muted">Company bio shown below the footer logo.</small>
                </div>

                <!-- Meta Title -->
                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $settings->meta_title ?? '') }}">
                </div>

                <!-- Meta Description -->
                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea name="meta_description" class="form-control">{{ old('meta_description', $settings->meta_description ?? '') }}</textarea>
                </div>

                <!-- Meta Keywords -->
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $settings->meta_keywords ?? '') }}">
                </div>

                <!-- Contact Email -->
                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email ?? '') }}">
                </div>

                <!-- Contact Phone -->
                <div class="form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone ?? '') }}">
                </div>

                <!-- WhatsApp Number -->
                <div class="form-group">
                    <label for="whatsapp_number">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}" placeholder="e.g. +861234567890">
                    <small class="text-muted">Shown as a WhatsApp quick-contact icon (wa.me link).</small>
                </div>

                <!-- WeChat QR Code -->
                <div class="form-group">
                    <label for="wechat_qr_image">WeChat QR Code Image</label>
                    <input type="file" name="wechat_qr_image" accept="image/*" class="form-control">
                    @if($settings->wechat_qr_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $settings->wechat_qr_image) }}" alt="WeChat QR" width="120">
                        </div>
                    @endif
                    <small class="text-muted">Upload the WeChat QR code so buyers can scan & chat. Shown in a popover from the quick-contact icons.</small>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $settings->address ?? '') }}">
                </div>

                <!-- Footer Text -->
                <div class="form-group">
                    <label for="footer_text">Footer Text</label>
                    <textarea name="footer_text" class="form-control">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                </div>

                <!-- Customer Login Heading -->
                <div class="form-group">
                    <label for="customer_login_heading">Customer Login Heading</label>
                    <input type="text" name="customer_login_heading" class="form-control" value="{{ old('customer_login_heading', $settings->customer_login_heading ?? '') }}" placeholder='e.g. Sign In (default: "Sign In")'>
                    <small class="text-muted">Heading shown at the top of the customer login page. Leave blank to use the default.</small>
                </div>

                @if ($isSuperAdmin ?? false)
                <!-- Site Status (Super Admin only) -->
                <div class="form-group">
                    <label for="site_status">Site Status</label>
                    <select name="site_status" class="form-select">
                        <option value="active" {{ old('site_status', $settings->site_status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('site_status', $settings->site_status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive (maintenance)</option>
                        <option value="published" {{ old('site_status', $settings->site_status ?? 'active') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="unpublished" {{ old('site_status', $settings->site_status ?? 'active') == 'unpublished' ? 'selected' : '' }}>Unpublished (maintenance)</option>
                        <option value="unlisted" {{ old('site_status', $settings->site_status ?? 'active') == 'unlisted' ? 'selected' : '' }}>Unlisted (live, hidden from search)</option>
                    </select>
                    <small class="text-muted">Only the Super Admin can change this. Inactive/Unpublished shows a maintenance page on the public site.</small>
                </div>
                @endif

                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-success mt-3">Update Settings</button>
            </form>
        </div>
    </div>

@endsection
