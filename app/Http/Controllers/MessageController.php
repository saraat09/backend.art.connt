<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Events\NewMessageEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class MessageController extends Controller
{
    /* ═══════════════════════════════
     * GET /api/conversations
     * ═══════════════════════════════ */
    public function conversations(): JsonResponse
    {
        $me = JWTAuth::user()->id;

        $messages = Message::where('sender_id', $me)
            ->orWhere('receiver_id', $me)
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $messages->groupBy(function ($msg) use ($me) {
            return $msg->sender_id == $me
                ? $msg->receiver_id
                : $msg->sender_id;
        });

        $conversations = [];

        foreach ($grouped as $userId => $msgs) {

            $otherUser = User::find($userId);
            if (!$otherUser) continue;

            $last = $msgs->first();

            $conversations[] = [
                'other_user' => [
                    'id'    => $otherUser->id,
                    'name'  => $otherUser->name,
                    'role'  => $otherUser->role,
                    'photo' => $otherUser->photo
                        ? asset('storage/' . $otherUser->photo)
                        : null,
                ],
                'last_message' => [
                    'body'       => $last->type === 'location' ? '📍 Position partagée' : $last->body,
                    'is_mine'    => $last->sender_id == $me,
                    'created_at' => $last->created_at,
                ],
                'unread_count' => $msgs
                    ->where('receiver_id', $me)
                    ->where('read', false)
                    ->count(),
            ];
        }

        return response()->json(array_values($conversations));
    }

    /* ═══════════════════════════════
     * GET /api/conversations/{id}
     * ═══════════════════════════════ */
    public function getMessages($userId)
    {
        $me = JWTAuth::user()->id;

        $messages = Message::where(function ($q) use ($me, $userId) {
                $q->where('sender_id', $me)
                  ->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($me, $userId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $me);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        //  marquer comme lus
        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->update(['read' => true]);

        //  supprimer notifications liées
        Notification::where('user_id', $me)
            ->where('type', 'new_message')
            ->whereJsonContains('data->from_id', (int)$userId)
            ->delete();

        //  format React
        return response()->json(
            $messages->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'type'       => $m->type ?? 'text',
                'lat'        => $m->lat,
                'lng'        => $m->lng,
                'is_mine'    => $m->sender_id === $me,
                'read'       => $m->read,
                'created_at' => $m->created_at,
            ])
        );
    }

    /* ═══════════════════════════════
     * POST /api/messages
     * ═══════════════════════════════ */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'nullable|string',
            'type'        => 'nullable|in:text,location',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        $sender = JWTAuth::user();
        $type   = $request->type ?? 'text';

        // Pour un message de localisation, body est auto-généré
        $body = $type === 'location'
            ? '📍 Position partagée'
            : $request->body;

        if (empty($body)) {
            return response()->json(['error' => 'Le message ne peut pas être vide.'], 422);
        }

        //  créer message
        $message = Message::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $request->receiver_id,
            'body'        => $body,
            'type'        => $type,
            'lat'         => $type === 'location' ? $request->lat : null,
            'lng'         => $type === 'location' ? $request->lng : null,
            'read'        => false,
        ]);

        //  créer notification
        Notification::create([
            'user_id' => $request->receiver_id,
            'type'    => 'new_message',
            'data'    => [
                'from'       => $sender->name,
                'from_id'    => $sender->id,
                'message'    => $type === 'location' ? '📍 Position partagée' : mb_substr($request->body, 0, 80),
                'message_id' => $message->id,
            ],
            'read' => false,
        ]);

        //  realtime (optionnel)
        try {
            event(new NewMessageEvent($message, $sender));
        } catch (\Exception $e) {}

        return response()->json([
            'id'         => $message->id,
            'body'       => $message->body,
            'type'       => $message->type,
            'lat'        => $message->lat,
            'lng'        => $message->lng,
            'is_mine'    => true,
            'read'       => false,
            'created_at' => $message->created_at,
        ], 201);
    }

    /* ═══════════════════════════════
     * PUT /api/messages/{id}/read
     * ═══════════════════════════════ */
    public function markRead($id): JsonResponse
    {
        $me = JWTAuth::user()->id;

        $msg = Message::where('id', $id)
            ->where('receiver_id', $me)
            ->firstOrFail();

        $msg->update(['read' => true]);

        return response()->json(['ok' => true]);
    }
}