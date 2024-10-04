<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\CouncilController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CouncilPositionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('guest')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('auth/google/signin', [AuthController::class, 'signInWithGoogle']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'userDetails']); // Get user details
    Route::put('user', [AuthController::class, 'updateUser']);
    Route::apiResource('councils', CouncilController::class);
    Route::get('councils/{council}/positions', [CouncilPositionController::class, 'index']);
    Route::get('councils/{council}/positions/{id}', [CouncilPositionController::class, 'show']);
    Route::post('councils/{council}/positions', [CouncilPositionController::class, 'store']);
    Route::put('councils/{council}/positions/{id}', [CouncilPositionController::class, 'update']);
    Route::delete('councils/{council}/positions/{id}', [CouncilPositionController::class, 'destroy']);
    Route::put('/council-positions/{id}/switch', [CouncilPositionController::class, 'switchPosition']);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('collections', CollectionController::class);
    Route::delete('collections/{collectionId}/items/{itemId}', [CollectionController::class, 'removeItem']);
    Route::get('councils/{councilId}/chat-room', [ChatRoomController::class, 'show']);
    Route::get('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'index']);
    Route::post('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'store']);
    Route::apiResource('councils.events', EventController::class);
    Route::post('councils/{councilId}/events/{eventId}/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('councils/{councilId}/events/{eventId}/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('devices/register', [DeviceController::class, 'storeOrUpdate']);
    Route::delete('devices/{deviceId}', [DeviceController::class, 'destroy']);
});
