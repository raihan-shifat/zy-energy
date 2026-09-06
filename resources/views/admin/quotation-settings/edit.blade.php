@extends('admin.layouts.admin')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="mb-0 dt-heading">Quotation Settings</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.quotation-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Default Notes</label>
                <textarea name="default_notes" class="form-control summernote" rows="6">{!! $defaultNotes !!}</textarea>
                <small class="text-muted">Pre-filled when creating new quotations. Can be overridden per quotation.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Default Remarks</label>
                <textarea name="default_remarks" class="form-control summernote" rows="6">{!! $defaultRemarks !!}</textarea>
                <small class="text-muted">Pre-filled when creating new quotations. Can be overridden per quotation.</small>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });
});
</script>
@endsection
