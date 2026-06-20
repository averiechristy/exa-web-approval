<?php

namespace App\Jobs;

use App\Services\LogActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $logName = null,
        public ?string $event = null,
        public ?Model $causedBy = null,
        public ?Model $performedOn = null,
        public ?array $properties = null,
        public ?string $description = null,
    ) {
        $this->onQueue('log');
    }

    /**
     * Execute the job.
     */
    public function handle(LogActivityService $logActivityService): void
    {
        $logActivityService->log(
            logName: $this->logName,
            event: $this->event,
            causedBy: $this->causedBy,
            performedOn: $this->performedOn,
            properties: $this->properties,
            description: $this->description,
        );
    }
}
