<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Http\Requests\CreatePostRequest;
use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'index']);
    }

    public function index(Channel $channel, Thread $thread)
    {
        return $thread->replies()->paginate(20);
    }

    public function store(Channel $channel, Thread $thread, Request $request, CreatePostRequest $form)
    {
        if ($thread->locked) {
            return response('Thread is locked', 422);
        }

        return $thread->addReply([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ])->load('owner');

        // $form->persists($thread);

        // The following code was replaced by the form class
        /*
        if (\Gate::denies('create', new Reply)) {
            return response(
                'You are posting too frequently. Please take a break :)',
                429
            );
        }

        try {
            // Replaced by Gate::denies
            // $this->authorize('create', new Reply);

            $this->validate($request, [
                'body' => 'required|spamfree'
            ]);

            $reply = $thread->addReply([
                'body' => $request->body,
                'user_id' => auth()->id(),
            ]);

            return $reply->load('owner');

        } catch (\Exception $e) {
            return response(
                'Sorry, your reply could not be saved at this time.',
                422
            );
        }
        */
    }

    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        request()->validate([
            'body' => 'required|spamfree'
        ]);

        $reply->update([
            'body' => request('body'),
        ]);
    }

    public function destroy(Reply $reply)
    {
        // if ($reply->user_id != auth()->id()) {
        //     return response([], 403);
        // }

        $this->authorize('update', $reply);

        $reply->delete();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply deleted']);
        }

        return back();
    }
}
