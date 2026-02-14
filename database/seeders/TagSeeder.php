<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // genre
            ['name' => 'ファンタジー', 'slug' => 'fantasy', 'category' => 'genre'],
            ['name' => 'ロマンス', 'slug' => 'romance', 'category' => 'genre'],
            ['name' => 'アクション', 'slug' => 'action', 'category' => 'genre'],
            ['name' => 'コメディ', 'slug' => 'comedy', 'category' => 'genre'],
            ['name' => 'ドラマ', 'slug' => 'drama', 'category' => 'genre'],
            ['name' => 'スポーツ', 'slug' => 'sports', 'category' => 'genre'],
            // mood
            ['name' => '感動', 'slug' => 'emotional', 'category' => 'mood'],
            ['name' => '切ない', 'slug' => 'melancholy', 'category' => 'mood'],
            ['name' => '勇気', 'slug' => 'courage', 'category' => 'mood'],
            ['name' => '笑い', 'slug' => 'funny', 'category' => 'mood'],
            ['name' => '癒し', 'slug' => 'healing', 'category' => 'mood'],
            // theme
            ['name' => '友情', 'slug' => 'friendship', 'category' => 'theme'],
            ['name' => '恋愛', 'slug' => 'love', 'category' => 'theme'],
            ['name' => '成長', 'slug' => 'growth', 'category' => 'theme'],
            ['name' => '家族', 'slug' => 'family', 'category' => 'theme'],
            ['name' => '自然', 'slug' => 'nature', 'category' => 'theme'],
            // scene_type
            ['name' => '名シーン', 'slug' => 'iconic-scene', 'category' => 'scene_type'],
            ['name' => 'クライマックス', 'slug' => 'climax', 'category' => 'scene_type'],
            ['name' => 'オープニング', 'slug' => 'opening', 'category' => 'scene_type'],
            ['name' => 'エンディング', 'slug' => 'ending', 'category' => 'scene_type'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
