<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'thumbnail_path', 'frame_path', 'print_size', 'photo_slots', 'slot_positions', 'is_active'])]
class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'photo_slots' => 'integer',
            'slot_positions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function photoSessions(): HasMany
    {
        return $this->hasMany(PhotoSession::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }
}
