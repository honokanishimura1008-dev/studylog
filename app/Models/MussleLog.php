<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MussleLog extends Model
{
    protected $fillable = [
        'user_id',
        'material_id',
        'mussle_date',
        'weight_kg',
        'reps',
        'sets',
        'memo',
        'minutes',
        'learned',
        'stuck',
    ];

    protected function casts(): array
    {
        return [
            'mussle_date' => 'date',
            'weight_kg' => 'float',
            'reps' => 'integer',
            'sets' => 'integer',
            'minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * 「60kg × 8回 × 3セット」形式の表示文字列を返す。
     * kg/reps/sets が未入力の旧レコードは分数表示にフォールバックする。
     */
    public function setSummary(): string
    {
        if ($this->reps && $this->sets) {
            $weight = $this->weight_kg
                ? rtrim(rtrim(number_format($this->weight_kg, 1), '0'), '.').'kg'
                : '自重';

            return "{$weight} × {$this->reps}回 × {$this->sets}セット";
        }

        if ($this->minutes) {
            return "{$this->minutes}分";
        }

        return '—';
    }
}
