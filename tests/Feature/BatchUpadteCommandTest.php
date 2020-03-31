<?php

namespace Tests\Feature;

use App\Events\BatchUpdate;
use App\PendingUpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class BatchUpadteCommandTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        Redis::flushdb();
    }

    /**
     * @test
     * @return void
     */
    public function it_should_not_run_if_records_is_not_up_to_a_1000()
    {
        Event::fake();

        factory(PendingUpdateRequest::class, 20)->create();

        $this->artisan('batch:updates');

        Event::assertNotDispatched(BatchUpdate::class);
    }

    /** @test */
    public function should_send_request_when_records_is_up_to_1000_and_number_of_calls_made_is_less_than_50()
    {
        Event::fake();

        Redis::set('number_of_calls_made', 20);

        factory(PendingUpdateRequest::class, 1000)->create();

        $this->artisan('batch:updates');

        Event::assertDispatched(BatchUpdate::class);
    }

    /** @test */
    public function should_increase_number_of_calls_made_after_run()
    {
        Event::fake();

        Redis::set('number_of_calls_made', 20);

        factory(PendingUpdateRequest::class, 1000)->create();

        $this->artisan('batch:updates');

        $callsMade = Redis::get('number_of_calls_made');

        $this->assertEquals(21, $callsMade);
    }

    /**
     * limit has been reached within the hour 
     * @test 
     */
    public function should_not_send_request_when_records_is_up_to_1000_and_number_of_calls_made_is_greater_than_50()
    {
        Event::fake();

        Redis::set('number_of_calls_made', 51);
        
        factory(PendingUpdateRequest::class, 1000)->create();

        $this->artisan('batch:updates');

        Event::assertNotDispatched(BatchUpdate::class);
    }

}
