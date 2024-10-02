<?php

namespace App\Observers;

use App\Models\Council;

class CouncilObserver
{
    /**
     * Handle the Council "created" event.
     */
    public function created(Council $council): void
    {
        if ($council->chatRooms()->count() === 0) {

            $council->chatRooms()->create([
                'name' => 'Chat Room ' . $council->name,
            ]);
        }
    }

    /**
     * Handle the Council "updated" event.
     */
    public function updated(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "deleted" event.
     */
    public function deleted(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "restored" event.
     */
    public function restored(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "force deleted" event.
     */
    public function forceDeleted(Council $council): void
    {
        //
    }
}
