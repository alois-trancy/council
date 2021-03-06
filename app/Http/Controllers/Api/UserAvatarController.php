<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserAvatarController extends Controller
{
    public function store()
    {
        request()->validate([
            'avatar' => ['required', 'image'],
        ]);

        auth()->user()->update([
            // You can use storeAs() if you want a specific name for the file
            // If you use store() only, the filename will be hashed
            // request()->file('avatar')->storeAs('avatars', 'avatar.jpg, 'public')
            'avatar_path' => request()->file('avatar')->store('avatars', 'public')
        ]);

        // return back();

        return response([], 204);
    }
}
