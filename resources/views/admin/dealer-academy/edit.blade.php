@extends('layouts.admin')
@section('title', __('panel.admin.pages.dealer_academy_edit.title') . ' – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.pages.dealer_academy_edit.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.pages.dealer_academy_edit.sub') }}</p>
    </div>
    <a href="{{ route('admin.dealer-academy.index') }}" class="btn btn--ghost btn--pill">{{ __('panel.admin.pages.dealer_academy_edit.back_to_list') }}</a>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.dealer-academy.update', $item->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        
        <div class="form-group">
            <label class="form-label">Title *</label>
            <input name="title" class="form-input" required value="{{ old('title', $item->title) }}">
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Content Type *</label>
                <select name="content_type" id="contentType" class="form-input" required onchange="toggleInputs()">
                    <option value="">Select Type...</option>
                    @foreach(['video' => 'Video', 'pdf' => 'PDF', 'article' => 'Article', 'link' => 'Link'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('content_type', $item->content_type) == $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-input" required>
                    <option value="">Select Category...</option>
                    @foreach(['Sales Training', 'Product Info', 'Installation', 'Service'] as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $item->category) == $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Thumbnail Image</label>
            @if($item->thumbnail_path)
                <div class="mb-2">
                    <img src="{{ \App\Support\PublicMedia::url($item->thumbnail_path) }}" style="width:120px; height:80px; object-fit:cover; border-radius:8px; border:1px solid var(--gray-200)">
                </div>
            @endif
            <input type="file" name="thumbnail" class="form-input" accept="image/*">
            <p class="text-xs text-muted mt-1">Leave empty to keep current thumbnail. Max 10MB.</p>
        </div>

        <div id="fileInputGroup" class="form-group" style="display:none">
            <label class="form-label" id="fileLabel">Upload File</label>
            @if($item->file_path)
                <div class="mb-2 text-sm">
                    Current: <a href="{{ \App\Support\PublicMedia::url($item->file_path) }}" target="_blank" class="text-primary-600 fw-700">View Current File</a>
                </div>
            @endif
            <input type="file" name="file" class="form-input" accept=".pdf,.mp4,.avi,.mov">
            <p class="text-xs text-muted mt-1">Leave empty to keep current file. Max 50MB</p>
        </div>

        <div id="linkInputGroup" class="form-group" style="display:none">
            <label class="form-label">External Link</label>
            <input type="url" name="external_link" class="form-input" value="{{ old('external_link', $item->external_link) }}" placeholder="https://youtube.com/...">
        </div>

        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary">Update Content</button>
            <a href="{{ route('admin.dealer-academy.index') }}" class="btn btn--ghost">Cancel</a>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function toggleInputs() {
    const type = document.getElementById('contentType').value;
    const fileGroup = document.getElementById('fileInputGroup');
    const linkGroup = document.getElementById('linkInputGroup');
    const fileLabel = document.getElementById('fileLabel');

    if (!fileGroup || !linkGroup || !fileLabel) return;

    fileGroup.style.display = 'none';
    linkGroup.style.display = 'none';

    if (type === 'pdf') {
        fileGroup.style.display = 'block';
        fileLabel.textContent = 'Upload PDF Document';
    } else if (type === 'video') {
        fileGroup.style.display = 'block';
        fileLabel.textContent = 'Upload Video File';
    } else if (type === 'link') {
        linkGroup.style.display = 'block';
    }
}
toggleInputs();
</script>
@endsection
