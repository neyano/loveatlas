<?php

namespace Database\Seeders;

use App\Models\Work;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    public function run(): void
    {
        $works = [
            ['title' => '千と千尋の神隠し', 'title_original' => 'Spirited Away', 'type' => 'anime', 'year' => 2001, 'country' => '日本', 'description' => '宮崎駿監督のスタジオジブリ作品。不思議な世界に迷い込んだ少女・千尋の成長物語。'],
            ['title' => 'もののけ姫', 'title_original' => 'Princess Mononoke', 'type' => 'anime', 'year' => 1997, 'country' => '日本', 'description' => '宮崎駿監督作品。人間と自然の対立を描いた壮大な物語。'],
            ['title' => '耳をすませば', 'title_original' => 'Whisper of the Heart', 'type' => 'anime', 'year' => 1995, 'country' => '日本', 'description' => '近藤喜文監督のスタジオジブリ作品。読書好きの少女・雫の青春物語。'],
            ['title' => '君の名は。', 'title_original' => 'Your Name.', 'type' => 'anime', 'year' => 2016, 'country' => '日本', 'description' => '新海誠監督作品。東京の少年と飛騨の少女が入れ替わる物語。'],
            ['title' => '天気の子', 'title_original' => 'Weathering with You', 'type' => 'anime', 'year' => 2019, 'country' => '日本', 'description' => '新海誠監督作品。天気を操る少女と家出少年の物語。'],
            ['title' => 'すずめの戸締まり', 'title_original' => 'Suzume', 'type' => 'anime', 'year' => 2022, 'country' => '日本', 'description' => '新海誠監督作品。災いの扉を閉める旅に出る少女の物語。'],
            ['title' => 'ローマの休日', 'title_original' => 'Roman Holiday', 'type' => 'movie', 'year' => 1953, 'country' => 'アメリカ', 'description' => 'ウィリアム・ワイラー監督作品。ローマを舞台にした王女と記者のラブストーリー。'],
            ['title' => 'アメリ', 'title_original' => 'Le Fabuleux Destin d\'Amélie Poulain', 'type' => 'movie', 'year' => 2001, 'country' => 'フランス', 'description' => 'ジャン＝ピエール・ジュネ監督作品。パリ・モンマルトルを舞台にした空想好きな少女の物語。'],
            ['title' => 'SLAM DUNK', 'title_original' => null, 'type' => 'anime', 'year' => 1993, 'country' => '日本', 'description' => '井上雄彦原作のバスケットボール漫画・アニメ。'],
            ['title' => 'ラブライブ!', 'title_original' => 'Love Live!', 'type' => 'anime', 'year' => 2013, 'country' => '日本', 'description' => '廃校を阻止するためにスクールアイドルを結成する少女たちの物語。'],
        ];

        foreach ($works as $work) {
            Work::create(array_merge($work, ['is_approved' => true]));
        }
    }
}
