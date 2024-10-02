<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    // Success response
    public static function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    // Error response
    public static function error($message = 'Error', $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    // Paginated response
    public static function paginated(LengthAwarePaginator $data, $message = 'Data retrieved successfully', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
            ],
        ], $code);
    }
}