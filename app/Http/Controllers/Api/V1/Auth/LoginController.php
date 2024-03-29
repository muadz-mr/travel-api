<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for auth-related features
 */
class LoginController extends Controller
{
    /**
     * Login
     *
     * This endpoint allows you login and retrieve access token.
     *
     * @response status=200 scenario="Success" {"access_token": "2|jdijijdeuhiuf"}
     * @response status=422 scenario="Data incorrect" {"error": "The provided credentials are incorrect."}
     **/
    public function __invoke(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error' => 'The provided credentials are incorrect.',
            ], 422);
        }

        $device = substr($request->userAgent() ?? '', 0, 255);

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken,
        ]);
    }
}
