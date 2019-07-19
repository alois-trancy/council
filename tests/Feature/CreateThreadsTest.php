<?php

namespace Tests\Feature;

use App\Rules\Recaptcha;
use App\Thread;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        app()->singleton(Recaptcha::class, function () {
            return \Mockery::mock(Recaptcha::class, function ($m) {
                $m->shouldReceive('passes')->andReturn(true);    
            });
        });
    }

    /** @test */
    public function guests_may_not_create_threads()
    {
        $this->withExceptionHandling();

        $this->get('/threads/create')
             ->assertRedirect(route('login'));

        $this->post(route('threads'))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function new_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        $user = factory(\App\User::class)->states('unconfirmed')->create();

        $this->signIn($user);

        $thread = make(\App\Thread::class);

        $this->post(route('threads'), $thread->toArray())
             ->assertRedirect(route('threads'))
             ->assertSessionHas('flash', 'You must confirm your email address.');
    }

    /** @test */
    public function a_user_can_create_new_forum_threads()
    {
        // // Given we have a signed in user
        // $this->signIn();

        // // When we hit the endpoint to create a new thread
        // $thread = make(\App\Thread::class);
        // $response = $this->post(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token']);

        $response = $this->publishThread([
            'title' => 'Some Title',
            'body' => 'Some Body',
        ]);

        // Then, when we visit the thread page,
        // We should see the new thread.
        $this->get($response->headers->get('Location'))
        	 ->assertSee('Some Title')
        	 ->assertSee('Some Body');
    }

    /** @test */
    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
             ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
             ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_a_recaptcha_verification()
    {
        if (Recaptcha::isInTestMode()) {
            $this->markTestSkipped("Recaptcha is in test mode.");
        }

        unset(app()[Recaptcha::class]);

        $this->publishThread(['g-recaptcha-response' => 'test'])
             ->assertSessionHasErrors('g-recaptcha-response');
    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        factory(\App\Channel::class, 2)->create();

        $this->publishThread(['channel_id' => null])
             ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
             ->assertSessionHasErrors('channel_id');
    }

    /** @test */
    public function a_thread_requires_a_unique_slug()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['title' => 'Foo Title']);

        $this->assertEquals('foo-title', $thread->fresh()->slug);

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals('foo-title-' . $thread['id'], $thread['slug']);
    }

    /** @test */
    public function a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['title' => 'Some Title 24']);

        $thread = $this->postJson(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals('some-title-24-' . $thread['id'], $thread['slug']);
    }

    /** @test */
    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create(\App\Thread::class);

        $this->delete($thread->path())
             ->assertRedirect(route('login'));

        $this->signIn();
        $this->delete($thread->path())
             ->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create(\App\Thread::class, ['user_id' => auth()->id()]);
        $reply = create(\App\Reply::class, ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());
        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id])
             ->assertDatabaseMissing('replies', ['id' => $reply->id])
             ->assertDatabaseMissing('activities', [
                'subject_id' => $thread->id,
                'subject_type' => get_class($thread),
            ])
             ->assertDatabaseMissing('activities', [
                'subject_id' => $reply->id,
                'subject_type' => get_class($reply),
            ]);
    }

    /** @test */
    function a_new_thread_cannot_be_created_in_an_archived_channel()
    {
        $channel = create('App\Channel', ['archived' => true]);
        
        $channel->archive();

        $this->publishThread(['channel_id' => $channel->id])
            ->assertSessionHasErrors('channel_id');

        $this->assertEquals(0, $channel->threads);
    }

    public function publishThread($overrides = [])
    {
        $this->withExceptionHandling()
             ->signIn();

        $thread = make(\App\Thread::class, $overrides);

        return $this->post(route('threads'), $thread->toArray() + ['g-recaptcha-response' => 'token']);
    }

}