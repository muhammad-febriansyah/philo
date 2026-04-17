<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'photo_count', 'print_size', 'price', 'is_active'])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'photo_count' => 'integer',
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class);
    }
}
