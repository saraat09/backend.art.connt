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

       try {

    // CREATE USER
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => $request->role,
        'phone'    => $request->phone,
        'city'     => $request->city,
    ]);

} catch (\Exception $e) {
    return response()->json([
        'error' => 'ERREUR USER',
        'message' => $e->getMessage()
    ], 500);
}
        // ARTISAN PROFILE
       try {

    $profile = ArtisanProfile::create([
        'user_id'     => $user->id,
        'trade'       => $request->trade,
        'description' => $request->description,
        'location'    => $request->location,
        'available'   => true,
    ]);

} catch (\Exception $e) {
    return response()->json([
        'error' => 'ERREUR PROFILE',
        'message' => $e->getMessage()
    ], 500);
}
           try {

    if (!empty($request->services)) {
        foreach ($request->services as $service) {
            ArtisanService::create([
                'artisan_profile_id' => $profile->id,
                'service_name' => $service,
            ]);
        }
    }

} catch (\Exception $e) {
    return response()->json([
        'error' => 'ERREUR SERVICES',
        'message' => $e->getMessage()
    ], 500);
}
        

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Inscription réussie',
            'token'   => $token,
            'user'    => $user->load('artisanProfile.services')
        ], 201);
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

        try {
            if (!$token = JWTAuth::attempt($request->only('email','password'))) {
                return response()->json(['message' => 'Login incorrect'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Erreur token'], 500);
        }

        $user = JWTAuth::user()->load('artisanProfile.services');

        return response()->json([
            'token' => $token,
            'user'  => $user
        ]);
    }
}