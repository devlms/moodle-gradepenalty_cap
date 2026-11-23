<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace gradepenalty_cap;

use core\plugininfo\gradepenalty;
use core_grades\penalty_manager;
use grade_item;

/**
 * Tests for the cap at the pass mark.
 *
 * @package    gradepenalty_cap
 * @category   test
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(penalty_calculator::class)]
final class penalty_calculator_test extends \advanced_testcase {
    /** @var int Monday 23 November 2026, 12:00 UTC. */
    private const DUE = 1795435200;

    /**
     * Grade an assignment, apply penalties and return the final grade.
     *
     * @param float $rawgrade Grade the teacher gave, out of 100.
     * @param int $submitted When the work was submitted.
     * @param int $due When it was due.
     * @param float|null $gradepass Grade to pass on the grade item.
     * @return float
     */
    private function penalised(float $rawgrade, int $submitted, int $due = self::DUE, ?float $gradepass = null): float {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        penalty_manager::enable_modules(['assign']);
        gradepenalty::enable_plugin('cap', true);

        grade_update('mod/assign', $course->id, 'mod', 'assign', $assign->id, 0, ['userid' => $user->id, 'rawgrade' => $rawgrade]);
        $item = grade_item::fetch([
            'courseid' => $course->id, 'itemtype' => 'mod', 'itemmodule' => 'assign',
            'iteminstance' => $assign->id, 'itemnumber' => 0,
        ]);
        if ($gradepass !== null) {
            $item->gradepass = $gradepass;
            $item->update();
        }
        penalty_manager::apply_grade_penalty_to_user($user->id, $item, $submitted, $due);
        return (float) $item->get_final($user->id)->finalgrade;
    }

    /**
     * Cases with the default settings: 40%, 5 working days, then the minimum grade.
     *
     * @return array
     */
    public static function default_settings_provider(): array {
        $due = self::DUE;
        return [
            'on time' => [80, $due, 80],
            'one hour late' => [80, $due + HOURSECS, 40],
            'late but already below the pass mark' => [30, $due + HOURSECS, 30],
            'friday, four working days late' => [80, $due + 4 * DAYSECS, 40],
            'next monday before noon, inside 5 working days' => [80, $due + 7 * DAYSECS - HOURSECS, 40],
            'next monday after noon, past 5 working days' => [80, $due + 7 * DAYSECS + HOURSECS, 0],
        ];
    }

    /**
     * Default settings.
     *
     * @param float $rawgrade
     * @param int $submitted
     * @param float $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('default_settings_provider')]
    public function test_default_settings(float $rawgrade, int $submitted, float $expected): void {
        $this->resetAfterTest();
        $this->setTimezone('UTC');
        $this->assertEquals($expected, $this->penalised($rawgrade, $submitted));
    }

    public function test_grade_to_pass_on_the_item_wins(): void {
        $this->resetAfterTest();
        $this->setTimezone('UTC');
        $this->assertEquals(50, $this->penalised(80, self::DUE + HOURSECS, self::DUE, 50));
    }

    public function test_site_pass_percentage(): void {
        $this->resetAfterTest();
        $this->setTimezone('UTC');
        set_config('passpercent', 50, 'gradepenalty_cap');
        $this->assertEquals(50, $this->penalised(80, self::DUE + HOURSECS));
    }

    public function test_weekends_count_when_set(): void {
        $this->resetAfterTest();
        $this->setTimezone('UTC');
        set_config('countweekends', 1, 'gradepenalty_cap');
        // Sunday is day 6 after the Monday deadline, past a 5 day window.
        $this->assertEquals(0, $this->penalised(80, self::DUE + 6 * DAYSECS));
    }

    public function test_keep_cap_after_the_window(): void {
        $this->resetAfterTest();
        $this->setTimezone('UTC');
        set_config('afterwindow', 'cap', 'gradepenalty_cap');
        $this->assertEquals(40, $this->penalised(80, self::DUE + 30 * DAYSECS));
    }

    public function test_no_due_date_means_no_penalty(): void {
        $this->resetAfterTest();
        $this->assertEquals(80, $this->penalised(80, self::DUE, 0));
    }
}
