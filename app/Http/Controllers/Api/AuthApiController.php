<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthApiController
 * ──────────────────
 * Issues and revokes Laravel Sanctum personal access tokens.
 *
 * Postman usage:
 *   POST /api/login          { email, password }          → returns token
 *   POST /api/logout         Bearer <token> in header     → revokes token
 *   GET  /api/user           Bearer <token>               → current user
 */
class AuthApiController extends Controller
{
    // ── POST /api/login ───────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        /** @var User $user */
        $user  = Auth::user();

        // Revoke old tokens so only one session at a time
        $user->tokens()->delete();

        $token = $user->createToken('pharmacy-api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    // ── POST /api/logout ──────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token used for this request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // ── GET /api/user ─────────────────────────────────────────
    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
