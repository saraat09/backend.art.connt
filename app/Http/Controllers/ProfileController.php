<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\ArtisanProfile;
use App\Models\ArtisanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    /* ══════════════════════════════════════════
     *  PUT /api/profile
     *  Mise à jour du profil (infos + services)
     * ══════════════════════════════════════════ */
    public function update(Request $request): JsonResponse
    {
        $user = JWTAuth::user();

        $rules = [
            'name'        => 'sometimes|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'city'        => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:1000',
            // Artisan only
            'trade'       => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:1000',
            'location'    => 'sometimes|string|max:150',
            'available'   => 'nullable|boolean',
            'services'    => 'nullable|array|max:10',
            'services.*'  => 'string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mise à jour des champs user
        $userFields = array_filter(
            $request->only(['name', 'phone', 'city', 'bio']),
            fn($v) => !is_null($v)
        );
        if (!empty($userFields)) {
            $user->update($userFields);
        }

        // Mise à jour du profil artisan
        if ($user->role === 'artisan') {
            $profile = $user->artisanProfile;

            if ($profile) {
                $profileFields = array_filter(
                    $request->only(['trade', 'description', 'location']),
                    fn($v) => !is_null($v)
                );

                if ($request->filled('available')) {
                    $profileFields['available'] = (bool) $request->available;
           }

                if (!empty($profileFields)) {
                    $profile->update($profileFields);
                }

                // Remplacer les services si fournis
                if ($request->has('services')) {
                    $profile->services()->delete();

                    foreach ($request->services as $serviceName) {
                        if (trim($serviceName)) {
                            ArtisanService::create([
                                'artisan_profile_id' => $profile->id,
                                'service_name'       => trim($serviceName),
                            ]);
                        }
                    }
                }
            }
        }

        $updated = $user->fresh(['artisanProfile.services']);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $this->formatUser($updated),
        ]);
    }

    /* ══════════════════════════════════════════
     *  POST /api/profile/photo
     *  Upload de la photo de profil
     * ══════════════════════════════════════════ */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photo.image'  => 'Le fichier doit être une image.',
            'photo.mimes'  => 'Formats acceptés : JPG, PNG, WebP.',
            'photo.max'    => 'La photo ne doit pas dépasser 4 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = JWTAuth::user();

        // Supprimer l'ancienne photo si elle existe
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // Stocker dans storage/app/public/photos/
        $path = $request->file('photo')->store('photos', 'public');

        $user->update(['photo' => $path]);

        return response()->json([
            'message'   => 'Photo mise à jour.',
            'photo_url' => asset('storage/' . $path),
        ]);
    }

    /* ── Formateur ──────────────────────────── */
    private function formatUser($user): array
    {
        $data = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
            'phone' => $user->phone,
            'city'  => $user->city,
            'bio'   => $user->bio,
            'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
        ];

        if ($user->role === 'artisan' && $user->artisanProfile) {
            $p = $user->artisanProfile;
            $data['profile'] = [
                'id'            => $p->id,
                'trade'         => $p->trade,
                'description'   => $p->description,
                'location'      => $p->location,
                'available'     => $p->available,
                'rating'        => $p->rating,
                'reviews_count' => $p->reviews_count,
                'services'      => $p->services->map(fn($s) => [
                    'id'         => $s->id,
                    'name'       => $s->service_name,
                    'price_from' => $s->price_from,
                ])->values(),
            ];
        }

        return $data;
    }
}
