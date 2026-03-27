<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MessageController extends Controller
{
    public function getConversations()
    {
        $user = Auth::user();
        
        // Get unique users the current user has exchanged messages with
        $userIds = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get()
            ->map(function ($m) use ($user) {
                return $m->sender_id == $user->id ? $m->receiver_id : $m->sender_id;
            })
            ->filter() // Remove nulls if any
            ->unique();

        $conversations = User::whereIn('id', $userIds)
            ->get()
            ->map(function ($u) use ($user) {
                $lastMessage = Message::where(function ($q) use ($user, $u) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $u->id);
                    })
                    ->orWhere(function ($q) use ($user, $u) {
                        $q->where('sender_id', $u->id)->where('receiver_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'profile_picture' => $u->profile_picture,
                    'last_message' => $lastMessage ? $lastMessage->content : '',
                    'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : '',
                    'unread_count' => Message::where('sender_id', $u->id)
                        ->where('receiver_id', $user->id)
                        ->whereNull('read_at')
                        ->count(),
                ];
            });

        return response()->json(['conversations' => $conversations]);
    }

    public function getMessages(User $user)
    {
        $me = Auth::user();
        
        $messages = Message::where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(Request $request, User $user)
    {
        $me = Auth::user();
        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $isSupportRequest = ((int) $user->id === 1);
        $hasSupportStatusColumn = Schema::hasColumn('messages', 'support_status');
        if ($isSupportRequest) {
            $hasPendingSupportRequest = false;
            if ($hasSupportStatusColumn) {
                $hasPendingSupportRequest = Message::where('sender_id', $me->id)
                    ->where('receiver_id', 1)
                    ->where('support_status', 'pending')
                    ->exists();
            }

            if ($hasPendingSupportRequest) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Your request is already submitted and awaiting admin review.',
                ], 422);
            }
        }

        $payload = [
            'sender_id' => $me->id,
            'receiver_id' => $user->id,
            'content' => $data['content'],
        ];
        if ($isSupportRequest && $hasSupportStatusColumn) {
            $payload['support_status'] = 'pending';
        }

        $message = Message::create($payload);

        return response()->json([
            'message' => $message,
            'msg' => 'Your request has been submitted successfully.',
        ]);
    }
}
