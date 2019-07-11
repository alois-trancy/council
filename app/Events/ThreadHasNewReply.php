<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class ThreadHasNewReply
{
    public $thread;
    public $reply;

    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($thread, $reply)
    {
        //
        $this->thread = $thread;
        $this->reply = $reply;
    }
}
