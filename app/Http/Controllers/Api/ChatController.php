<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ChatController extends Controller
{

    public function getConversations()
    {
        $userId = auth()->id();

        $chats = Chat::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        })
            ->latest('created_at')
            ->get()
            ->groupBy(function ($chat) use ($userId) {

                return $chat->sender_id == $userId ? $chat->receiver_id : $chat->sender_id;
            })
            ->map(function ($chatGroup) use ($userId) {
                $latestChat = $chatGroup->first();

                $otherUserId = ($latestChat->sender_id == $userId) ? $latestChat->receiver_id : $latestChat->sender_id;
                $otherUser = User::find($otherUserId);

                $unreadCount = Chat::where('sender_id', $otherUserId)
                    ->where('receiver_id', $userId)
                    ->where('status', 'sent')
                    ->count();

                return [
                    'chat_id' => $latestChat->id,
                    'user_id' => $otherUser->id,
                    'name' => $otherUser->first_name . ' ' . $otherUser->last_name,
                    'profile_image' => $otherUser->profile_image ?? 'default_avatar.png',
                    'last_message' => $latestChat->message,
                    'status' => $latestChat->status,
                    'unread_count' => $unreadCount,
                    'updated_at' => $latestChat->updated_at->diffForHumans(),
                ];
            })
            ->values(); // Reset the collection keys

        return response()->json($chats);
    }



    public function sendMessage(Request $request)
    {
        $chat = Chat::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'status' => 'sent'
        ]);

        return response()->json(['success' => true, 'message' => 'Message sent!', 'data' => $chat]);
    }

    public function getChatMessages($userId)
    {
        $loggedInUserId = auth()->id();

        $messages = Chat::where(function ($query) use ($loggedInUserId, $userId) {
            $query->where('sender_id', $loggedInUserId)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($loggedInUserId, $userId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $loggedInUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $loggedInUserId)
            ->where('status', 'sent')
            ->update(['status' => 'delivered']);

        return response()->json($messages);
    }

    public function markAsRead($userId)
    {
        $loggedInUserId = auth()->id();

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $loggedInUserId)
            ->where('status', 'delivered')
            ->update(['status' => 'read']);

        return response()->json(['message' => 'Messages marked as read']);
    }
}
