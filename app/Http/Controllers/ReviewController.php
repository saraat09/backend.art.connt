<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * GET /api/artisans/{id}/reviews
     * Récupère les avis d'un artisan avec réponses
     */
    public function index($artisanId)
    {
        $reviews = Review::with(['client', 'reply.artisan'])
            ->where('artisan_id', $artisanId)
            ->latest()
            ->get();

        return response()->json([
            'reviews' => $reviews->map(fn($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'created_at' => $r->created_at,
                'client'     => [
                    'id'    => $r->client?->id,
                    'name'  => $r->client?->name,
                    'photo' => $r->client?->photo
                        ? asset('storage/' . $r->client->photo)
                        : null,
                ],
                'reply' => $r->reply ? [
                    'id'         => $r->reply->id,
                    'body'       => $r->reply->body,
                    'created_at' => $r->reply->created_at,
                    'artisan'    => [
                        'name'  => $r->reply->artisan?->name,
                        'photo' => $r->reply->artisan?->photo
                            ? asset('storage/' . $r->reply->artisan->photo)
                            : null,
                    ],
                ] : null,
            ])
        ]);
    }

    /**
     * POST /api/reviews
     * Store ou update un avis — NÉCESSITE une conversation préalable
     */
    public function store(Request $request)
    {
        $user = JWTAuth::user();

        $request->validate([
            'artisan_id' => 'required|exists:users,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        // ── Contrainte : conversation obligatoire ─────────────────────────
        // Un client ne peut noter un artisan que s\'il existe au moins un message
        // entre les deux utilisateurs (dans messages).
        $hasConversation = Message::where(function ($q) use ($user, $request) {
            $q->where('sender_id', $user->id)
              ->where('receiver_id', $request->artisan_id);
        })->orWhere(function ($q) use ($user, $request) {
            $q->where('sender_id', $request->artisan_id)
              ->where('receiver_id', $user->id);
        })->exists();

        if (!$hasConversation) {
            return response()->json([
                'error' => 'Vous devez d\'abord avoir une conversation avec cet artisan avant de laisser un avis.'
            ], 403);
        }





        // ── Un client ne peut pas noter son propre profil ─────────────────
        if ($user->id == $request->artisan_id) {
            return response()->json(['error' => 'Vous ne pouvez pas noter votre propre profil.'], 403);
        }

        $review = Review::updateOrCreate(
            [
                'client_id'  => $user->id,
                'artisan_id' => $request->artisan_id,
            ],
            [
                'rating'  => $request->rating,
                'comment' => $request->comment ?? null,
            ]
        );

        // Mettre à jour la note de l'artisan
        $artisan = User::find($request->artisan_id);

        if ($artisan && $artisan->profile) {
            $avg   = Review::where('artisan_id', $artisan->id)->avg('rating');
            $count = Review::where('artisan_id', $artisan->id)->count();

            $artisan->profile->update([
                'rating'        => round($avg, 1),
                'reviews_count' => $count,
            ]);
        }

        return response()->json([
            'success' => true,
            'review'  => $review,
        ]);
    }

    /**
     * POST /api/reviews/{id}/reply
     * L'artisan répond à un avis le concernant
     */
    public function reply(Request $request, $reviewId)
    {
        $artisan = JWTAuth::user();

        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $review = Review::findOrFail($reviewId);

        // Seul l'artisan concerné peut répondre
        if ($review->artisan_id !== $artisan->id) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $reply = ReviewReply::updateOrCreate(
            ['review_id' => $review->id],
            [
                'artisan_id' => $artisan->id,
                'body'       => $request->body,
            ]
        );

        return response()->json([
            'success' => true,
            'reply'   => [
                'id'         => $reply->id,
                'body'       => $reply->body,
                'created_at' => $reply->created_at,
                'artisan'    => [
                    'name'  => $artisan->name,
                    'photo' => $artisan->photo
                        ? asset('storage/' . $artisan->photo)
                        : null,
                ],
            ],
        ]);
    }

    /**
     * GET /api/reviews/can-review/{artisanId}
     * Vérifie si l'utilisateur connecté peut noter cet artisan
     */
    public function canReview($artisanId)
    {
        $user = JWTAuth::user();

        $hasConversation = Message::where(function ($q) use ($user, $artisanId) {
            $q->where('sender_id', $user->id)
              ->where('receiver_id', $artisanId);
        })->orWhere(function ($q) use ($user, $artisanId) {
            $q->where('sender_id', $artisanId)
              ->where('receiver_id', $user->id);
        })->exists();


        $existingReview = Review::where('client_id', $user->id)
            ->where('artisan_id', $artisanId)
            ->first();

        return response()->json([
            'can_review'      => $hasConversation,
            'existing_review' => $existingReview ? [
                'rating'  => $existingReview->rating,
                'comment' => $existingReview->comment,
            ] : null,
        ]);
    }
}