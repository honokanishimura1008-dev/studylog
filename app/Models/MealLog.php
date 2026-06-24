<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    /**
     * 食事の区分。
     * 選択肢・バリデーション・表示変換をここから一元参照する。
     */
    public const MEAL_TYPES = [
        'morning' => '朝',
        'lunch' => '昼',
        'dinner' => '夜',
        'snack' => '間食',
    ];

    protected $fillable = [
        'user_id',
        'food_id',
        'food_name_free',
        'meal_type',
        'eaten_on',
        'amount_g',
        'kcal',
        'protein',
        'fat',
        'carbs',
    ];

    protected function casts(): array
    {
        return [
            'eaten_on' => 'date',
            'amount_g' => 'integer',
            'kcal' => 'integer',
            'protein' => 'float',
            'fat' => 'float',
            'carbs' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /** マスタ名 or 自由記述名を返す */
    public function displayName(): string
    {
        return $this->food?->name ?? $this->food_name_free ?? '—';
    }

    public function mealTypeLabel(): string
    {
        return self::MEAL_TYPES[$this->meal_type] ?? $this->meal_type;
    }

    /** マスタ参照ではない（自由記述 or クイック）か */
    public function isFree(): bool
    {
        return $this->food_id === null;
    }

    /** 名前だけのクイック記録か（栄養値が一切ない自由記述） */
    public function isQuick(): bool
    {
        return $this->isFree()
            && $this->kcal === null
            && $this->protein === null
            && $this->fat === null
            && $this->carbs === null;
    }

    /** 集計に計上できる栄養値を持っているか */
    public function hasNutrition(): bool
    {
        return $this->kcal !== null;
    }
}
