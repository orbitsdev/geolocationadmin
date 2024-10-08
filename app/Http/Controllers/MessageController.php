<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ChatRoomResource;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($chatRoomId)
    {
        $chatRoom = ChatRoom::with(['messages.councilPosition']) // Eager load related data
                        ->findOrFail($chatRoomId);


    return ApiResponse::success(new ChatRoomResource($chatRoom), 'Chat room with messages retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,$chatRoomId)
    {
        $validatedData = $request->validate([
            'council_position_id' => 'required|exists:council_positions,id',
            'message' => 'required|string',
        ]);

        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        $message = $chatRoom->messages()->create($validatedData);

        return ApiResponse::success(new MessageResource($message), 'Message sent successfully', 201);
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
