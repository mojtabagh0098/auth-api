<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Laravel API Documentation",
 *      description="API documentation for authentication and user management",
 *      @OA\Contact(
 *          email="support@example.com"
 *      )
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/register",
     *      tags={"Authentication"},
     *      summary="ثبت‌نام کاربر جدید",
     *      description="یک کاربر جدید ثبت‌نام می‌کند و یک توکن دسترسی دریافت می‌کند.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","password","password_confirmation"},
     *              @OA\Property(property="name", type="string", example="Ali Ahmadi"),
     *              @OA\Property(property="email", type="string", format="email", example="ali@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="کاربر با موفقیت ثبت شد",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", type="object"),
     *              @OA\Property(property="access_token", type="string", example="token_abc123")
     *          )
     *      ),
     *      @OA\Response(response=400, description="خطای اعتبارسنجی"),
     *      @OA\Response(response=500, description="خطای سرور")
     * )
     */
    public function register(Request $request) : Response {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);

        $validatedData['password'] = bcrypt(request('password'));

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user' => $user, 'access_token' => $accessToken], 201);
    }
    public function login(Request $request) : Response {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken], 200);
    }
    public function logout() : Response {
        auth()->user()->currentAccessToken()->delete();

        return response(['message' => 'Successfully logged out']);
    }

    public function profile() : Response {
        return response(['user' => auth()->user()]);
    }
}
