<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/admin/users
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles')->paginate(10); 

        if($users->isEmpty()){
            return response()->json(['message' => "No users found"], 404);
        }

        return response()->json($users, 200);

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
     * GET api/admin/user/{user_id}
     */
    public function show(User $user): JsonResponse  
    {
        return response()->json([
            'user' => $user->load('roles')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * PUT api/admin/user/{user_id}
     */
    public function update(Request $request, User $user) : JsonResponse
    {

        if ($this->protectAdmin($user)) {

            return response()->json(['message' => 'Action denied: This user is protected.'], 403);
        }

        $validated = $request -> validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'is_active' => 'sometimes|boolean'
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated succesfully.',
            'user' => $user->load('roles')
        ], 200);

    }

    public function block(User $user): JsonResponse
    {
        if ($this->protectAdmin($user)) {

            return response()->json(['message' => 'Action denied: This user is protected.'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'unblocked' : 'blocked';

        return response()->json([
            'message'=>"User has been {$status}."
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE api/admin/users/{user_id}
     */
    public function destroy(User $user): JsonResponse
    {
        if ($this->protectAdmin($user)) {

            return response()->json(['message' => 'Action denied: This user is protected.'], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.'
        ], 200);

    }

    private function protectAdmin(User $user) : bool{

        if (auth()->id() === $user->id) {
            return true;
        }

        if ($user->hasRole(RolesEnum::Admin->value)) {
            return true;
        }
        
        return false;
    }

}
