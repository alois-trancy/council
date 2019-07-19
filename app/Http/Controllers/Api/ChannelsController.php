<?php

namespace App\Http\Controllers\api;

use App\Channel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChannelsController extends Controller
{
    /**
     * Fetch all channels.
     */
    public function index()
    {
        return cache()->rememberForever('channels', function () {
            return Channel::all();
        });
    }
}
