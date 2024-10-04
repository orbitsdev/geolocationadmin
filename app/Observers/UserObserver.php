<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $slug = Str::slug($user->first_name . ' ' . $user->last_name);
        $originalSlug = $slug;


        $counter = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $user->slug = $slug;
    
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $user->slug = Str::slug($user->first_name . ' ' . $user->last_name);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
