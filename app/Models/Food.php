<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    /**
     * カテゴリーの定義
     * 選択肢の一覧・バリデーション・表示変換を全てこの1箇所から参照させ、
     * 追加・変更時にここだけ修正できる
     */
    public const CATEGORIES = [
        'staple' => '主食',
        'main' => '主菜',
        'side' => '副菜',
        'other' => 'その他',
    ];

    protected $fillable = [
        'name',
        'category',
        'emoji',
        'kcal',
        'protein',
        'fat',
        'carbs',
    ];

    protected function casts(): array
    {
        return [
            'kcal' => 'integer',
            'protein' => 'float',
            'fat' => 'float',
            'carbs' => 'float',
        ];
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
