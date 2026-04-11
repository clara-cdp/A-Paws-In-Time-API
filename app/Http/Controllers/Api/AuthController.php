<?php

namespace App\Http\Controllers\Api;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;


/**
 * @group 1. ACCOUNT
 * APIs for managing user accounts. This includes registration of new users, 
 * identity verification through login, and secure session termination.
 * <aside>The <b>Logout</b> endpoint requires a valid Bearer Token.</aside>
 */

class AuthController extends Controller
{
    /**
     * REGISTER 
     * 
     * Allows a User to create an account.
     * 
     * @bodyParam name string required The name of the user. Example: Leonard Nemaw
     * @bodyParam email string required The email of the user. Example: nemawtrek@apaws.com
     * @bodyParam password string required The password. Example: Apawsy123!
     * @bodyParam password_confirmation string required must match password. Example: Apawsy123!

     * @response 201 scenario="CREATED"
     * {
     *   "user": {
     *       "id": 15,
     *       "name": "Leonard Nemaw", 
     *       "email": "nemawtrek@apaws.com",
     *       "role": "User",
     *       "is_active": true
     *      }
     * },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
     * 
     * @response 422 scenario="validation error"
     *  { "message": "The email field must be a valid email address.",
     *      "errors": {
     *         "email": [
     *             "The email field must be a valid email address."
     *         ]
     *     }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->refresh();

        $user->assignRole(RolesEnum::User->value);
        $user->load('roles');

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * LOGIN
     * 
     * Allows a User to log in to its own account.
     * 
     * @bodyParam email string required. Example: paw@apaws.com
     * @bodyParam password string required. Example: Pawsword2!
     * 
     * @response 200 scenario="OK"
     * {
     *   "user": {
     *       "id": 15,
     *       "name": "Paw Solo", 
     *       "email": "spawwars@apaws.com",
     *       "role": "User",
     *       "is_active": true
     *      }
     * },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
     * 
     * @response 401 scenario="unauthorized"{ "message": "Invalid Credentials"}
     * 
     */
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
        $user->load('roles');
        $token = $user->createToken('api_token')->accessToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 200);
    }

    /**
     * LOGOUT
     * 
     * Revokes the current user's access token to end the session.
     * <aside class="warning">Once revoked, the token can no longer be used to access the resto of protected routes.</aside>
     * @authenticated
     * @response 200 scenario="OK"{"message": "Successfully logged out"}
     * 
     * @response 401 scenario="Unauthorized"{"message": "Unauthenticated."}
     * 
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->token()) {
            $user->token()->revoke();

            return response()->json(['message' => 'Successfully logged out'], 200);
        }

        return response()->json(['message' => 'Token not found'], 404);
    }
}

