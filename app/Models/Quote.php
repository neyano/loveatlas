<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_id',
        'location_id',
        'quote_text',
        'character_name',
        'scene_description',
        'episode_info',
        'language',
        'photo_path',
        'status',
        'likes_count',
    ];

    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'quote_tag');
    }

    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'votes')->withTimestamps();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // --- Scopes ---

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function scopeInBoundingBox(Builder $query, float $n, float $s, float $e, float $w): void
    {
        $query->whereHas('location', function ($q) use ($n, $s, $e, $w) {
            $q->whereBetween('latitude', [$s, $n])
              ->whereBetween('longitude', [$w, $e]);
        });
    }
}
