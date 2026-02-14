<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();

        $quotes = [
            // 千と千尋の神隠し - 道後温泉
            ['work_id' => 1, 'location_id' => 1, 'quote_text' => 'ここで働かせてください！', 'character_name' => '荻野千尋', 'scene_description' => '湯婆婆に働かせてほしいと懇願するシーン', 'tags' => ['感動', '成長', '名シーン']],
            ['work_id' => 1, 'location_id' => 1, 'quote_text' => '一度あったことは忘れないものさ。思い出せないだけで。', 'character_name' => '銭婆', 'scene_description' => '千尋に語りかける銭婆のセリフ', 'tags' => ['感動', '癒し']],
            // もののけ姫 - 屋久島
            ['work_id' => 2, 'location_id' => 2, 'quote_text' => '生きろ。', 'character_name' => 'アシタカ', 'scene_description' => 'サンに向けて放つ力強い一言', 'tags' => ['勇気', '名シーン', '自然']],
            ['work_id' => 2, 'location_id' => 2, 'quote_text' => '黙れ小僧！お前にサンが救えるか！', 'character_name' => 'モロの君', 'scene_description' => 'アシタカに対するモロの怒り', 'tags' => ['名シーン']],
            // 耳をすませば - 聖蹟桜ヶ丘
            ['work_id' => 3, 'location_id' => 3, 'quote_text' => '自分の中にはまだ原石があるだけ。それを関しては見つけていかないと。', 'character_name' => '月島雫', 'scene_description' => '自分の才能について悩む雫', 'tags' => ['成長', '感動']],
            // 君の名は。- 飛騨古川
            ['work_id' => 4, 'location_id' => 4, 'quote_text' => 'まだ会ったことのない君を、探している。', 'character_name' => '立花瀧', 'scene_description' => '糸守町を探し飛騨を訪れるシーン', 'tags' => ['恋愛', '切ない', '名シーン']],
            // 君の名は。- 須賀神社
            ['work_id' => 4, 'location_id' => 5, 'quote_text' => '君の名前は？', 'character_name' => '立花瀧 / 宮水三葉', 'scene_description' => 'ラストシーン、階段で再会する二人', 'tags' => ['恋愛', '感動', 'エンディング', '名シーン']],
            // 天気の子 - 田端
            ['work_id' => 5, 'location_id' => 6, 'quote_text' => '天気なんて、狂ったままでいいんだ。', 'character_name' => '森嶋帆高', 'scene_description' => '陽菜を選ぶ帆高の決意', 'tags' => ['恋愛', '勇気', 'クライマックス']],
            // すずめの戸締まり - 宮崎
            ['work_id' => 6, 'location_id' => 7, 'quote_text' => '行ってきます。', 'character_name' => '岩戸鈴芽', 'scene_description' => '旅立ちのシーン', 'tags' => ['成長', 'オープニング']],
            // ローマの休日 - スペイン広場
            ['work_id' => 7, 'location_id' => 8, 'quote_text' => 'ローマです。断然ローマ。この街の思い出をいつまでも大切にしたいと思います。', 'character_name' => 'アン王女', 'scene_description' => '記者会見で一番印象に残った都市を聞かれて', 'tags' => ['ロマンス', '感動', '名シーン']],
            // アメリ - モンマルトル
            ['work_id' => 8, 'location_id' => 9, 'quote_text' => '他人の人生に手を出すのはやめて、自分の人生を生きなさい。', 'character_name' => 'ガラス男', 'scene_description' => 'アメリに人生のアドバイスをするシーン', 'tags' => ['成長', '癒し']],
            // SLAM DUNK - 鎌倉高校前
            ['work_id' => 9, 'location_id' => 10, 'quote_text' => '諦めたらそこで試合終了ですよ。', 'character_name' => '安西先生', 'scene_description' => '三井寿に語りかける名シーン', 'tags' => ['スポーツ', '勇気', '名シーン']],
            // ラブライブ! - 神田明神
            ['work_id' => 10, 'location_id' => 11, 'quote_text' => 'みんなで叶える物語。', 'character_name' => 'μ\'s', 'scene_description' => 'スクールアイドルとしての決意', 'tags' => ['友情', '感動']],
        ];

        foreach ($quotes as $quoteData) {
            $tagNames = $quoteData['tags'];
            unset($quoteData['tags']);

            $quote = Quote::create(array_merge($quoteData, [
                'user_id' => $admin->id,
                'language' => 'ja',
                'status' => 'approved',
            ]));

            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
            $quote->tags()->attach($tagIds);
        }
    }
}
