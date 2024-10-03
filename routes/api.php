<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

Route::apiResource('councils', CouncilController::class);
Route::apiResource('council-positions', CouncilPositionController::class);
Route::apiResource('tasks', TaskController::class);
Route::apiResource('posts', PostController::class);
Route::apiResource('collections', CollectionController::class);
Route::delete('collections/{collectionId}/items/{itemId}', [CollectionController::class, 'removeItem']);
Route::get('councils/{councilId}/chat-room', [ChatRoomController::class, 'show']);
Route::get('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'index']);
Route::post('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'store']);
Route::apiResource('councils.events', EventController::class);
Route::post('councils/{councilId}/events/{eventId}/attendance/check-in', [AttendanceController::class, 'checkIn']);
Route::post('devices/register', [DeviceController::class, 'storeOrUpdate']);
Route::delete('devices/{deviceId}', [DeviceController::class, 'destroy']);
