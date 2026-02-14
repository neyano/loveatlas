<?php

namespace App\Observers;

use App\Models\Vote;

class VoteObserver
{
    public function created(Vote $vote): void
    {
        $vote->quote()->increment('likes_count');
    }

    public function deleted(Vote $vote): void
    {
        $vote->quote()->decrement('likes_count');
    }
}
