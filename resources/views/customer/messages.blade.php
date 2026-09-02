@extends('layouts.customer')
@section('title', __('panel.messages.title').' – '.__('panel.customer_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.messages.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.customer_panel.sub_customer') }}</p>
    </div>
</div>

<div class="card" style="padding: 0; display: flex; height: 600px; overflow: hidden; border-radius: 12px;">
    <div style="width: 300px; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; background: #f9fafb;">
        <div class="chat-sidebar-head">{{ __('panel.messages.inbox') }}</div>
        <div class="chat-sidebar-toolbar">
            <input type="search" id="chat-user-search" class="form-input chat-search-input" placeholder="{{ __('panel.messages.search_name') }}" autocomplete="off" aria-label="{{ __('panel.messages.search_name_aria') }}">
            <div class="chat-filter-row">
                <button type="button" id="chat-filter-all" class="chat-filter-btn is-active" aria-pressed="true">{{ __('panel.messages.all') }}</button>
                <button type="button" id="chat-filter-unread" class="chat-filter-btn" aria-pressed="false">{{ __('panel.messages.unread') }}</button>
            </div>
        </div>
        <div id="conversations-list" style="flex-grow: 1; overflow-y: auto;">
            <div class="chat-conv-empty">{{ __('panel.messages.loading') }}</div>
        </div>
    </div>

    <div id="chat-area" style="flex-grow: 1; display: flex; flex-direction: column; background: #fff;">
        <div id="chat-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem; display: none;">
            <div id="chat-header-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 800;"></div>
            <div id="chat-header-name" class="fw-700 text-dark"></div>
        </div>
        
        <div id="messages-container" style="flex-grow: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #9ca3af; text-align: center; padding: 2rem;">
                {{ __('panel.messages.select_message_to_chat') }}
            </div>
        </div>

        <div id="chat-input-container" style="padding: 1.25rem; border-top: 1px solid #e5e7eb; display: none;">
            <div id="image-preview-wrap" style="display:none;margin-bottom:0.5rem;padding:0.5rem;border:1px dashed #cbd5e1;border-radius:8px;position:relative;">
                <img id="image-preview-img" src="" alt="" style="max-height:120px;max-width:100%;border-radius:6px;display:block;">
                <button type="button" id="image-preview-clear" class="btn btn--ghost btn--sm" style="margin-top:0.35rem;">{{ __('panel.messages.remove_image') }}</button>
            </div>
            <form id="message-form" style="display: flex; gap: 0.75rem; align-items:center;">
                <button type="button" id="btn-attach" class="btn btn--ghost" aria-label="Attach image" title="Attach image" style="border-radius:50%;width:44px;height:44px;padding:0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-8.49 8.49a5.5 5.5 0 0 1-7.78-7.78l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95l-9.2 9.19a1.5 1.5 0 1 1-2.12-2.12l8.14-8.13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <input type="file" id="chat-image-input" accept="image/*" style="display:none;">
                <input type="text" id="message-input" class="form-input" placeholder="{{ __('panel.messages.type_message') }}" style="border-radius: 24px; padding-left: 1.25rem; padding-right: 1.25rem; flex: 1;">
                <button type="submit" class="btn btn--primary" style="border-radius: 50%; width: 44px; height: 44px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.msg { max-width: 75%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; position: relative; }
.msg--sent { align-self: flex-end; background: #007bff; color: #fff; border-bottom-right-radius: 2px; }
.msg--received { align-self: flex-start; background: #333; color: #fff; border-bottom-left-radius: 2px; }
.msg-time { font-size: 0.7rem; opacity: 0.7; margin-top: 0.25rem; display: block; }
</style>
@endsection

@php
    $messagesPanelI18n = [
        'noMessages' => __('panel.messages.no_messages'),
        'loading' => __('panel.messages.loading'),
        'noUnread' => __('panel.messages.no_unread_conversations'),
        'noSearch' => __('panel.messages.no_search_match'),
        'noConversations' => __('panel.messages.no_conversations'),
        'sendFailed' => __('panel.messages.send_failed'),
    ];
@endphp

@section('scripts')
<script>
const panelI18n = @json($messagesPanelI18n);

let currentUserId = null;
let pollInterval = null;
let allConversations = [];
let chatSearchQuery = '';
let chatUnreadOnly = false;

document.getElementById('btn-attach').addEventListener('click', function () {
    document.getElementById('chat-image-input').click();
});

document.getElementById('chat-image-input').addEventListener('change', function (e) {
    const f = e.target.files && e.target.files[0];
    if (!f || !f.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        const wrap = document.getElementById('image-preview-wrap');
        const img = document.getElementById('image-preview-img');
        img.src = ev.target.result;
        wrap.style.display = '';
    };
    reader.readAsDataURL(f);
});

document.getElementById('image-preview-clear').addEventListener('click', function () {
    document.getElementById('chat-image-input').value = '';
    document.getElementById('image-preview-wrap').style.display = 'none';
    document.getElementById('image-preview-img').src = '';
});

document.getElementById('chat-user-search').addEventListener('input', function (e) {
    chatSearchQuery = (e.target.value || '');
    renderConversationList();
});

document.getElementById('chat-filter-all').addEventListener('click', function () {
    chatUnreadOnly = false;
    document.getElementById('chat-filter-all').classList.add('is-active');
    document.getElementById('chat-filter-unread').classList.remove('is-active');
    document.getElementById('chat-filter-all').setAttribute('aria-pressed', 'true');
    document.getElementById('chat-filter-unread').setAttribute('aria-pressed', 'false');
    renderConversationList();
});

document.getElementById('chat-filter-unread').addEventListener('click', function () {
    chatUnreadOnly = true;
    document.getElementById('chat-filter-unread').classList.add('is-active');
    document.getElementById('chat-filter-all').classList.remove('is-active');
    document.getElementById('chat-filter-unread').setAttribute('aria-pressed', 'true');
    document.getElementById('chat-filter-all').setAttribute('aria-pressed', 'false');
    renderConversationList();
});

function sortConversationsForDisplay(list) {
    return list.slice().sort(function (a, b) {
        const ua = (a.unread_count || 0) > 0 ? 1 : 0;
        const ub = (b.unread_count || 0) > 0 ? 1 : 0;
        if (ua !== ub) {
            return ub - ua;
        }
        const ta = Number(a.last_message_at) || 0;
        const tb = Number(b.last_message_at) || 0;
        return tb - ta;
    });
}

function getFilteredConversations() {
    let list = (allConversations || []).slice();
    const q = chatSearchQuery.trim().toLowerCase();
    if (q) {
        list = list.filter(function (c) { return (c.name || '').toLowerCase().indexOf(q) !== -1; });
    }
    if (chatUnreadOnly) {
        list = list.filter(function (c) { return (c.unread_count || 0) > 0; });
    }
    return sortConversationsForDisplay(list);
}

function updateNavUnreadBadge() {
    let totalUnread = 0;
    (allConversations || []).forEach(function (c) { totalUnread += (c.unread_count || 0); });
    const badge = document.getElementById('messages-nav-unread-badge');
    if (!badge) return;
    if (totalUnread > 0) {
        badge.textContent = String(totalUnread);
        badge.style.display = '';
        badge.style.marginLeft = 'auto';
    } else {
        badge.textContent = '';
        badge.style.display = 'none';
    }
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text == null ? '' : String(text);
    return d.innerHTML;
}

function renderConversationList() {
    const listEl = document.getElementById('conversations-list');
    const filtered = getFilteredConversations();
    listEl.innerHTML = '';

    updateNavUnreadBadge();

    if (!allConversations || allConversations.length === 0) {
        listEl.innerHTML = '<div class="chat-conv-empty">' + panelI18n.noConversations + '</div>';
        return;
    }
    if (filtered.length === 0) {
        const msg = chatUnreadOnly
            ? panelI18n.noUnread
            : (chatSearchQuery.trim() ? panelI18n.noSearch : panelI18n.noConversations);
        listEl.innerHTML = '<div class="chat-conv-empty">' + escapeHtml(msg) + '</div>';
        return;
    }

    filtered.forEach(function (c) {
        const div = document.createElement('div');
        const hasUnread = (c.unread_count || 0) > 0;
        div.className = 'conversation-item--chat ' + (currentUserId == c.id ? 'active' : '') + (hasUnread ? ' conversation-item--chat-unread' : '');
        div.onclick = function () { selectConversation(c.id, c.name, c.profile_picture); };

        const avatar = c.profile_picture
            ? '<img src="/uploads/app/public/' + escapeHtml(String(c.profile_picture).replace(/^\/+/, '').replace(/^storage\/app\/public\//i, '').replace(/^storage\//i, '')) + '" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">'
            : '<div class="letter-avatar" style="width: 40px; height: 40px; font-size: 1rem;">' + escapeHtml((c.name || '?')[0]) + '</div>';

        const n = c.unread_count || 0;
        const badgeLg = n > 9 ? ' chat-conv-unread-badge--lg' : '';
        const unread = hasUnread
            ? '<span class="chat-conv-unread-badge' + badgeLg + '" aria-label="' + n + ' unread">' + n + '</span>'
            : '';

        const lastPreview = (c.last_message && String(c.last_message).trim()) ? c.last_message : panelI18n.noMessages;
        const lastTime = c.last_message_time || '';

        const nameCls = 'chat-conv-name text-sm text-dark ' + (hasUnread ? 'chat-conv-name--unread' : '');
        const prevCls = 'chat-conv-preview ' + (hasUnread ? 'chat-conv-preview--unread' : '');

        div.innerHTML =
            '<div style="display: flex; gap: 0.75rem; align-items: flex-start;">' +
                avatar +
                '<div style="flex-grow: 1; min-width: 0;">' +
                    '<div class="chat-conv-row-top">' +
                        '<div class="' + nameCls + '">' + escapeHtml(c.name || '') + '</div>' +
                        '<div class="chat-conv-top-right">' + (hasUnread ? unread : '') + '</div>' +
                    '</div>' +
                    '<div class="' + prevCls + '" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px;">' + escapeHtml(lastPreview) + '</div>' +
                    (lastTime ? '<div class="chat-conv-row-time">' + escapeHtml(lastTime) + '</div>' : '') +
                '</div>' +
            '</div>';
        listEl.appendChild(div);
    });
}

async function loadConversations() {
    const res = await fetch('{{ route("customer.api.conversations") }}');
    const data = await res.json();
    allConversations = data.conversations || [];
    renderConversationList();
}

async function selectConversation(id, name, pic) {
    currentUserId = id;
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('chat-input-container').style.display = 'block';
    document.getElementById('chat-header-name').innerText = name;

    const avatarHeader = document.getElementById('chat-header-avatar');
    avatarHeader.innerHTML = pic
        ? '<img src="/uploads/app/public/' + String(pic).replace(/"/g, '').replace(/^\/+/, '').replace(/^storage\/app\/public\//i, '').replace(/^storage\//i, '') + '" style="width: 100%; height: 100%; object-fit: cover;" alt="">'
        : (name && name[0] ? name[0].toUpperCase() : '?');

    await loadMessages();
    await loadConversations();

    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(loadMessages, 3000);
}

async function loadMessages() {
    if (!currentUserId) return;
    const res = await fetch('/customer/api/messages/' + currentUserId);
    const data = await res.json();
    const container = document.getElementById('messages-container');
    const wasScrolledBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

    container.innerHTML = '';
    data.messages.forEach(function (m) {
        const isMe = m.sender_id == {{ auth()->id() }};
        const div = document.createElement('div');
        div.className = 'msg ' + (isMe ? 'msg--sent' : 'msg--received');
        const text = (m.content || '').trim();
        let inner = text ? escapeHtml(text).replace(/\n/g, '<br>') : '';
        if (m.attachment_url) {
            inner += (inner ? '<br>' : '') + '<img src="' + escapeHtml(m.attachment_url) + '" alt="" style="max-width:100%;max-height:200px;border-radius:8px;margin-top:6px;display:block;">';
        }
        div.innerHTML =
            inner +
            '<span class="msg-time">' + new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + '</span>';
        container.appendChild(div);
    });

    if (wasScrolledBottom) {
        container.scrollTop = container.scrollHeight;
    }
}

document.getElementById('message-form').onsubmit = async function (e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const content = input.value;
    const fileInput = document.getElementById('chat-image-input');
    const file = fileInput.files && fileInput.files[0];
    if (!content.trim() && !file) {
        return;
    }
    input.value = '';
    fileInput.value = '';
    document.getElementById('image-preview-wrap').style.display = 'none';
    document.getElementById('image-preview-img').src = '';

    const fd = new FormData();
    fd.append('content', content);
    if (file) fd.append('image', file);

    const res = await fetch('/customer/api/messages/' + currentUserId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: fd
    });

    const data = await res.json().catch(function () { return {}; });
    if (res.ok) {
        await loadMessages();
        await loadConversations();
    } else {
        alert(data.msg || data.error || panelI18n.sendFailed);
    }
};

loadConversations();
setInterval(loadConversations, 10000);
</script>
@endsection
