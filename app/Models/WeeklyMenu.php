<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyMenu extends Model
{
    public const DOWS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    /**
     * 曜日ごとの表示メタ情報（固定の週間プログラム構成）
     */
    public const DAY_META = [
        'mon' => [
            'label' => '月',
            'short' => '臀部・重量',
            'title' => '月曜｜臀部・重量日',
            'chips' => [['glute', '臀部']],
            'off' => false,
        ],
        'tue' => [
            'label' => '火',
            'short' => '肩・腕A',
            'title' => '火曜｜肩・腕A',
            'chips' => [['shoulder', '肩'], ['arm', '腕']],
            'off' => false,
        ],
        'wed' => [
            'label' => '水',
            'short' => 'オフ',
            'title' => '水曜｜オフ',
            'chips' => [['off', '休養']],
            'off' => true,
        ],
        'thu' => [
            'label' => '木',
            'short' => '脚・臀部Vol',
            'title' => '木曜｜脚・臀部ボリューム日',
            'chips' => [['leg', '脚'], ['glute', '臀部']],
            'off' => false,
        ],
        'fri' => [
            'label' => '金',
            'short' => '背中・二頭',
            'title' => '金曜｜背中・二頭',
            'chips' => [['back', '背中'], ['arm', '二頭']],
            'off' => false,
        ],
        'sat' => [
            'label' => '土',
            'short' => '臀部C・肩B',
            'title' => '土曜｜臀部C・肩B',
            'chips' => [['glute', '臀部'], ['shoulder', '肩']],
            'off' => false,
        ],
        'sun' => [
            'label' => '日',
            'short' => 'オフ',
            'title' => '日曜｜オフ',
            'chips' => [['off', '休養']],
            'off' => true,
        ],
    ];

    protected $fillable = [
        'user_id',
        'dow',
        'sort_order',
        'material_id',
        'sets',
        'rep_min',
        'rep_max',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'sets' => 'integer',
            'rep_min' => 'integer',
            'rep_max' => 'integer',
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

    /** レップ表示: 「6-8」または単一値なら「8」 */
    public function repDisplay(): string
    {
        return $this->rep_min === $this->rep_max
            ? (string) $this->rep_min
            : "{$this->rep_min}-{$this->rep_max}";
    }

    /** セット×レップ表示: 「4 × 6-8」 */
    public function setsRepDisplay(): string
    {
        return "{$this->sets} × {$this->repDisplay()}";
    }

    /**
     * レップ入力文字列を rep_min / rep_max に分解する。
     * 例: "6-8" → [6, 8]、 "10" → [10, 10]
     *
     * @return array{min: int, max: int}
     */
    public static function parseReps(string $input): array
    {
        $input = trim($input);

        if (str_contains($input, '-')) {
            [$min, $max] = array_map('intval', explode('-', $input, 2));

            return ['min' => $min, 'max' => $max];
        }

        $value = (int) $input;

        return ['min' => $value, 'max' => $value];
    }

    /** API・フロント用の配列 */
    public function toMenuArray(): array
    {
        $material = $this->material;

        return [
            'id' => $this->id,
            'dow' => $this->dow,
            'sort_order' => $this->sort_order,
            'material_id' => $this->material_id,
            'material_title' => $material->title,
            'cover_path' => $material->cover_path ? asset($material->cover_path) : null,
            'sets' => $this->sets,
            'rep_min' => $this->rep_min,
            'rep_max' => $this->rep_max,
            'rep_display' => $this->repDisplay(),
            'sets_rep_display' => $this->setsRepDisplay(),
            'memo' => $this->memo ?? '',
        ];
    }
}
