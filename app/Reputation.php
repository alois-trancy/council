<?php

namespace App;

class Reputation
{
    const THREAD_WAS_PUBLISHED = 10;
    const REPLY_POSTED = 2;
    const BEST_REPLY_AWARDED = 50;
    const REPLY_FAVORITED = 5;

    public static function gain($user, $points)
    {
        $user->increment('reputation', $points);
    }

    public static function lose($user, $points)
    {
        $user->decrement('reputation', $points);
    }
}
