<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLikesCount extends Command
{
    protected $signature = 'sync:likes-count';

    protected $description = 'votes テーブルから quotes.likes_count を再計算して同期する';

    public function handle(): int
    {
        $updated = DB::statement('
            UPDATE quotes SET likes_count = (
                SELECT COUNT(*) FROM votes WHERE votes.quote_id = quotes.id
            )
        ');

        $this->info('likes_count を同期しました。');

        return Command::SUCCESS;
    }
}
