<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyLog extends Model
{
    protected $fillable = [
        'material_id',
        'studied_on',
        'minutes',
        'memo',
    ];

    protected $casts = [
        'studied_on' => 'date',
        'minutes' => 'integer',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
