<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'country',
        'region',
        'city',
        'address',
        'place_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
