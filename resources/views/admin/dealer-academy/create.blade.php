@extends('layouts.admin')
@section('title', 'Add Academy Content – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Add Academy Content</h1>
        <p class="panel-page-sub">Create new training content for dealers</p>
    </div>
    <a href="{{ route('admin.dealer-academy.index') }}" class="btn btn--ghost btn--pill">← Back to List</a>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.dealer-academy.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Title *</label>
            <input name="title" class="form-input" required value="{{ old('title') }}">
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid--2">
            <div class="form-group">
                <label class="form-label">Content Type *</label>
                <select name="content_type" id="contentType" class="form-input" required onchange="toggleInputs()">
                    <option value="">Select Type...</option>
                    @foreach(['video' => 'Video', 'pdf' => 'PDF', 'article' => 'Article', 'link' => 'Link'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('content_type') == $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-input" required>
                    <option value="">Select Category...</option>
                    @foreach(['Sales Training', 'Product Info', 'Installation', 'Service'] as $cat)
                        <option value="{{ $cat }}" @selected(old('category') == $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="fileInputGroup" class="form-group" style="display:none">
            <label class="form-label" id="fileLabel">Upload File</label>
            <input type="file" name="file" class="form-input" accept=".pdf,.mp4,.avi,.mov">
            <p class="text-xs text-muted mt-1">Max 50MB</p>
        </div>

        <div id="linkInputGroup" class="form-group" style="display:none">
            <label class="form-label">External Link</label>
            <input type="url" name="external_link" class="form-input" value="{{ old('external_link') }}" placeholder="https://youtube.com/...">
        </div>

        <div class="modal-actions" style="justify-content:flex-start">
            <button class="btn btn--primary">Create Content</button>
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

    fileGroup.style.display = 'none';
    linkGroup.style.display = 'none';

    if (type === 'pdf') {
        fileGroup.style.display = 'block';
        fileLabel.textContent = 'Upload PDF Document *';
    } else if (type === 'video') {
        fileGroup.style.display = 'block';
        linkGroup.style.display = 'block';
        fileLabel.textContent = 'Upload Video File';
    } else if (type === 'link') {
        linkGroup.style.display = 'block';
    }
}
toggleInputs();
</script>
@endsection
