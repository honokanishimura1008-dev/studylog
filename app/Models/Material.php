<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    public const TYPES = [
        'legs' => '足',
        'shoulders_arms' => '肩/腕',
        'back' => '背中',
    ];

    /**
     * 種別ごとに選択できるカバー画像（public/images/materials/ 内のファイル名から拡張子を除いたもの）
     */
    public const COVER_IMAGES = [
        'legs' => [
            'アブダクション',
            'ケーブルヒップバック',
            'シーテッドレッグカール',
            'スモウスクワット',
            'ダンベルスクワット',
            'バーベルヒップスラスト',
            'ライイングレッグカール',
            'レッグプレス',
        ],
        'shoulders_arms' => [
            'アームカール',
            'インクラインダンベルカール',
            'ケーブルカール',
            'ケーブルトライプッシュダウン',
            'サイドレイズ',
            'ダンベルカール',
            'ダンベルショルダープレス',
            'ダンベルトライエクステンション',
        ],
        'back' => [
            'アシストチンアップ（マシン）',
            'マシンロウ',
            'ラットプルダウン',
        ],
    ];

    /**
     * @return array<int, string> 指定種別で選択可能な cover_path の一覧
     */
    public static function coverPathsForType(string $type): array
    {
        return array_map(
            fn (string $name) => 'images/materials/'.$name.'.png',
            self::COVER_IMAGES[$type] ?? [],
        );
    }

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'cover_path',
        'estimated_minutes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mussleLogs(): HasMany
    {
        return $this->hasMany(MussleLog::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    protected function progress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->estimated_minutes
                ? min(100, (int) round($this->mussleLogs()->sum('minutes') / $this->estimated_minutes * 100))
                : 0,
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(get: function () {
            $total = (int) $this->mussleLogs()->sum('minutes');

            if ($total === 0) {
                return '未実施';
            }

            if ($this->estimated_minutes && $total >= $this->estimated_minutes) {
                return '達成';
            }

            return '継続中';
        });
    }
}
