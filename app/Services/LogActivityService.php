<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class LogActivityService
{
    /**
     * Log an activity entry.
     */
    public function log(
        ?string $logName = null,
        ?string $event = null,
        ?Model $causedBy = null,
        ?Model $performedOn = null,
        ?array $properties = null,
        ?string $description = null,
    ): void {
        $activity = activity($logName);

        if ($event) {
            $activity->event($event);
        }

        if ($causedBy) {
            $activity->by($causedBy);
        }

        if ($performedOn) {
            $activity->on($performedOn);
        }

        if ($properties) {
            $activity->withProperties($properties);
        }

        $activity->log($description ?? 'Activity logged');
    }
}
