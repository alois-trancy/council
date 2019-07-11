<?php

namespace App\Http\Controllers;

use App\Thread;

class LockedThreadsController extends Controller
{
    public function store(Thread $thread)
    {
        // $thread->lock();
        $thread->update(['locked' => true]);
    }

    public function destroy(Thread $thread)
    {
        // $thread->unlock();
        $thread->update(['locked' => false]);
    }
}
