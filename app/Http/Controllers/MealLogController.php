<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMealLogRequest;
use App\Models\Food;
use App\Models\MealLog;
use Illuminate\Http\RedirectResponse;

class MealLogController extends Controller
{
    public function store(StoreMealLogRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // マスタ選択モードは複数行をまとめて登録できる
        if ($validated['mode'] === 'master') {
            foreach ($validated['items'] as $item) {
                $user->mealLogs()->create(
                    $this->masterData((int) $item['food_id'], (int) $item['amount_g'], $validated)
                );
            }

            $count = count($validated['items']);

            return $this->redirectToDayModal($validated['eaten_on'], "食事を{$count}件記録しました");
        }

        // 自由記述モードも複数行をまとめて登録できる
        if ($validated['mode'] === 'free') {
            foreach ($validated['free_items'] as $item) {
                $user->mealLogs()->create([
                    'meal_type' => $validated['meal_type'],
                    'eaten_on' => $validated['eaten_on'],
                    'food_id' => null,
                    'food_name_free' => $item['food_name_free'],
                    'amount_g' => null,
                    'kcal' => $item['kcal'] ?? null,
                    'protein' => $item['protein'] ?? null,
                    'fat' => $item['fat'] ?? null,
                    'carbs' => $item['carbs'] ?? null,
                ]);
            }

            $count = count($validated['free_items']);

            return $this->redirectToDayModal($validated['eaten_on'], "食事を{$count}件記録しました");
        }

        // クイック記録は名前だけ1件
        $user->mealLogs()->create($this->freeData($validated));

        return $this->redirectToDayModal($validated['eaten_on'], '食事を記録しました');
    }

    public function update(StoreMealLogRequest $request, MealLog $mealLog): RedirectResponse
    {
        abort_if($mealLog->user_id !== $request->user()->id, 403);

        $validated = $request->validated();
        $user = $request->user();

        $data = $validated['mode'] === 'master'
            ? $this->masterData((int) $validated['food_id'], (int) $validated['amount_g'], $validated)
            : $this->freeData($validated);

        $mealLog->update($data);

        // 編集フォームで増やした行は新しい記録として追加する
        $extra = 0;

        if ($validated['mode'] === 'master') {
            foreach ($validated['items'] ?? [] as $item) {
                $user->mealLogs()->create(
                    $this->masterData((int) $item['food_id'], (int) $item['amount_g'], $validated)
                );
                $extra++;
            }
        }

        if ($validated['mode'] === 'free') {
            foreach ($validated['free_items'] ?? [] as $item) {
                $user->mealLogs()->create([
                    'meal_type' => $validated['meal_type'],
                    'eaten_on' => $validated['eaten_on'],
                    'food_id' => null,
                    'food_name_free' => $item['food_name_free'],
                    'amount_g' => null,
                    'kcal' => $item['kcal'] ?? null,
                    'protein' => $item['protein'] ?? null,
                    'fat' => $item['fat'] ?? null,
                    'carbs' => $item['carbs'] ?? null,
                ]);
                $extra++;
            }
        }

        $message = $extra > 0
            ? "食事記録を更新し、{$extra}件追加しました"
            : '食事記録を更新しました';

        return $this->redirectToDayModal($validated['eaten_on'], $message);
    }

    public function destroy(MealLog $mealLog): RedirectResponse
    {
        abort_if($mealLog->user_id !== auth()->id(), 403);

        $date = $mealLog->eaten_on->format('Y-m-d');
        $mealLog->delete();

        // 履歴ページから削除した場合は元のページに戻る
        if (str_contains(url()->previous(), route('mussle-log.index'))) {
            return back()->with('status', '食事記録を削除しました');
        }

        return $this->redirectToDayModal($date, '食事記録を削除しました');
    }

    /**
     * マスタ選択モードの保存データ。
     * 栄養値は per 100g × 量 で自動算出（FR-02-4）
     *
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function masterData(int $foodId, int $amountG, array $validated): array
    {
        $food = Food::findOrFail($foodId);
        $ratio = $amountG / 100;

        return [
            'meal_type' => $validated['meal_type'],
            'eaten_on' => $validated['eaten_on'],
            'food_id' => $food->id,
            'food_name_free' => null,
            'amount_g' => $amountG,
            'kcal' => (int) round($food->kcal * $ratio),
            'protein' => round($food->protein * $ratio, 1),
            'fat' => round($food->fat * $ratio, 1),
            'carbs' => round($food->carbs * $ratio, 1),
        ];
    }

    /**
     * 自由記述 / クイック記録モードの保存データ。
     * free:  栄養値は分かる範囲で手入力（空欄=未計上）
     * quick: 名前だけのクイック記録。栄養値なし
     *
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function freeData(array $validated): array
    {
        $isQuick = $validated['mode'] === 'quick';

        return [
            'meal_type' => $validated['meal_type'],
            'eaten_on' => $validated['eaten_on'],
            'food_id' => null,
            'food_name_free' => $validated['food_name_free'],
            'amount_g' => null,
            'kcal' => $isQuick ? null : ($validated['kcal'] ?? null),
            'protein' => $isQuick ? null : ($validated['protein'] ?? null),
            'fat' => $isQuick ? null : ($validated['fat'] ?? null),
            'carbs' => $isQuick ? null : ($validated['carbs'] ?? null),
        ];
    }

    private function redirectToDayModal(string $date, string $message): RedirectResponse
    {
        return redirect()
            ->route('dashboard', ['date' => $date, 'modal' => 1, 'tab' => 'meal'])
            ->with('status', $message);
    }
}
