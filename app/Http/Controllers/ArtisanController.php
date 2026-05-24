<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ArtisanProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class ArtisanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'artisan')
                     ->with(['artisanProfile.services', 'artisanProfile.reviews']);

        // Filtres
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('trade')) {
            $query->whereHas('artisanProfile', function ($q) use ($request) {
                $q->where('trade', 'like', '%' . $request->trade . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('artisanProfile', function ($sub) use ($request) {
                    $sub->where('location', 'like', '%' . $request->location . '%');
                })
                ->orWhere('city', 'like', '%' . $request->location . '%');
            });
        }

        // Filtre disponibilité
        if ($request->filled('available')) {
            $query->whereHas('artisanProfile', function ($q) use ($request) {
                $q->where('available', (bool) $request->available);
            });
        }

        // Pagination
        $perPage  = min((int) $request->get('per_page', 12), 50);
        $artisans = $query->paginate($perPage);

        // Transformer pour ajouter rating calculé depuis reviews
        $artisans->getCollection()->transform(fn($user) => $this->formatArtisan($user));

        // Ordonner par rating moyen décroissant
        $sorted = $artisans->getCollection()->sortByDesc(fn($a) => $a['user_rating']);
        $artisans->setCollection($sorted->values());

        return response()->json($artisans);
    }

    public function show($id): JsonResponse
    {
        $user = User::where('role', 'artisan')
                    ->with(['artisanProfile.services', 'artisanProfile.reviews'])
                    ->findOrFail($id);

        return response()->json($this->formatArtisan($user));
    }

    /**
     * PATCH /api/artisans/availability
     * Bascule la disponibilité de l'artisan connecté
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $user = JWTAuth::user();

        if ($user->role !== 'artisan') {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $profile = $user->artisanProfile;

        if (!$profile) {
            return response()->json(['error' => 'Profil artisan introuvable.'], 404);
        }

        $newValue = !$profile->available;
        $profile->update(['available' => $newValue]);

        return response()->json([
            'available' => $newValue,
            'message'   => $newValue
                ? 'Vous êtes maintenant disponible.'
                : 'Vous êtes maintenant marqué comme occupé.',
        ]);
    }

    private function formatArtisan(User $user): array
    {
        $profile = $user->artisanProfile;

        $averageRating = $profile?->reviews->avg('rating') ?? 0;
        $reviewsCount  = $profile?->reviews->count() ?? 0;

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'city'          => $user->city,
            'photo'         => $user->photo ? asset('storage/' . $user->photo) : null,
            'user_rating'   => round($averageRating, 1),
            'reviews_count' => $reviewsCount,
            'profile'       => $profile ? [
                'id'            => $profile->id,
                'trade'         => $profile->trade,
                'description'   => $profile->description,
                'location'      => $profile->location,
                'available'     => (bool) $profile->available,
                'rating'        => round($averageRating, 1),
                'reviews_count' => $reviewsCount,
                'services'      => $profile->services->map(fn($s) => [
                    'id'         => $s->id,
                    'name'       => $s->service_name,
                    'price_from' => $s->price_from,
                ])->values(),
            ] : null,
        ];
    }
}