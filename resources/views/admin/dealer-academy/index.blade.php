@extends('layouts.admin')
@section('title', 'Dealer Academy Management – Admin Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Dealer Academy</h1>
        <p class="panel-page-sub">Manage training content for dealers</p>
    </div>
    <a href="{{ route('admin.dealer-academy.create') }}" class="btn btn--primary btn--pill">+ Add Content</a>
</div>

{{-- ─── FILTERS ─────────────────────────────────────────────────── --}}
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.dealer-academy.index') }}" class="grid grid--4" style="align-items: flex-end; gap: 1rem;">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Title..." value="{{ request('search') }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Type</label>
            <select name="content_type" class="form-input">
                <option value="">All Types</option>
                <option value="video" {{ request('content_type') === 'video' ? 'selected' : '' }}>Video</option>
                <option value="pdf" {{ request('content_type') === 'pdf' ? 'selected' : '' }}>PDF</option>
                <option value="article" {{ request('content_type') === 'article' ? 'selected' : '' }}>Article</option>
                <option value="link" {{ request('content_type') === 'link' ? 'selected' : '' }}>Link</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-input" placeholder="Category..." value="{{ request('category') }}">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn--primary" style="flex: 1;">Filter</button>
            <a href="{{ route('admin.dealer-academy.index') }}" class="btn btn--ghost">Clear</a>
        </div>
    </form>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

<div class="card" style="padding:0;">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Type</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            <tr>
                <td>
                    <div class="fw-700 text-dark">{{ $it->title }}</div>
                    <div class="text-sm text-muted">{{ Str::limit($it->description, 50) }}</div>
                </td>
                <td><span class="badge">{{ $it->category }}</span></td>
                <td><span class="badge badge--primary">{{ ucfirst($it->content_type) }}</span></td>
                <td>{{ $it->created_at->format('d M Y') }}</td>
                <td>
                    <div style="display:flex;gap:8px">
                        <a href="{{ route('admin.dealer-academy.edit', $it->id) }}" class="btn btn--ghost btn--sm">Edit</a>
                        <form action="{{ route('admin.dealer-academy.destroy', $it->id) }}" method="POST" onsubmit="return confirm('Delete this content?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn--ghost btn--sm" style="color:#ef4444">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No academy content found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem">
        {{ $items->links('components.pagination') }}
    </div>
</div>
@endsection
