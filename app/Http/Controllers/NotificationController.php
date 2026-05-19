<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{
    /* ═══════════════════════════════
     * GET /api/notifications
     * ═══════════════════════════════ */
    public function index(): JsonResponse
    {
        $userId = JWTAuth::user()->id;

        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'read' => $n->read,
                'created_at' => $n->created_at,
            ]);

        $unreadCount = Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /* ═══════════════════════════════
     * PUT /api/notifications/{id}/read
     * ═══════════════════════════════ */
  public function markAsRead($id, Request $request)
{
    $notif = Notification::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->first();

    if (!$notif) {
        return response()->json(['message' => 'not found'], 404);
    }

    //  SUPPRESSION DIRECTE (GARANTI)
    $notif->delete();

    return response()->json(['success' => true]);
}

    /* ═══════════════════════════════
     * PUT /api/notifications/read-all
     * ═══════════════════════════════ */
    public function markAllRead(): JsonResponse
    {
        $userId = JWTAuth::user()->id;

        Notification::where('user_id', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Toutes les notifications sont lues'
        ]);
    }
}