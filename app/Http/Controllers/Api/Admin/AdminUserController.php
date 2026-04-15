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

/**
 * @group 3. ADMIN
 * Administrative endpoints for managing the user database.
 * <aside class="warning">All endpoints in this group require <b>Admin</b> privileges.</aside>
 * <aside>
 * You can use the following credentials for testing:<br>
 * <b>Email:</b> adminino@apaws.com<br>
 * <b>Password:</b> Pawsword1!
 * </aside>
 * @authenticated
 */
class AdminUserController extends Controller
{
    /**
     * ADMIN - INDEX
     * 
     * Retrieve a paginated list of all users, including their roles and games.
     * 
     * @response 200 scenario="Success" {
     * {
     *   "data": [
     *       {
     *           "id": 1,
     *           "name": "Admin",
     *           "email": "adminino@apaws.com",
     *           "role": "Admin",
     *           "is_active": true,
     *           "games_count": 0,
     *           "game_list": []
     *       },
     *       {
     *           "id": 2,
     *           "name": "Edgar Allan Paw",
     *           "email": "paw@apaws.com",
     *           "role": "User",
     *           "is_active": true,
     *           "games_count": 2,
     *           "game_list": [
     *               "Sir Isaac Mewton",
     *               "Lucifur"
     *           ]
     *       },
     *       {
     *          //... continued
     * }
     * 
     * @response 404 scenario="Not found Users" {"message": "No users found"}
     * 
     * @response 403 scenario="No permission" {"message": "The user does not have permission."}
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
     * ADMIN - specific user view
     * 
     * View details of an specific regular user. 
     * 
     * @response 200 scenario="ok" {
     * {
     *      "data": {
     *           "id": 2,
     *           "name": "Edgar Allan Paw",
     *           "email": "paw@apaws.com",
     *           "role": "User",
     *           "is_active": true,
     *           "games_count": 2,
     *           "game_list": [
     *               "Sir Isaac Mewton",
     *               "Lucifur"
     *           ]
     *       }
     *   }
     * }
     */

    public function show(User $user): UserResource | JsonResponse
    {
        $user->load(['roles', 'games'])->loadCount('games');
        return response()->json([
            'user' => new UserResource($user)
        ], 200);
    }

    /**
     * ADMIN - UPDATE
     * 
     * Update the details of a regular user.
     *  
     * <aside>Note: An admin cannot update a fellow admin data.</aside>
     *  
     * @response 200 scenario="Updated" {
     * {
     *      "message": "User updated successfully.",
     *      "user": {
     *          "id": 3,
     *          "name": "Clawcatra",
     *          "email": "Claw@apaws.com",
     *          "role": "User",
     *          "is_active": true,
     *          "games_count": 2,
     *          "game_list": [
     *              "Clawdia" 
     *             "Pawliver"]
     *      }
     *  }
     * }
     * @response 403 scenario="Protected User" {"message": "Action denied: This user is protected."}
     */
    public function update(UpdateAdminRequest $request, User $user) : JsonResponse
    {
        if ($this->protectAdmin($user)) {
            return response()->json(['message' => 'Action denied: This user is protected.'], 403);
        }

        $user->update($request->validated());

        if ($request->has('role')) {
            $user->syncRoles([$request->input('role')]);
        }

        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
            $user->save();
        }

        $user->load(['roles', 'games'])->loadCount('games');

        return response()->json([
            'message' => 'User updated succesfully.',
            'user' => new UserResource($user) 
        ], 200);

    }

    /**
     * ADMIN - Toggle Block
     * Inverts the 'is_active' status of a user. 
     * If the user was active, they become blocked, and vice versa.
     * Since the user isn't deleted, the email address can not be used again in the future.
     * 
     * @response 200 scenario="Blocked" {"message": "User has been blocked."}
     * @response 200 scenario="Unblocked" {"message": "User has been unblocked."}
     */
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
     * ADMIN - DELETE:
     * 
     * Permanently remove a user from the database.<br>
     * <aside>NOTE: An admin cannot delete a felow admin.</aside>
     * <aside class="warning">This action is permanent and cannot be undone.</aside>
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

        if (auth()->check() && auth()->id() === $user->id) {
            return true;
        }

        if ($user->hasRole(RolesEnum::Admin->value)) {
            return true;
        }
        
        return false;
    }

}
