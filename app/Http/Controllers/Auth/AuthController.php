<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ArtisanProfile;
use App\Models\ArtisanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:artisan,client',
            'phone'    => 'nullable|string|max:20',
            'city'     => 'nullable|string|max:100',

            // artisan
            'trade'     => 'required_if:role,artisan|nullable|string|max:100',
            'location'  => 'required_if:role,artisan|nullable|string|max:150',
            'description' => 'nullable|string|max:1000',

            'services'  => 'nullable|array',
            'services.*'=> 'string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors'  => $validator->errors(),
                'debug'   => $request->all()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'phone'    => $request->phone,
                'city'     => $request->city,
            ]);

            if ($request->role === 'artisan') {
                $profile = ArtisanProfile::create([
                    'user_id'     => $user->id,
                    'trade'       => $request->trade,
                    'description' => $request->description,
                    'location'    => $request->location,
                    'available'   => true,
                ]);

                if (!empty($request->services) && is_array($request->services)) {
                    foreach ($request->services as $service) {
                        ArtisanService::create([
                            'artisan_profile_id' => $profile->id,
                            'service_name'       => $service,
                        ]);
                    }
                }
            }

            $token = JWTAuth::fromUser($user);
            DB::commit();

            return response()->json([
                'message' => 'Inscription réussie',
                'token'   => $token,
                'user'    => $user->load('artisanProfile.services')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur lors de l’inscription.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Erreur lors de la génération du token.'], 500);
        }

        return response()->json([
            'token' => $token,
            'user'  => $user->load('artisanProfile.services')
        ]);
    }
}