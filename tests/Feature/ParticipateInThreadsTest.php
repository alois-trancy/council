<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ParticipateInThreadsTest extends TestCase
{

	use DatabaseMigrations;

	/** @test */
    public function unauthenticated_users_may_not_add_replies()
    {
    	$this->withExceptionHandling()
             ->post('/threads/some-channel/1/replies', [])
             ->assertRedirect('/login');
    }


    /** @test */
    public function an_authenticated_user_may_participate_in_forum_threads()
    {
        // Given we have an authenticated user
        $this->signIn();

        // And an existing thread
        $thread = create(\App\Thread::class);

        // When the user adds a reply to the thread
        $reply = make(\App\Reply::class);
        $this->post($thread->path().'/replies', $reply->toArray());

        // Then their reply should be visible on the page.
        // $this->get($thread->path())
        // 	 ->assertSee($reply->body);

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
        $this->assertEquals(1, $thread->fresh()->replies_count);
    }

    /** @test */
    public function a_reply_requires_a_body()
    {
        $this->withExceptionHandling()
             ->signIn();

        $thread = create(\App\Thread::class);

        $reply = make(\App\Reply::class, ['body' => null]);
        $this->post($thread->path().'/replies', $reply->toArray())
             ->assertSessionHasErrors('body');
    }

    /** @test */
    public function unauthorize_users_cannot_delete_replies()
    {
        $this->withExceptionHandling();

        $reply = create(\App\Reply::class);

        $this->delete("/replies/{$reply->id}")
             ->assertRedirect('/login');

        $this->signIn()
             ->delete("/replies/{$reply->id}")
             ->assertStatus(403);
    }

    /** @test */
    public function authorize_users_can_delete_replies()
    {
        $this->signIn();
        $reply = create(\App\Reply::class, ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}")
             ->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

    /** @test */
    public function unauthorize_users_cannot_update_replies()
    {
        $this->withExceptionHandling();

        $reply = create(\App\Reply::class);

        $updatedReply = 'You\'ve been changed, fool.';
        $this->patch("/replies/{$reply->id}", ['body' => $updatedReply])
             ->assertRedirect('/login');

        $this->signIn()
             ->patch("/replies/{$reply->id}", ['body' => $updatedReply])
             ->assertStatus(403);
    }

    /** @test */
    public function authorize_users_can_update_replies()
    {
        $this->signIn();
        $reply = create(\App\Reply::class, ['user_id' => auth()->id()]);

        $updatedReply = 'You\'ve been changed, fool.';
        $this->patch("/replies/{$reply->id}", ['body' => $updatedReply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updatedReply]);
    }

    /** @test */
    public function replies_that_contain_spam_may_not_be_created()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $thread = create(\App\Thread::class);
        $reply = make(\App\Reply::class, [
            'body' => 'Yahoo Customer Support'
        ]);

        $this->json('post', $thread->path().'/replies', $reply->toArray())
             ->assertStatus(422);
    }

    /** @test */
    function users_may_only_reply_a_maximum_of_once_per_minute()
    {
        $this->withExceptionHandling();
        
        $this->signIn();

        $thread = create(\App\Thread::class);
        $reply = make(\App\Reply::class, [
            'body' => 'My simple reply.'
        ]);

        $this->post($thread->path().'/replies', $reply->toArray())
             ->assertStatus(200);
             
        $this->post($thread->path().'/replies', $reply->toArray())
             ->assertStatus(429);
    }
}
