<?php

namespace Tests\Unit;

use App\Models\WeeklyMenu;
use PHPUnit\Framework\TestCase;

class WeeklyMenuTest extends TestCase
{
    public function test_parse_reps_range(): void
    {
        $result = WeeklyMenu::parseReps('8-12');

        $this->assertSame(8, $result['min']);
        $this->assertSame(12, $result['max']);
    }

    public function test_parse_reps_single_value(): void
    {
        $result = WeeklyMenu::parseReps('10');

        $this->assertSame(10, $result['min']);
        $this->assertSame(10, $result['max']);
    }

    public function test_parse_reps_with_whitespace(): void
    {
        $result = WeeklyMenu::parseReps(' 6-8 ');

        $this->assertSame(6, $result['min']);
        $this->assertSame(8, $result['max']);
    }

    public function test_sets_rep_display_range(): void
    {
        $menu = new WeeklyMenu([
            'sets' => 4,
            'rep_min' => 8,
            'rep_max' => 12,
        ]);

        $this->assertSame('4 × 8-12', $menu->setsRepDisplay());
    }

    public function test_sets_rep_display_single(): void
    {
        $menu = new WeeklyMenu([
            'sets' => 3,
            'rep_min' => 10,
            'rep_max' => 10,
        ]);

        $this->assertSame('3 × 10', $menu->setsRepDisplay());
    }
}
