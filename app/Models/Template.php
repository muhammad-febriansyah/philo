<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'thumbnail_path', 'frame_path', 'print_size', 'photo_slots', 'slot_positions', 'is_active'])]
class Template extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateFactory> */
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
}
