<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\Device;
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
        $user->load(['councilPositions' => function ($query) {
            $query->notLogin();
        }]);
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
            return ApiResponse::error('Invalid credentials', 422);
        }
        $user->load(['councilPositions' => function ($query) {
            $query->notLogin();
        }]);
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
        $user->load(['councilPositions' => function ($query) {
            $query->notLogin();
        }]);
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
        $user = $request->user();
        $user->tokens()->delete();

        if ($request->has('device_id')) {
            Device::where('user_id', $user->id)
                  ->where('device_id', $request->device_id)
                  ->delete();
        }

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function userDetails(Request $request)
{
    // Get the authenticated user
    $user = $request->user();

    // Load related council positions (or any other relations you may need)
    $user->load(['councilPositions' => function ($query) {
        $query->notLogin();
    }]);

    // Return the user details using the UserResource
    return ApiResponse::success(new UserResource($user), 'User details retrieved successfully');
}


    // Get the details of the authenticated user
    public function updateUser(Request $request)
{
    $user = $request->user();

    $validatedData = $request->validate([
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|min:8|confirmed',
        'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048'
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

    // Handle image upload
    if ($request->hasFile('image')) {
        // Store the uploaded image in the 'public/profile_images' directory
        $path = $request->file('image')->store('profile_images', 'public');
        // Save the image path to the user model
        $user->image = $path;
    }

    // Save the updated user information
    $user->save();

    // Reload the user data after saving
    $user->refresh();

    // Load related council positions
    $user->load(['councilPositions' => function ($query) {
        $query->notLogin();
    }]);

    return ApiResponse::success(new UserResource($user), 'User updated successfully');
}


}
