<?php

namespace App\Http\Controllers\Api;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required | string | min:2 | max:255',
            'email' => 'required | string | email | unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->numbers()
                    ->mixedCase()
                    ->symbols(),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole(RolesEnum::User->value);

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $token = $user->createToken('api_token')->accessToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse

    {
        $user = $request->user('api');

        if ($user && $user->token()) {
            $tokenId = $user->token()->id;

            DB::table('oauth_access_tokens')
                ->where('id', $tokenId)
                ->update(['revoked' => true]);

            return response()->json(['message' => 'Successfully logged out'], 200);
        }

        return response()->json(['message' => 'Token not found'], 404);
    }
}

