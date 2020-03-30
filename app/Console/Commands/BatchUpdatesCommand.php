<?php

namespace App\Console\Commands;

use App\Events\BatchUpdate;
use App\PendingUpdateRequest;
use App\User;
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

        if ($this->canRunUpdateRequest()) {

            $total = PendingUpdateRequest::count();

            if ($total >= $this->runsLimit) {

                $requestData = [ 
                    'batches' => [
                        'subscribers' =>  PendingUpdateRequest::take(1000)->pluck('data')
                    ]
                ];
                
                event(new BatchUpdate($requestData));

                Redis::incr('number_of_calls_made');
            }
        } else {
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
        return !is_null($this->getCurrentCounter()) && $this->numberofRuns <= 50;
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
