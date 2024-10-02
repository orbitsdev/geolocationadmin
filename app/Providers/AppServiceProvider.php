<?php

namespace App\Providers;

use App\Models\Council;
use App\Models\User;
use App\Observers\CouncilObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\ApiResponse;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Model::unguard();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($data = [], $message = 'Success', $code = 200) {
            return ApiResponse::success($data, $message, $code);
        });
    
        // Error macro
        Response::macro('error', function ($message = 'Error', $code = 400) {
            return ApiResponse::error($message, $code);
        });
    
        // Paginated macro
        Response::macro('paginated', function (LengthAwarePaginator $data, $message = 'Data retrieved successfully', $code = 200) {
            return ApiResponse::paginated($data, $message, $code);
        });
        User::observe(UserObserver::class);
        Council::observe(CouncilObserver::class);
    }
}
