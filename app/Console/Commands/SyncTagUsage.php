<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTagUsage extends Command
{
    protected $signature = 'sync:tag-usage';

    protected $description = 'quote_tag テーブルから tags.usage_count を再計算して同期する';

    public function handle(): int
    {
        DB::statement('
            UPDATE tags SET usage_count = (
                SELECT COUNT(*) FROM quote_tag WHERE quote_tag.tag_id = tags.id
            )
        ');

        $this->info('usage_count を同期しました。');

        return Command::SUCCESS;
    }
}
