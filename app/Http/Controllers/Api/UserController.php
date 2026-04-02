<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    public function show(Request $request): JsonResponse
    {
        $request->user(); 
        return response()->json([
            'user'=>$request->user()->load('roles')
        ],200);
    }

    /**
     * Update the specified resource in storage.
     * PUT /api/me
     */
    public function update(Request $request) : JsonResponse
    {
        $user = $request->user(); 

        $validated = $request -> validate ([ 
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id, 
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Your Profile is been updated.',
            'user' =>$user
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/me
     */
    public function destroy(request $request): JsonResponse
    {
        $user = $request->user();

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->token()->revoke();

        $user->delete();

        return response()->json([
            'message' => 'Your Account has been deleted.'
        ],200);
    }
}
