<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class UpdateUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user information';

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
        $this->info('updating user infomation');

        $users = User::all();

        $bar = $this->output->createProgressBar(count($users));

        $bar->start();

        $this->updateUsers($users);
       
        $bar->finish();
    }

    public function updateUsers($users)
    {
        $users->each(function ($user) {
            $newUserInfo = factory(User::class)->make([
                'email' => $user->email
            ]);


            $user->first_name = $newUserInfo->first_name;
            $user->last_name = $newUserInfo->last_name;
            $user->time_zone = $newUserInfo->time_zone;

            $user->update();
        });

    }
}
