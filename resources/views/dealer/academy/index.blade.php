@extends('layouts.dealer')
@section('title', 'Dealer Academy – Dealer Panel')
@section('content')

<style>
    .academy-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);
        padding: 3rem 2rem;
        border-radius: 20px;
        color: #fff;
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(14, 165, 163, 0.3);
    }
    .academy-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .academy-title {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }
    .academy-sub {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
    }
    
    .academy-filters {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .category-tabs {
        display: flex;
        gap: 0.75rem;
        overflow-x: auto;
        padding-bottom: 5px;
    }
    .category-tab {
        padding: 0.6rem 1.25rem;
        background: #fff;
        border: 1px solid var(--gray-200);
        border-radius: 99px;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        text-decoration: none;
        display: inline-block;
    }
    .category-tab:hover {
        border-color: var(--primary-500);
        color: var(--primary-700);
        background: var(--gray-50);
    }
    .category-tab.active {
        background: var(--gray-900) !important;
        border-color: var(--gray-900) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .academy-search-wrap {
        position: relative;
        flex-grow: 1;
        max-width: 400px;
    }
    .academy-search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border-radius: 99px;
        border: 1px solid var(--gray-200);
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .academy-search-input:focus {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 4px rgba(14, 165, 163, 0.1);
        outline: none;
    }
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .academy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.75rem;
    }
    .learning-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .learning-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
        border-color: var(--primary-200);
    }
    .card-thumbnail {
        height: 180px;
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .card-thumbnail::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.4) 100%);
        z-index: 1;
    }
    .type-icon-wrap {
        width: 56px;
        height: 56px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-600);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        z-index: 2;
        transition: transform 0.3s ease;
    }
    .learning-card:hover .type-icon-wrap {
        transform: scale(1.1);
    }
    .card-content {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .card-category {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--primary-600);
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .card-desc {
        font-size: 0.9rem;
        color: var(--gray-500);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1.25rem;
        border-top: 1px solid var(--gray-100);
    }
    .btn-learn {
        background: var(--gray-900);
        color: #fff;
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    .btn-learn:hover {
        background: var(--primary-600);
        transform: translateX(4px);
    }

    /* Type specific colors */
    .type-video { background: #fee2e2; color: #ef4444; }
    .type-pdf { background: #e0f2fe; color: #0ea5e9; }
    .type-link { background: #f0fdf4; color: #22c55e; }
    .type-article { background: #fef3c7; color: #f59e0b; }
</style>

<div class="academy-header">
    <h1 class="academy-title">Dealer Academy</h1>
    <p class="academy-sub">Enhance your knowledge with expert training modules, installation guides, and sales resources designed to grow your business.</p>
</div>

<div class="academy-filters">
    <div class="academy-search-wrap">
        <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <form action="{{ route('dealer.academy.index') }}" method="GET">
            <input name="search" class="academy-search-input" placeholder="Search resources..." value="{{ request('search') }}">
        </form>
    </div>
    
    <div class="category-tabs">
        <a href="{{ route('dealer.academy.index') }}" class="category-tab {{ !request('category') ? 'active' : '' }}">All Modules</a>
        @foreach($categories as $cat)
            <a href="{{ route('dealer.academy.index', ['category' => $cat]) }}" class="category-tab {{ request('category') == $cat ? 'active' : '' }}">{{ $cat }}</a>
        @endforeach
    </div>
</div>

<div class="academy-grid">
    @forelse($items as $it)
    <div class="learning-card">
        <div class="card-thumbnail">
            <div class="type-icon-wrap">
                @if($it->content_type === 'video')
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                @elseif($it->content_type === 'pdf')
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                @elseif($it->content_type === 'link')
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                @else
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                @endif
            </div>
            
            @php
                $bgImage = '';
                if($it->thumbnail_path) {
                    $bgImage = asset('storage/' . $it->thumbnail_path);
                } else {
                    if($it->category == 'Sales Training') $bgImage = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&q=80';
                    elseif($it->category == 'Product Info') $bgImage = 'https://images.unsplash.com/photo-1585338107529-13afc5f02586?w=800&q=80';
                    elseif($it->category == 'Installation') $bgImage = 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800&q=80';
                    elseif($it->category == 'Service') $bgImage = 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800&q=80';
                    else $bgImage = 'https://images.unsplash.com/photo-1434031216660-c50938c8f3ef?w=800&q=80';
                }
            @endphp
            <img src="{{ $bgImage }}" alt="" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
        </div>
        
        <div class="card-content">
            <div class="card-category">{{ $it->category }}</div>
            <h3 class="card-title">{{ $it->title }}</h3>
            <p class="card-desc">{{ Str::limit($it->description, 100) }}</p>
            
            <div class="card-footer">
                <div style="display:flex; flex-direction:column; gap:4px">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--gray-400); display:flex; align-items:center; gap:4px; text-transform: uppercase;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Self-paced
                    </span>
                    <div style="width: 100px; height: 4px; background: var(--gray-100); border-radius: 2px; overflow: hidden;">
                        <div style="width: 0%; height: 100%; background: var(--primary-500);"></div>
                    </div>
                </div>
                
                @if($it->content_type === 'video')
                    @if($it->external_link)
                        <button class="btn-learn" onclick="openVideoModal('{{ $it->external_link }}', '{{ addslashes($it->title) }}')">
                            Watch Now <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    @elseif($it->file_path)
                        <a href="{{ asset('storage/' . $it->file_path) }}" target="_blank" class="btn-learn">
                            Watch Video <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                @elseif($it->content_type === 'pdf')
                    <a href="{{ asset('storage/' . $it->file_path) }}" target="_blank" class="btn-learn">
                        Read PDF <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @elseif($it->content_type === 'link')
                    <a href="{{ $it->external_link }}" target="_blank" class="btn-learn">
                        Visit Link <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <button class="btn-learn" onclick="viewArticle(@json($it))">
                        Read Article <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: span 3; text-align:center; padding:5rem; background: #f9fafb; border-style: dashed;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
        <h3 class="fw-800" style="color:var(--gray-900)">No resources found</h3>
        <p class="text-muted">Try adjusting your search or category filters.</p>
        <a href="{{ route('dealer.academy.index') }}" class="btn btn--primary mt-4">View All Modules</a>
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>

{{-- Video Modal --}}
<div id="academyVideoModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:10000; align-items:center; justify-content:center; padding:20px; backdrop-filter: blur(5px);">
    <div class="card" style="width:100%; max-width:1000px; padding:0; background:transparent; position:relative; box-shadow: none; border:none;">
        <button onclick="closeVideoModal()" style="position:absolute; top:-40px; right:0; background:none; border:none; color:#fff; font-size:40px; cursor:pointer; font-weight: 300;">&times;</button>
        <div style="position:relative; padding-bottom:56.25%; height:0; border-radius:20px; overflow:hidden; background:#000; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
            <iframe id="academyVideoIframe" style="position:absolute; top:0; left:0; width:100%; height:100%;" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3 id="videoTitle" style="color:#fff; margin-top:20px; font-weight:800; font-size:1.5rem; text-align: center;"></h3>
    </div>
</div>

{{-- Article Modal --}}
<div id="academyArticleModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; padding:20px; backdrop-filter: blur(5px);">
    <div class="card" style="width:100%; max-width:800px; max-height:85vh; overflow-y:auto; padding:40px; border-radius: 24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px">
            <h2 id="articleTitle" style="margin:0; font-weight:800; font-size: 1.75rem; color: var(--gray-900); letter-spacing: -0.01em;"></h2>
            <button onclick="closeArticleModal()" style="background:var(--gray-100); border:none; font-size:24px; cursor:pointer; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition: all 0.2s ease;">&times;</button>
        </div>
        <div id="articleContent" class="text-muted" style="line-height:1.8; white-space: pre-wrap; font-size: 1.05rem; color: var(--gray-600);"></div>
        <div class="mt-5 text-right">
            <button class="btn btn--primary" onclick="closeArticleModal()" style="padding: 0.75rem 2rem; border-radius: 12px;">Got it, thanks!</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openVideoModal(url, title) {
    let embedUrl = url;
    if (url.includes('youtube.com/watch?v=')) {
        embedUrl = url.replace('watch?v=', 'embed/');
    } else if (url.includes('vimeo.com/')) {
        embedUrl = url.replace('vimeo.com/', 'player.vimeo.com/video/');
    }
    
    document.getElementById('academyVideoIframe').src = embedUrl;
    document.getElementById('videoTitle').textContent = title;
    document.getElementById('academyVideoModal').style.display = 'flex';
}

function closeVideoModal() {
    document.getElementById('academyVideoIframe').src = '';
    document.getElementById('academyVideoModal').style.display = 'none';
}

function viewArticle(it) {
    document.getElementById('articleTitle').textContent = it.title;
    document.getElementById('articleContent').textContent = it.description;
    document.getElementById('academyArticleModal').style.display = 'flex';
}

function closeArticleModal() {
    document.getElementById('academyArticleModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('academyVideoModal')) closeVideoModal();
    if (event.target == document.getElementById('academyArticleModal')) closeArticleModal();
}
</script>
@endsection
