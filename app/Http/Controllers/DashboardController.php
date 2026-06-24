<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Support\CalendarMonth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $selectedDate    = $this->resolveSelectedDate($request);
        $displayDate     = $this->resolveDisplayMonth($request, $selectedDate);
        $selectedDateKey = $selectedDate->format('Y-m-d');

        $trainingLogs = $this->monthlyTrainingLogs($user, $displayDate);
        $mealLogs     = $this->monthlyMealLogs($user, $displayDate);

        $calendar = CalendarMonth::for(
            $displayDate,
            $this->dateKeys($trainingLogs, 'mussle_date'),
            $this->dateKeys($mealLogs, 'eaten_on'),
            $selectedDateKey
        );

        $todayLogs  = $this->logsOnDate($trainingLogs, 'mussle_date', $selectedDateKey);
        $todayMeals = $this->logsOnDate($mealLogs, 'eaten_on', $selectedDateKey);

        return view('dashboard', [
            'selectedDate'      => $selectedDate,
            'selectedDateKey'   => $selectedDateKey,
            'selectedDateLabel' => $selectedDate->format('n月j日').'（'.$this->weekdayLabel($selectedDate).'）',
            'displayYear'       => $displayDate->year,
            'displayMonth'      => $displayDate->month,
            'displayMonthLabel' => $displayDate->format('Y年 n月'),
            'weeks'             => $calendar->weeks(),
            'todayLogs'         => $todayLogs,
            'materials'         => $user->materials()->orderBy('title')->get(),
            'editLog'           => $this->resolveEditTarget($user->mussleLogs()->with('material'), $request, 'edit'),
            'todayMeals'        => $todayMeals,
            'mealSummary'       => $this->buildMealSummary($todayMeals),
            'foods'             => Food::orderBy('name')->get(),
            'editMeal'          => $this->resolveEditTarget($user->mealLogs()->with('food'), $request, 'editMeal'),
            'recentFreeNames'   => $this->recentFreeNames($user),
            'prevUrl'           => $this->monthUrl($selectedDateKey, $displayDate->copy()->subMonth()),
            'nextUrl'           => $this->monthUrl($selectedDateKey, $displayDate->copy()->addMonth()),
            'todayUrl'          => $this->monthUrl(today()->format('Y-m-d'), today()),
        ]);
    }

    private function monthlyTrainingLogs($user, Carbon $month): EloquentCollection
    {
        return $user->mussleLogs()
            ->with('material')
            ->whereBetween('mussle_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('id')
            ->get();
    }

    private function monthlyMealLogs($user, Carbon $month): EloquentCollection
    {
        return $user->mealLogs()
            ->with('food')
            ->whereBetween('eaten_on', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('id')
            ->get();
    }

    /**
     * ログ群から日付（Y-m-d）の重複なし一覧を取り出す
     *
     * @return array<int, string>
     */
    private function dateKeys(EloquentCollection $logs, string $column): array
    {
        return $logs
            ->pluck($column)
            ->map(fn (Carbon $date) => $date->format('Y-m-d'))
            ->unique()
            ->all();
    }

    /**
     * 指定日のログだけを抽出する
     */
    private function logsOnDate(EloquentCollection $logs, string $column, string $dateKey): EloquentCollection
    {
        return $logs
            ->filter(fn ($log) => $log->{$column}->format('Y-m-d') === $dateKey)
            ->values();
    }

    /**
     * 編集対象レコードを解決する（クエリパラメータがあれば検索）
     */
    private function resolveEditTarget($query, Request $request, string $key)
    {
        if (! $request->filled($key)) {
            return null;
        }

        return $query->find($request->integer($key));
    }

    /**
     * 当日食事ログから栄養合計を組み立てる
     *
     * @return array<string, int|float>
     */
    private function buildMealSummary(EloquentCollection $todayMeals): array
    {
        $counted = $todayMeals->filter(fn ($meal) => $meal->hasNutrition());

        return [
            'kcal'      => (int) $counted->sum('kcal'),
            'protein'   => round($counted->sum('protein'), 1),
            'fat'       => round($counted->sum('fat'), 1),
            'carbs'     => round($counted->sum('carbs'), 1),
            'uncounted' => $todayMeals->count() - $counted->count(),
        ];
    }

    /**
     * 自由入力名の入力補完候補（直近・重複なし）
     */
    private function recentFreeNames($user)
    {
        return $user->mealLogs()
            ->whereNotNull('food_name_free')
            ->latest('id')
            ->limit(30)
            ->pluck('food_name_free')
            ->unique()
            ->take(6)
            ->values();
    }

    private function monthUrl(string $dateKey, Carbon $month): string
    {
        return route('dashboard', [
            'date'  => $dateKey,
            'year'  => $month->year,
            'month' => $month->month,
        ]);
    }

    private function resolveSelectedDate(Request $request): Carbon
    {
        $date = $request->query('date');

        if (! $date) {
            return today();
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Exception) {
            return today();
        }
    }

    private function resolveDisplayMonth(Request $request, Carbon $fallback): Carbon
    {
        $year  = $request->integer('year',  $fallback->year);
        $month = $request->integer('month', $fallback->month);

        try {
            return Carbon::createFromDate($year, $month, 1)->startOfMonth();
        } catch (\Exception) {
            return $fallback->copy()->startOfMonth();
        }
    }

    private function weekdayLabel(Carbon $date): string
    {
        return ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
    }
}
