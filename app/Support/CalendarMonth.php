<?php

namespace App\Support;

use Carbon\Carbon;

class CalendarMonth
{
    private function __construct(
        private readonly Carbon $displayDate,
        private readonly array $trainingDates,
        private readonly array $mealDates,
        private readonly string $selectedDateKey,
    ) {}

    public static function for(
        Carbon $displayDate,
        array $trainingDates,
        array $mealDates,
        string $selectedDateKey,
    ): self {
        return new self($displayDate, $trainingDates, $mealDates, $selectedDateKey);
    }

    public function weeks(): array
    {
        $start   = $this->displayDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end     = $this->displayDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $month   = $this->displayDate->month;
        $weeks   = [];
        $current = $start->copy();

        while ($current <= $end) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $this->buildDay($current, $month);
                $current->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function buildDay(Carbon $date, int $month): array
    {
        $dateKey = $date->format('Y-m-d');

        return [
            'day'         => $date->day,
            'date'        => $dateKey,
            'inMonth'     => $date->month === $month,
            'isToday'     => $date->isToday(),
            'isSelected'  => $dateKey === $this->selectedDateKey,
            'hasTraining' => in_array($dateKey, $this->trainingDates, true),
            'hasMeal'     => in_array($dateKey, $this->mealDates, true),
            'selectUrl'   => route('dashboard', [
                'date'  => $dateKey,
                'year'  => $this->displayDate->year,
                'month' => $this->displayDate->month,
            ]),
        ];
    }
}
