<?php

namespace App\Http\Controllers;

use App\Reputation;
use App\Favorite;
use App\Reply;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
	public function __construct()
	{
		return $this->middleware('auth');
	}

    public function store(Reply $reply)
    {
    	// Favorite::create([
    	// 	'user_id' => auth()->id(),
    	// 	'favorited_id' => $reply->id,
    	// 	'favorited_type' => get_class($reply),
    	// ]);
    	
    	$reply->favorite();

        Reputation::gain($reply->owner, Reputation::REPLY_FAVORITED);

        return back();
    }

    public function destroy(Reply $reply)
    {
        $reply->unfavorite();

        Reputation::lose($reply->owner, Reputation::REPLY_FAVORITED);
    }
}