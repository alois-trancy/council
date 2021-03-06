<?php

namespace App\Listeners;

use App\User;
use App\Events\ThreadReceivedNewReply;
use App\Notifications\YouWereMentioned;

class NotifyMentionedUsers
{
    /**
     * Handle the event.
     *
     * @param  ThreadReceivedNewReply  $event
     * @return void
     */
    public function handle(ThreadReceivedNewReply $event)
    {
        // collect($event->reply->mentionedUsers())
        //     ->map(function ($name) use ($event) {
        //         return User::where('name', $name)->first();
        //     })
        //     ->filter()
        //     ->each(function ($user) use ($event) {
        //         $user->notify(new YouWereMentioned($event->reply));
        //     });

        User::whereIn('name', $event->reply->mentionedUsers())
            ->get()
            ->each(function ($user) use ($event) {
                $user->notify(new YouWereMentioned($event->reply));
            });
    }
}
