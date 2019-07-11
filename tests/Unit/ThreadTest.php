<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ThreadTest extends TestCase
{
	use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create(\App\Thread::class);
    }

    /** @test */
    public function a_thread_has_a_path()
    {
        $thread = create(\App\Thread::class);
        $this->assertEquals(
            "/threads/{$thread->channel->slug}/{$thread->slug}", $thread->path()
        );
    }

    /** @test */
    public function a_thread_has_replies()
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->thread->replies);
    }

    /** @test */
    public function a_thread_has_a_creator()
    {
        $this->assertInstanceOf(\App\User::class, $this->thread->creator);
    }

    /** @test */
    public function a_thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'body' => 'Foobar',
            'user_id' => 1
        ]);
        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */
    public function a_thread_notifies_all_registered_subscribers_when_a_reply_is_added()
    {
        \Notification::fake();

        $this->signIn()
             ->thread
             ->subscribe()
             ->addReply([
                'body' => 'Foobar',
                'user_id' => create(\App\User::class)->id
        ]);

        \Notification::assertSentTo(auth()->user(), \App\Notifications\ThreadWasUpdated::class);
    }

    /** @test */
    public function a_thread_belongs_to_a_channel()
    {
        $this->withExceptionHandling();

        $thread = create(\App\Thread::class);
        $this->assertInstanceOf(\App\Channel::class, $thread->channel);
    }

    /** @test */
    public function a_thread_can_be_subscribed_to()
    {
        $thread = create(\App\Thread::class);

        $thread->subscribe($userId = 1);

        $this->assertEquals(
            1,
            $thread->subscriptions()->where('user_id', $userId)->count()
        );

    }

    /** @test */
    public function a_thread_can_be_unsubscribed_from()
    {
        $thread = create(\App\Thread::class);

        $thread->subscribe($userId = 1);

        $thread->unsubscribe($userId);

        $this->assertCount(0, $thread->subscriptions);
    }

    /** @test */
    public function it_knows_if_the_authenticated_user_is_subscribed_to_it()
    {
        $thread = create(\App\Thread::class);

        $this->signIn();

        $this->assertFalse($thread->isSubscribedTo);

        $thread->subscribe();

        $this->assertTrue($thread->isSubscribedTo);
    }

    /** @test */
    public function a_thread_can_check_if_the_authenticated_user_has_read_all_replies()
    {
        $this->signIn();

        $thread = create(\App\Thread::class);

        tap(auth()->user(), function ($user) use ($thread) {
            $this->assertTrue($thread->hasUpdatesFor($user));

            $user->read($thread);            

            $this->assertFalse($thread->hasUpdatesFor($user)); 
        });               
    }

    // /** @test */
    // public function a_thread_record_each_visit()
    // {
    //     $thread = make(\App\Thread::class, ['id' => 1]);

    //     $thread->visits()->reset();

    //     $this->assertSame(0, $thread->visits()->count());

    //     $thread->visits()->record();

    //     $this->assertEquals(1, $thread->visits()->count());

    //     $thread->visits()->record();

    //     $this->assertEquals(2, $thread->visits()->count());
    // }

    /** @test */
    // public function a_thread_may_be_locked()
    // {
    //     $this->assertFalse($this->thread->locked);

    //     $this->thread->lock();

    //     $this->assertTrue($this->thread->locked);
    // }

    /** @test */
    public function a_thread_body_is_sanitized_automatically()
    {
        $thread = make(\App\Thread::class, ['body' => '<script>alert("bad")</script><p>This is okay.</p>']);

        $this->assertEquals('<p>This is okay.</p>', $thread->body);
    }
}