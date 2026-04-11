<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use App\Enums\RolesEnum;
use App\Models\User;

/**
 * @group 2.USER 
 * Endpoints for managing the authenticated user's profile information and account status.
 * <aside><b>Bearer Token</b> is nedded to try out all the following routes</aside>
 * @authenticated
 */
class UserController extends Controller
{
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * USER - view profile
     * 
     * Fetches the profile details of the currently authenticated user.
     * 
     * @response 200 scenario="OK"
     * { 
     *   "user": {
     *      "id": 16,
     *      "name": "Meawdona", 
     *      "email": "likeapurr@apaws.com",
     *      "role": "User",
     *      "is_active": true}
     *  },
     * 
     * @response 401 escenario="Unauthorized"{ "message": "Unauthenticated." }
     */
    public function show(request $request): UserResource
    {
        $user = $request->user();

        if (!$user) {
            return abort(401, 'Unauthenticated');
        }

        return new UserResource($user);
    }

    /**
     * USER - update profile 
     * 
     * Allows a user to update their own dprofile.
     * Updates the authenticated user's name, email, or password.
     * password confirmation needed in case the user wants to update the password too.
     * 
     * @bodyParam name string required The name of the user. Example: Meawdona
     * @bodyParam email string required The email of the user. Example: likeapurr@apaws.com
     * @bodyParam password string required The password. Example: Apawsy123!
     * @bodyParam password_confirmation string required must match password. Example: Apawsy123!
     * 
     * @response 200 scenario="OK"
     *{
     *   "message": "Your profile is been updated.", 
     *   "user": {
     *       "id": 16,
     *       "name": "Meawdonna", 
     *       "email": "likeapurr@apaws.com",
     *       "role": "User",
     *       "is_active": true}
     *     },
     * }
     * 
     * @response 422 escenario="UNPROCESSABLE REQUEST"
     * {
     *       "message": "The name field must be at least 2 characters.",
     *       "errors": {
     *           "name": [
     *               "The name field must be at least 2 characters."
     *           ]
     *       }
     *   }
     * 
     * @response 401 escenario="Unauthorized" { "message": "Unauthenticated."}
     * 
     */
    public function update(UpdateUserRequest $request) : JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $user->update($request->validated());

        return response()->json([
            'message' => 'Your profile is been updated.', 
            'user' => new UserResource($user),
        ],200);
    }

    /**
     * USER - delete profile 
     * 
     * Deletes the authenticated user's account and revokes all active tokens.
     * (Last Admin is not allowed to erase itself).
     * <aside class="warning">This action is permanent and cannot be undone.</aside>
     * 
     * @response 200 scenario="OK"{ "message": "Your Account has been deleted."}
     * @response 401 escenario="Unauthorized"{ "message": "Unauthenticated." }
     * @response 403 scenario="Forbidden" {"message": "Action denied: You are the last Admin.
     *  Promote another user before deleting your account or contact the Paws Master." }
     */
    public function destroy(request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        if ($user->hasRole(\App\Enums\RolesEnum::Admin->value, 'api')) {

            $adminCount = \App\Models\User::role(\App\Enums\RolesEnum::Admin->value, 'api')->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Action denied: You are the last Admin. Promote another user before deleting your account or contact the Paws Master.'
                ], 403);
            }
        }

        $user->token()->revoke();
        $user->delete();

        return response()->json([
            'message' => 'Your Account has been deleted.'
        ],200);
    }
}
