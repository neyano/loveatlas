<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * アバター画像を保存 (200x200 リサイズ)
     */
    public function storeAvatar(UploadedFile $file): string
    {
        return $this->store($file, 'avatars', 200);
    }

    /**
     * 一般画像を保存 (最大800px幅)
     */
    public function storeImage(UploadedFile $file, string $directory): string
    {
        return $this->store($file, $directory, 800);
    }

    /**
     * 画像を保存・リサイズ
     *
     * Intervention Image がインストールされている場合は WebP 変換+リサイズ。
     * 未インストールの場合はそのまま保存。
     */
    private function store(UploadedFile $file, string $directory, int $maxWidth): string
    {
        // Intervention Image が利用可能かチェック
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            return $this->storeWithIntervention($file, $directory, $maxWidth);
        }

        return $this->storeSimple($file, $directory);
    }

    /**
     * Intervention Image による高度な画像処理
     */
    private function storeWithIntervention(UploadedFile $file, string $directory, int $maxWidth): string
    {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $image = $manager->read($file->getPathname());
        $image->scaleDown(width: $maxWidth);

        $filename = Str::random(32) . '.webp';
        $path = "uploads/{$directory}/{$filename}";

        $fullPath = storage_path("app/public/{$path}");
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $image->toWebp(80)->save($fullPath);

        return $path;
    }

    /**
     * シンプルなファイル保存 (Intervention Image なし)
     */
    private function storeSimple(UploadedFile $file, string $directory): string
    {
        $filename = Str::random(32) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(
            "public/uploads/{$directory}",
            $filename
        );
    }
}
