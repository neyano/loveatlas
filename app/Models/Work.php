<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_original',
        'type',
        'year',
        'country',
        'description',
        'poster_path',
        'external_url',
        'submitted_by',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
