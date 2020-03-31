<?php

namespace App\Listeners;

use App\PendingUpdateRequest;

class SendBatchUpdateRequest
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->dataToBeUpdated['batches']['subscribers']->each(function($data){
            $updateRequest = PendingUpdateRequest::whereJsonContains('data->email', json_decode($data)->email)
                ->first();
            
            $this->logData($updateRequest);
            
            $updateRequest->delete();
        });

    }

    public function logData($data)
    {
        logger("[{$data->user->id}] firstname: {$data->user->first_name} time_zone: {$data->user->time_zone}");
    }
}
