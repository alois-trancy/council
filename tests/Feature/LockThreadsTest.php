<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LockThreadsTest extends TestCase
{
	use DatabaseMigrations;

	/** @@test */
    public function non_adminitrators_may_not_lock_threads()
    {
    	$this->withExceptionHandling();

        $this->signIn();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $this->post(route('locked-threads.store', $thread))->assertStatus(403);

        $this->assertFalse($thread->fresh()->locked);
    }

    /** @@test */
    public function administrators_can_lock_threads()
    {
        $this->signInAdmin();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);

        $this->post(route('locked-threads.store', $thread));

        $this->assertTrue($thread->fresh()->locked, 'Failed asserting that the thread was locked.');
    }

    /** @@test */
    public function administrators_can_unlock_threads()
    {
        $this->signInAdmin();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id(), 'locked' => true]);

        $this->delete(route('locked-threads.destroy', $thread));

        $this->assertFalse($thread->fresh()->locked, 'Failed asserting that the thread was unlocked.');
    }

    /** @@test */
    public function once_locked_a_thread_may_not_receive_new_replies()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['locked' => true]);

        $this->post($thread->path() . '/replies', [
        	'body' => 'Foobar',
        	'user_id' => auth()->id()
        ])->assertStatus(422);
    }
}
