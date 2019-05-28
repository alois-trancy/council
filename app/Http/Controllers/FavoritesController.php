<?php

namespace App\Http\Controllers;

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

        return back();
    }

    public function destroy(Reply $reply)
    {
        $reply->unfavorite();
    }
}