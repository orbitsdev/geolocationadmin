<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ChatRoomResource;


class ChatRoomController extends Controller
{

    public function index(string $councilId)
    {
        $council = Council::findOrFail($councilId);


    $chatRoom = $council->chatRooms()->with('messages.councilPosition')->first(); // Eager load messages and council position relationships

    if (!$chatRoom) {
        return ApiResponse::error('Chat room not found', 404);
    }

    return ApiResponse::success(new ChatRoomResource($chatRoom), 'Chat room retrieved successfully');

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
}
