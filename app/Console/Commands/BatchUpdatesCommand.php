<?php

namespace App\Console\Commands;

use App\PendingUpdateRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class BatchUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:updates';

    protected $batchRequestData = ['batches' => []];

    protected $numberofRuns;


    protected $runsLimit = 1000;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs user data with third party provider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initializeCounter();

        if (!is_null($this->getCurrentCounter()) && $this->canRunUpdateRequest()) {

        
            $total = PendingUpdateRequest::count();

            if ($total >= $this->runsLimit) {

                $requestData = ['subscribers' => []];

                foreach (PendingUpdateRequest::take($this->runsLimit)->cursor() as $updatedRecord) {
                    array_push($requestData['subscribers'], $updatedRecord->data);
                    logger("[{$updatedRecord->user->id}] firstname: {$updatedRecord->user->first_name} time_zone: {$updatedRecord->user->time_zone}");
                    $updatedRecord->delete();
                }

                array_push($this->batchRequestData['batches'], $requestData);

                Redis::incr('number_of_calls_made');
            }
        } else {
            /**
             * makes sure the key has not expired before resetting it
             * it ensures we make the required number of requests within the hour
             */
            if (!$this->getCurrentCounter()) {
                $this->resetRuns();
            }
        }
    }

    /**
     * @return bool
     * should update run
     */
    protected function canRunUpdateRequest()
    {
        return $this->numberofRuns <= 50;
    }

    /**
     * @return mixed
     * will return null when redis expires key in after an hour
     */
    protected function getCurrentCounter()
    {
        return Redis::get('number_of_calls_made');
    }


    /**
     * sets the current number of runs
     */
    protected function initializeCounter()
    {
        $numberofRuns = $this->getCurrentCounter();

        if (is_null($numberofRuns)) {
            $numberofRuns = $this->resetRuns();
        }

        $this->numberofRuns = $numberofRuns;
    }

    /**
     * resets key in redis for an hour
     */
    protected function resetRuns()
    {
        Redis::set('number_of_calls_made', 1, 'EX', 60 * 60);
    }

}
