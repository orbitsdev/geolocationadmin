<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        // Fetch all available positions
        $positions = Position::select('id', 'name')->get();
        return ApiResponse::success($positions, 'Positions retrieved successfully');
    }
}
