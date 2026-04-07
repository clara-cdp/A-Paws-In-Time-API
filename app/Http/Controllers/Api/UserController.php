<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use App\Enums\RolesEnum;
use App\Models\User;

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
     * Display the specified resource.
     * GET /api/me
     */
    public function show(UpdateUserRequest $request): UserResource
    {
        return new UserResource($request->user()); 
    }

    /**
     * Update the specified resource in storage.
     * PUT /api/me
     */
    public function update(UpdateUserRequest $request) : JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Your profile is been updated.', 
            'user' => new UserResource($user),
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/me
     */
    public function destroy(request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        if ($user->hasRole(\App\Enums\RolesEnum::Admin->value, 'api')) {

            $adminCount = \App\Models\User::role(\App\Enums\RolesEnum::Admin->value, 'api')->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Action denied: You are the last Admin. Promote another user before deleting your account.'
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
