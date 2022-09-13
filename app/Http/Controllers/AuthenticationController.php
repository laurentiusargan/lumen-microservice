<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthenticationController extends BaseController
{
    /**
     * login a user
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, ['email' => 'required|email', 'password' => 'required|string']);

        $data = $request->all();

        if (User::where('email', $data['email'])->first()) {
            $token = Auth::attempt($data);

            if ($token) {
                return $this->respondWithToken($token);
            }  // successfull login

            return response()->json(['error' => "Email address or password not correct"], 401);  // wrong password
        }

        return response()->json(
            ['error' => "Email address or password not correct"],
            404
        );
    }

    /**
     * logout a user
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * get the authenticated user
     * @return JsonResponse
     */
    public function currentUser(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    /**
     * refresh a token
     * @return JsonResponse
     */
    public function refreshToken()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * get the token
     * @param string $token
     * @return JsonResponse
     */
    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
