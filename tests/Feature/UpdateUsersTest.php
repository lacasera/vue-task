<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUsersTest extends TestCase
{
    use RefreshDatabase;


    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }
    /**
     * 
     * @test
     * @return void
     */
    public function user_email_should_not_be_updated()
    {
        $userEmails = User::all()->pluck('email')->all();

        $this->artisan('users:update');

        User::all()->each(function ($updatedUser) use ($userEmails) {
            $this->assertTrue(in_array($updatedUser->email, $userEmails));
        });    
    }

    /**
     * @test
     * 
     */
    public function user_info_should_be_upated()
    {
        $user = User::first();
        $this->artisan('users:update');

        $updatedUser = User::first();

        $this->assertNotEquals($user->first_name, $updatedUser->first_name);
        $this->assertNotEquals($user->last_name, $updatedUser->last_name);
    }

}
