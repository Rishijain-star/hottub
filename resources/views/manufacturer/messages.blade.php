@extends('layouts.manufacturer')
@section('title', 'Messages – Manufacturer Panel')
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">Messages</h1>
        <p class="panel-page-sub">Private chat with your customers</p>
    </div>
</div>

<div class="card" style="padding: 0; display: flex; height: 600px; overflow: hidden; border-radius: 12px;">
    <!-- Conversations List -->
    <div style="width: 300px; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; background: #f9fafb;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; font-weight: 800; color: var(--gray-900);">Conversations</div>
        <div id="conversations-list" style="flex-grow: 1; overflow-y: auto;">
            <div style="padding: 2rem; text-align: center; color: #9ca3af;">Loading...</div>
        </div>
    </div>

    <!-- Chat Area -->
    <div id="chat-area" style="flex-grow: 1; display: flex; flex-direction: column; background: #fff;">
        <div id="chat-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem; display: none;">
            <div id="chat-header-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 800;"></div>
            <div id="chat-header-name" class="fw-700 text-dark"></div>
        </div>
        
        <div id="messages-container" style="flex-grow: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #9ca3af; text-align: center; padding: 2rem;">
                Select a conversation to start chatting
            </div>
        </div>

        <div id="chat-input-container" style="padding: 1.25rem; border-top: 1px solid #e5e7eb; display: none;">
            <form id="message-form" style="display: flex; gap: 0.75rem;">
                <input type="text" id="message-input" class="form-input" placeholder="Type a message..." required style="border-radius: 24px; padding-left: 1.25rem; padding-right: 1.25rem;">
                <button type="submit" class="btn btn--primary" style="border-radius: 50%; width: 44px; height: 44px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.conversation-item { padding: 1rem 1.5rem; cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f3f4f6; }
.conversation-item:hover { background: #fff; }
.conversation-item.active { background: #fff; border-left: 4px solid var(--primary); }
.msg { max-width: 75%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; position: relative; }
.msg--sent { align-self: flex-end; background: #007bff; color: #fff; border-bottom-right-radius: 2px; }
.msg--received { align-self: flex-start; background: #333; color: #fff; border-bottom-left-radius: 2px; }
.msg-time { font-size: 0.7rem; opacity: 0.7; margin-top: 0.25rem; display: block; }
</style>
@endsection

@section('scripts')
<script>
let currentUserId = null;
let pollInterval = null;

async function loadConversations() {
    const res = await fetch('{{ route("manufacturer.api.conversations") }}');
    const data = await res.json();
    const list = document.getElementById('conversations-list');
    list.innerHTML = '';
    
    if (data.conversations.length === 0) {
        list.innerHTML = '<div style="padding: 2rem; text-align: center; color: #9ca3af;">No conversations yet.</div>';
        return;
    }

    let totalUnread = 0;
    data.conversations.forEach(c => {
        totalUnread += c.unread_count;
        const div = document.createElement('div');
        div.className = `conversation-item ${currentUserId == c.id ? 'active' : ''}`;
        div.onclick = () => selectConversation(c.id, c.name, c.profile_picture);
        
        const avatar = c.profile_picture 
            ? `<img src="/storage/${c.profile_picture}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">`
            : `<div class="letter-avatar" style="width: 40px; height: 40px; font-size: 1rem;">${c.name[0]}</div>`;

        div.innerHTML = `
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                ${avatar}
                <div style="flex-grow: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <div class="fw-700 text-dark text-sm" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${c.name}</div>
                        <div style="font-size: 0.7rem; color: #9ca3af;">${c.last_message_time}</div>
                    </div>
                    <div class="text-xs text-muted" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${c.last_message || 'No messages'}</div>
                </div>
                ${c.unread_count > 0 ? `<div style="background: var(--primary); color: #fff; width: 18px; height: 18px; border-radius: 50%; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;">${c.unread_count}</div>` : ''}
            </div>
        `;
        list.appendChild(div);
    });

    // Update notification dot in sidebar if needed (assuming there's a badge element)
    const badge = document.getElementById('msg-badge');
    if (badge) {
        badge.innerText = totalUnread > 0 ? totalUnread : '';
        badge.style.display = totalUnread > 0 ? 'flex' : 'none';
    }
}

async function selectConversation(id, name, pic) {
    currentUserId = id;
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('chat-input-container').style.display = 'block';
    document.getElementById('chat-header-name').innerText = name;
    
    const avatarHeader = document.getElementById('chat-header-avatar');
    avatarHeader.innerHTML = pic 
        ? `<img src="/storage/${pic}" style="width: 100%; height: 100%; object-fit: cover;">`
        : name[0].toUpperCase();

    loadMessages();
    loadConversations(); // refresh list to show active state

    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(loadMessages, 3000);
}

async function loadMessages() {
    if (!currentUserId) return;
    const res = await fetch(`/manufacturer/api/messages/${currentUserId}`);
    const data = await res.json();
    const container = document.getElementById('messages-container');
    const wasScrolledBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

    container.innerHTML = '';
    data.messages.forEach(m => {
        const isMe = m.sender_id == {{ auth()->id() }};
        const div = document.createElement('div');
        div.className = `msg ${isMe ? 'msg--sent' : 'msg--received'}`;
        div.innerHTML = `
            ${m.content}
            <span class="msg-time">${new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
        `;
        container.appendChild(div);
    });

    if (wasScrolledBottom) {
        container.scrollTop = container.scrollHeight;
    }
}

document.getElementById('message-form').onsubmit = async (e) => {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const content = input.value;
    input.value = '';

    const res = await fetch(`/manufacturer/api/messages/${currentUserId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ content })
    });
    
    if (res.ok) {
        loadMessages();
        loadConversations();
    }
};

loadConversations();
setInterval(loadConversations, 10000);
</script>
@endsection
