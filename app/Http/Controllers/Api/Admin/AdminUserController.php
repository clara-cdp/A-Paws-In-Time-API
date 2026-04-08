<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Http\Requests\Admin\UpdateAdminRequest;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/admin/users
     */
    public function index(): AnonymousResourceCollection | JsonResponse
    {
        $users = User::with('roles','games')
        ->withCount('games')
        ->paginate(10); 

        if($users->isEmpty()){
            return response()->json(['message' => "No users found"], 404);
        }

        return UserResource::collection($users);
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
    public function show(User $user): UserResource 

    {
        $user->load(['roles', 'games'])->loadCount('games');

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     * PUT api/admin/user/{user_id}
     */
    public function update(UpdateAdminRequest $request, User $user) : JsonResponse
    {

        if ($this->protectAdmin($user)) {
            return response()->json(['message' => 'Action denied: This user is protected.'], 403);
        }

        $user->update($request->validated());

        $user->load(['roles', 'games'])->loadCount('games');

        return response()->json([
            'message' => 'User updated succesfully.',
            'user' => new UserResource($user) 
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
