<?php

namespace App\Http\Controllers;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * Store or update review
     */
 

public function store(Request $request)
{
    $user = JWTAuth::user();

    $request->validate([
        'artisan_id' => 'required|exists:users,id',
        'rating'     => 'required|integer|min:1|max:5',
        'comment'    => 'nullable|string'
    ]);

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

    //  mettre à jour la note de l'artisan

$artisan = User::find($request->artisan_id);

if ($artisan && $artisan->profile) { 

    $avg = Review::where('artisan_id', $artisan->id)->avg('rating');
    $count = Review::where('artisan_id', $artisan->id)->count();

    $artisan->profile->update([
        'rating' => round($avg, 1),
        'reviews_count' => $count
    ]);
}
    return response()->json([
        'success' => true,
        'review' => $review
    ]);
}

    /**
     * Get artisan reviews
     */
    public function index($artisanId)
    {
        $reviews = Review::with('client')
            ->where('artisan_id', $artisanId)
            ->latest()
            ->get();

        return response()->json([
            'reviews' => $reviews
        ]);
    }
}