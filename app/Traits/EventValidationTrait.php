<?php

namespace App\Traits;

use App\Models\Event;

trait EventValidationTrait
{
    public function validateEventTiming(Event $event)
    {
        $formattedStartTime = $event->start_time->format('l, F j, Y, g:i A');
        $formattedEndTime = $event->end_time->format('l, F j, Y, g:i A');
        $currentTime = now()->format('l, F j, Y, g:i A');

        if (now()->lt($event->start_time)) {
            throw new \Exception("The event has not started yet. It starts on {$formattedStartTime}. Current time is {$currentTime}.", 403);
        }

        if (now()->gt($event->end_time)) {
            throw new \Exception("The event has already ended. It ended on {$formattedEndTime}. Current time is {$currentTime}. You cannot proceed at this time.", 403);
        }
    }
}
