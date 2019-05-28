<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TrendingThreadsTest extends TestCase
{
    use DatabaseMigrations;

	public function setUp()
	{
		parent::setUp();

        $this->trending = new \App\Trending();

		$this->trending->reset();
	}

    /** @test */
    public function it_increments_a_threads_score_each_time_it_is_read()
    {
    	$this->assertEmpty($this->trending->get());

        $thread = create(\App\Thread::class);

        $this->call('GET', $thread->path());

        $this->assertCount(1, $trending = $this->trending->get());

        $this->assertEquals($thread->title, $trending[0]->title);
    }
}
