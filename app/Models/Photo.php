<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['photo_session_id', 'original_path', 'order'])]
class Photo extends Model
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function photoSession(): BelongsTo
    {
        return $this->belongsTo(PhotoSession::class);
    }
}
