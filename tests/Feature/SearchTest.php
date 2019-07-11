<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function a_user_can_search_threads()
    {
        if (! config('scout.algolia.id')) {
            $this->markTestSkipped("Algolia is not configured.");
        }

    	config(['scout.driver' => 'algolia']);

    	$search = 'foobar';

    	create(\App\Thread::class, [], 2);
        create(\App\Thread::class, ['body' => "A thread with the {$search} term."], 2);

    	do {
    		sleep(.25);
    		$results = $this->getJson("/threads/search?query={$search}")->json();
    	} while (empty($results));
        
     	$this->assertCount(2, $results['data']);
    }
}
