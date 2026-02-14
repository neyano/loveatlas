<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => '道後温泉本館', 'latitude' => 33.85167, 'longitude' => 132.78556, 'country' => '日本', 'region' => '愛媛県', 'city' => '松山市', 'address' => '愛媛県松山市道後湯之町5-6', 'description' => '「千と千尋の神隠し」の油屋のモデルとされる温泉。'],
            ['name' => '屋久島・白谷雲水峡', 'latitude' => 30.36667, 'longitude' => 130.52500, 'country' => '日本', 'region' => '鹿児島県', 'city' => '屋久島町', 'address' => '鹿児島県熊毛郡屋久島町', 'description' => '「もののけ姫」の森のモデルとなった苔むす原生林。'],
            ['name' => '聖蹟桜ヶ丘駅周辺', 'latitude' => 35.65028, 'longitude' => 139.44639, 'country' => '日本', 'region' => '東京都', 'city' => '多摩市', 'address' => '東京都多摩市関戸', 'description' => '「耳をすませば」の舞台のモデルとなった街。'],
            ['name' => '飛騨古川駅', 'latitude' => 36.23528, 'longitude' => 137.18639, 'country' => '日本', 'region' => '岐阜県', 'city' => '飛騨市', 'address' => '岐阜県飛騨市古川町金森町', 'description' => '「君の名は。」で瀧が糸守町を探しに降り立った駅。'],
            ['name' => '須賀神社', 'latitude' => 35.68750, 'longitude' => 139.71944, 'country' => '日本', 'region' => '東京都', 'city' => '新宿区', 'address' => '東京都新宿区須賀町5', 'description' => '「君の名は。」のラストシーンの階段がある神社。'],
            ['name' => '田端駅南口', 'latitude' => 35.73806, 'longitude' => 139.76083, 'country' => '日本', 'region' => '東京都', 'city' => '北区', 'address' => '東京都北区東田端1丁目', 'description' => '「天気の子」で帆高が陽菜と出会うシーンの近くの駅。'],
            ['name' => '日南海岸', 'latitude' => 31.58333, 'longitude' => 131.43333, 'country' => '日本', 'region' => '宮崎県', 'city' => '日南市', 'address' => '宮崎県日南市', 'description' => '「すずめの戸締まり」の冒頭で登場する宮崎の海岸。'],
            ['name' => 'スペイン広場', 'latitude' => 41.90583, 'longitude' => 12.48222, 'country' => 'イタリア', 'region' => 'ラツィオ州', 'city' => 'ローマ', 'address' => 'Piazza di Spagna, Roma', 'description' => '「ローマの休日」でアン王女がジェラートを食べた名所。'],
            ['name' => 'カフェ・デ・ドゥ・ムーラン', 'latitude' => 48.88444, 'longitude' => 2.33417, 'country' => 'フランス', 'region' => 'イル＝ド＝フランス', 'city' => 'パリ', 'address' => '15 Rue Lepic, 75018 Paris', 'description' => '映画「アメリ」でアメリが働くカフェのモデル。'],
            ['name' => '鎌倉高校前駅', 'latitude' => 35.30417, 'longitude' => 139.49750, 'country' => '日本', 'region' => '神奈川県', 'city' => '鎌倉市', 'address' => '神奈川県鎌倉市腰越1丁目', 'description' => '「SLAM DUNK」のオープニングに登場する踏切がある駅。'],
            ['name' => '神田明神', 'latitude' => 35.70194, 'longitude' => 139.76806, 'country' => '日本', 'region' => '東京都', 'city' => '千代田区', 'address' => '東京都千代田区外神田2-16-2', 'description' => '「ラブライブ!」の聖地として有名な神社。'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
