<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use RecordsActivity, Searchable;
    // use RecordsVisits;

    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected $casts = [
        'locked' => 'boolean',
        'pinned' => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();

        // static::addGlobalScope('replyCount', function($builder) {
        //     $builder->withCount('replies');
        // });

        // withoutGlobalScope() can be used to not include the global scope while $with can't be disabled
        // static::addGlobalScope('creator', function($builder) {
        //     $builder->withCount('creator');
        // });

        static::deleting(function ($thread) {
            // $thread->replies()->delete();
            $thread->replies->each->delete();

            Reputation::lose($thread->creator, Reputation::THREAD_WAS_PUBLISHED);
        });

        static::created(function ($thread) {
            $thread->update(['slug' => $thread->title]);

            Reputation::gain($thread->creator, Reputation::THREAD_WAS_PUBLISHED);
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function bestReply()
    {
        return $this->hasOne(Reply::class, 'id', 'best_reply_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function addReply($reply)
    {
        // $reply = $this->replies()->create($reply);
        // $this->increment('replies_count');
        // return $reply;

        $reply = $this->replies()->create($reply);

        // Use events when there are many things to do after adding a reply
        // event(new ThreadHasNewReply($this, $reply));

        // $this->notifySubscribers($reply);

        event(new ThreadReceivedNewReply($reply));

        return $reply;
    }

    // public function lock()
    // {
    //     $this->update(['locked' => true]);
    // }

    // public function unlock()
    // {
    //     $this->update(['locked' => false]);
    // }

    // public function notifySubscribers($reply)
    // {
    //     $this->subscriptions
    //          ->where('user_id', '!=', $reply->user_id)
    //          ->each
    //          ->notify($reply);
    // }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id()
        ]);

        return $this;
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
             ->where('user_id', $userId ?: auth()->id())
             ->delete();
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
                    ->where('user_id', auth()->id())
                    ->exists();
    }

    public function hasUpdatesFor($user)
    {
        // Look in the cache for the proper key.
        // compare the carbon instance with the $thread->updated_at
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    // public function visits()
    // {
    //     return new Visits($this);
    // }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setSlugAttribute($value)
    {
        /*
        if (static::whereSlug($slug = str_slug($value))->exists()) {
            $slug = $this->incrementSlug($slug);
        }

        $this->attributes['slug'] = $slug;
        */

        /*
        $slug = str_slug($value);
        $original = $slug;
        $count  = 2;
        while (static::whereSlug($slug)->exists()) {
            $slug = "{$original}-" . $count++;
        }

        $this->attributes['slug'] = $slug;
        */

        $slug = str_slug($value);
        if (static::whereSlug($slug)->exists()) {
            $slug = $slug.'-'.$this->id;
        }
        $this->attributes['slug'] = $slug;
    }

    /*
    public function incrementSlug($slug, $count = 2)
    {
        // $max = static::whereTitle($this->title)->latest('id')->value('slug');

        // if (is_numeric($max[-1])) {
        //     return preg_replace_callback('/(\d+)$/', function ($matches) {
        //         return $matches[1] + 1;
        //     }, $max);
        // }

        // return "{$slug}-2";

        $original = $slug;
        while (static::whereSlug($slug)->exists()) {
            $slug = "{$original}-" . $count++;
        }

        return $slug;
    }
    */

    public function markBestReply(Reply $reply)
    {
        if ($this->hasBestReply()) {
            Reputation::lose($this->bestReply->owner, Reputation::BEST_REPLY_AWARDED);
        }

        $reply->thread->update(['best_reply_id' => $reply->id]);

        Reputation::gain($reply->owner, Reputation::BEST_REPLY_AWARDED);
    }

    public function hasBestReply()
    {
        return ! is_null($this->best_reply_id);
    }

    public function toSearchableArray()
    {
        return $this->toArray() + ['path' => $this->path()];
    }

    public function getBodyAttribute($body)
    {
        return \Purify::clean($body);
    }
}
