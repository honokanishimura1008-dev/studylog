<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\User;
use App\Models\WeeklyMenu;
use Illuminate\Database\Seeder;

class WeeklyMenuSeeder extends Seeder
{
    /**
     * 週間メニューの初期データ（種目マスタから material を紐づけて登録）
     */
    public function run(): void
    {
        User::all()->each(function (User $user) {
            if ($user->weeklyMenus()->exists()) {
                return;
            }

            $this->seedForUser($user);
        });
    }

    private function seedForUser(User $user): void
    {
        // 種目マスタ（COVER_IMAGES）から material を用意
        $materials = [];
        foreach (Material::COVER_IMAGES as $type => $names) {
            foreach ($names as $name) {
                $materials[$name] = Material::firstOrCreate(
                    ['user_id' => $user->id, 'title' => $name],
                    [
                        'type' => $type,
                        'cover_path' => 'images/materials/'.$name.'.png',
                        'estimated_minutes' => 60,
                    ]
                );
            }
        }

        $program = [
            'mon' => [
                ['バーベルヒップスラスト', 1, 4, 6, 8, '週の最重量。RIRほぼ0まで'],
                ['スモウスクワット', 2, 4, 8, 8, null],
                ['ケーブルヒップバック', 3, 3, 12, 12, '片側ずつ'],
                ['アブダクション', 4, 3, 15, 20, null],
                ['ライイングレッグカール', 5, 3, 10, 10, null],
            ],
            'tue' => [
                ['ダンベルショルダープレス', 1, 4, 6, 10, null],
                ['サイドレイズ', 2, 4, 12, 15, '三角筋中部'],
                ['ケーブルトライプッシュダウン', 3, 3, 10, 12, null],
                ['ダンベルトライエクステンション', 4, 3, 10, 10, null],
                ['インクラインダンベルカール', 5, 3, 10, 10, null],
                ['ケーブルカール', 6, 3, 12, 15, null],
            ],
            'thu' => [
                ['レッグプレス', 1, 4, 10, 10, '足を高くワイドに置く'],
                ['ダンベルスクワット', 2, 3, 10, 12, null],
                ['シーテッドレッグカール', 3, 4, 10, 10, null],
                ['ケーブルヒップバック', 4, 3, 15, 15, '片側ずつ'],
                ['アブダクション', 5, 3, 20, 20, null],
            ],
            'fri' => [
                ['アシストチンアップ（マシン）', 1, 4, 6, 8, null],
                ['ラットプルダウン', 2, 3, 8, 10, null],
                ['マシンロウ', 3, 4, 10, 10, null],
                ['ダンベルカール', 4, 3, 10, 10, null],
                ['アームカール', 5, 3, 12, 12, null],
            ],
            'sat' => [
                ['バーベルヒップスラスト', 1, 4, 10, 12, '中重量でパンプ狙い'],
                ['スモウスクワット', 2, 3, 10, 10, null],
                ['アブダクション', 3, 3, 20, 20, null],
                ['ダンベルショルダープレス', 4, 3, 10, 12, null],
                ['サイドレイズ', 5, 4, 15, 20, null],
            ],
        ];

        foreach ($program as $dow => $items) {
            foreach ($items as [$name, $order, $sets, $repMin, $repMax, $memo]) {
                if (! isset($materials[$name])) {
                    continue;
                }

                WeeklyMenu::create([
                    'user_id' => $user->id,
                    'dow' => $dow,
                    'sort_order' => $order,
                    'material_id' => $materials[$name]->id,
                    'sets' => $sets,
                    'rep_min' => $repMin,
                    'rep_max' => $repMax,
                    'memo' => $memo,
                ]);
            }
        }
    }
}
