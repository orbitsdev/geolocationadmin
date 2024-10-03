<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
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
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'provider' => 'CREDENTIALS',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'User registered successfully', 201);
    }

    // Login user
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'User logged in successfully');
    }

    // Handle Google Sign-In using token
    public function signInWithGoogle(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->input('token'));

        $parsedName = $this->parseFullName($googleUser->getName());

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'first_name' => $parsedName['first_name'],
                'last_name' => $parsedName['last_name'],
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'image' => $googleUser->getAvatar(),
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'User logged in with Google successfully');
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    // Get the details of the authenticated user
    public function userDetails(Request $request)
    {
        $user = $request->user(); // Assuming you're using Sanctum to handle authentication

        return ApiResponse::success(new UserResource($user), 'User details retrieved successfully');
    }

    public function updateUser(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        // Update user data
        if (!empty($validatedData['first_name'])) {
            $user->first_name = $validatedData['first_name'];
        }

        if (!empty($validatedData['last_name'])) {
            $user->last_name = $validatedData['last_name'];
        }
        if (!empty($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }

        // Update password if provided
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Save the updated user information
        $user->save();

        return ApiResponse::success(new UserResource($user), 'User updated successfully');
    }
    private function parseFullName($fullName)
    {
        $nameParts = explode(' ', trim($fullName));
        $firstName = array_shift($nameParts); // Get the first part of the name
        $lastName = implode(' ', $nameParts); // Concatenate the rest as the last name

        return [
            'first_name' => $firstName,
            'last_name' => $lastName ?: '',
        ];
    }
}
