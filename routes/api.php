<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\CouncilController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\NotificationController;
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
    Route::get('user', [AuthController::class, 'userDetails']);
    Route::post('user/profile-update', [AuthController::class, 'updateProfile']);

    Route::apiResource('councils', CouncilController::class);


    Route::get('positions', [PositionController::class, 'index']);

    Route::get('councils/{council}/available-users', [CouncilPositionController::class, 'availableUsers']);
    Route::get('councils/{council}/search-officers', [CouncilPositionController::class, 'searchOfficer']);
    Route::get('councils/{council}/positions', [CouncilPositionController::class, 'index']);
    Route::get('councils/{council}/positions/{id}', [CouncilPositionController::class, 'show']);
    Route::post('councils/{council}/positions', [CouncilPositionController::class, 'store']);
    Route::put('councils/{council}/positions/{id}', [CouncilPositionController::class, 'update']);
    Route::delete('councils/{council}/positions/{id}', [CouncilPositionController::class, 'destroy']);
    Route::put('/council-positions/{id}/switch', [CouncilPositionController::class, 'switchPosition']);

    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/files', [TaskController::class, 'uploadFiles']);
    Route::delete('/tasks/{taskId}/media/{mediaId}', [TaskController::class, 'deleteMedia']);

    Route::get('/tasks/council-tasks/{council_position_id}', [TaskController::class, 'fetchByCouncilPositionOrCouncil']);
    Route::apiResource('tasks', TaskController::class);

    Route::get('/posts/council-posts/{council_position_id}', [PostController::class, 'fetchByCouncilPositionOrCouncil']);
    Route::get('/posts/council/{council_id}', [PostController::class, 'fetchByCouncil']);
    Route::delete('/posts/{postId}/media/{mediaId}', [PostController::class, 'deleteMedia']);

    Route::post('posts/update/{id}',[PostController::class,'updatePost'])->name('update.posts');
    Route::apiResource('posts', PostController::class);

    Route::get('/collections/council', [CollectionController::class, 'fetchByCouncil']);
    Route::delete('/collections/{collectionId}/items/{itemId}', [CollectionController::class, 'removeItem']);
    Route::apiResource('collections', CollectionController::class);

    Route::post('chat-rooms/{chatRoomId}/create-messages', [MessageController::class, 'store']);
    Route::get('chat-rooms/{chatRoomId}/messages', [MessageController::class, 'index']);
    Route::get('/councils/council-events/{council_position_id}', [EventController::class, 'fetchByCouncilPositionOrCouncil']);
    Route::post('councils/{councilId}/events/create', [EventController::class, 'store']);

    Route::get('councils/events/my-attendances', [AttendanceController::class, 'myAttendance']);
    Route::get('councils/events/{eventId}/attendances', [AttendanceController::class, 'showEventAttendance']);
    Route::get('councils/events/{eventId}/attendance-record', [AttendanceController::class, 'showEventAttendanceRecord']);

    Route::apiResource('councils.events', EventController::class);
    Route::post('councils/{councilId}/events/{eventId}/attendance/check-in', [AttendanceController::class, 'checkIn']);

    Route::post('councils/{councilId}/events/{eventId}/attendance/check-out', [AttendanceController::class, 'checkOut']);
    
    Route::post('devices/register', [DeviceController::class, 'storeOrUpdate']);
    Route::delete('devices/{deviceId}', [DeviceController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/read/multiple', [NotificationController::class, 'markMultipleAsRead']);

    Route::get('/media/by-council/{councilId}', [MediaController::class, 'fetchMediaByCouncil']);
});


Route::get('/send-notification', function(){
    FCMController::sendPushNotification('fUyNeZkhQ-6wn2-S-Jn48C:APA91bHZSBE0Lu8bOBpc98TPcXi6BywPoTpFr9aXfQjuJjIhK_6H8mlaoNRdpu_U2YXbLghaM-v1DiNH_8jMLcrhLcoCoPL4eiF8ioZp8oacivLXBqi1SC8', 'Task Assigned', 'test', [

        'notification' => 'task',
    ]);
});
