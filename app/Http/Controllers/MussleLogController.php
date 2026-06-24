<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMussleLogRequest;
use App\Models\MussleLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MussleLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');
        $tab = $request->query('tab', 'train');

        // ===== 食事タブ =====
        if ($tab === 'meal') {
            $query = $user->mealLogs()
                ->with('food')
                ->latest('eaten_on')
                ->latest('id');

            if ($filter === 'week') {
                $query->whereBetween('eaten_on', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
            }

            $mealLogs = $query->paginate(20)->withQueryString();

            return view('mussle-log.index', [
                'tab' => 'meal',
                'filter' => $filter,
                'mealLogs' => $mealLogs,
                // 日付ごとにグルーピングして表示する
                'mealsByDate' => $mealLogs->groupBy(fn ($meal) => $meal->eaten_on->format('Y-m-d')),
            ]);
        }

        // ===== 筋トレタブ =====
        $query = $user->mussleLogs()
            ->with('material')
            ->latest('mussle_date')
            ->latest('id');

        if ($filter === 'stuck') {
            $query->whereNotNull('stuck')->where('stuck', '!=', '');
        }

        if ($filter === 'week') {
            $query->whereBetween('mussle_date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]);
        }

        $studyLogs = $query->paginate(15)->withQueryString();

        return view('mussle-log.index', [
            'tab' => 'train',
            'filter' => $filter,
            'studyLogs' => $studyLogs,
        ]);
    }

    public function store(StoreMussleLogRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->mussleLogs()->create($validated);

        // 登録後はダッシュボードに戻し、同じ日付のモーダルを開いた状態にする
        return $this->redirectToDayModal($validated['mussle_date'], '記録を追加しました');
    }

    public function update(StoreMussleLogRequest $request, MussleLog $mussleLog): RedirectResponse
    {
        abort_if($mussleLog->user_id !== $request->user()->id, 403);

        $validated = $request->validated();

        $mussleLog->update($validated);

        return $this->redirectToDayModal($validated['mussle_date'], '記録を更新しました');
    }

    public function destroy(MussleLog $mussleLog): RedirectResponse
    {
        $user = auth()->user();

        abort_if($mussleLog->user_id !== $user->id, 403);

        $date = $mussleLog->mussle_date->format('Y-m-d');
        $mussleLog->delete();

        // 履歴ページから削除した場合は元のページに戻る
        if (str_contains(url()->previous(), route('mussle-log.index'))) {
            return back()->with('status', '記録を削除しました');
        }

        return $this->redirectToDayModal($date, '記録を削除しました');
    }

    private function redirectToDayModal(string $date, string $message): RedirectResponse
    {
        return redirect()
            ->route('dashboard', ['date' => $date, 'modal' => 1])
            ->with('status', $message);
    }

    /**
     * 種目別 重量推移マトリクス
     */
    public function matrix(Request $request): View
    {
        $user = $request->user();

        // 直近5ヶ月（今月を含む）を生成
        $months = collect(range(4, 0))->map(fn ($i) => now()->subMonths($i));
        $monthLabels = $months->map(fn ($m) => $m->format('n') . '月');

        // 重量記録がある種目を取得
        $materialsWithLogs = $user->materials()
            ->whereHas('mussleLogs', fn ($q) => $q->whereNotNull('weight_kg'))
            ->with(['mussleLogs' => fn ($q) => $q->whereNotNull('weight_kg')->orderBy('mussle_date')])
            ->get();

        $matrixData = $materialsWithLogs->map(function ($material) use ($months) {
            $monthlyMax = $months->map(function ($month) use ($material) {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $maxWeight = $material->mussleLogs
                    ->filter(fn ($log) => $log->mussle_date->between($start, $end))
                    ->max('weight_kg');

                return $maxWeight ?: null;
            });

            // 前月比の計算（今月 vs 先月）
            $current = $monthlyMax[4];
            $previous = $monthlyMax[3];
            $trend = null;
            $trendValue = null;

            if ($current !== null && $previous !== null) {
                $diff = $current - $previous;
                if ($diff > 0) {
                    $trend = 'up';
                    $trendValue = '+'.rtrim(rtrim(number_format($diff, 1), '0'), '.');
                } elseif ($diff < 0) {
                    $trend = 'down';
                    $trendValue = rtrim(rtrim(number_format($diff, 1), '0'), '.');
                } else {
                    $trend = 'flat';
                    $trendValue = '±0';
                }
            } elseif ($current !== null && $previous === null) {
                $trend = 'up';
                $trendValue = 'new';
            }

            // スパークラインデータ生成（有効な値のみ）
            $validValues = $monthlyMax->filter()->values();
            $sparklineSvg = null;
            if ($validValues->count() >= 2) {
                $sparklineSvg = $this->generateSparkline($validValues);
            }

            return [
                'material' => $material,
                'monthlyMax' => $monthlyMax,
                'current' => $current,
                'previous' => $previous,
                'trend' => $trend,
                'trendValue' => $trendValue,
                'sparklineSvg' => $sparklineSvg,
            ];
        })->filter(fn ($item) => $item['current'] !== null); // 今月データがある種目のみ表示

        return view('mussle-logs.matrix', [
            'months' => $months,
            'monthLabels' => $monthLabels,
            'matrixData' => $matrixData,
        ]);
    }

    /**
     * ミニスパークラインSVGを生成
     */
    private function generateSparkline($values): string
    {
        $count = $values->count();
        $min = $values->min();
        $max = $values->max();
        $range = $max - $min ?: 1;

        $width = 54;
        $height = 18;
        $padding = 2;

        $points = $values->map(function ($val, $i) use ($count, $min, $range, $width, $height, $padding) {
            $x = $count > 1 ? ($i / ($count - 1)) * $width : $width / 2;
            $y = $height - $padding - (($val - $min) / $range) * ($height - 2 * $padding);
            return sprintf('%.1f,%.1f', $x, $y);
        })->implode(' ');

        return $points;
    }
}
