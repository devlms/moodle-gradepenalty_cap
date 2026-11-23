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

use core_grades\penalty_container;

/**
 * Caps late work at the pass mark, then gives the minimum grade after the late window.
 *
 * @package    gradepenalty_cap
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class penalty_calculator extends \core_grades\penalty_calculator {
    /**
     * Work out the points to deduct and add them to the container.
     *
     * @param penalty_container $container
     */
    public static function calculate_penalty(penalty_container $container): void {
        $due = $container->get_due_date();
        $submitted = $container->get_submission_date();
        if ($due <= 0 || $submitted <= $due) {
            return;
        }

        // Work on the grade left after any plugin that ran before this one.
        $current = $container->get_grade_before_penalties() - $container->get_penalty();
        $min = (float) $container->get_min_grade();

        $inwindow = $submitted <= self::window_end($due);
        if (!$inwindow && get_config('gradepenalty_cap', 'afterwindow') !== 'cap') {
            $container->aggregate_penalty(max(0.0, $current - $min));
            return;
        }
        $container->aggregate_penalty(max(0.0, $current - self::pass_mark($container)));
    }

    /**
     * The pass mark: the grade item's "Grade to pass", else the site percentage of the grade range.
     *
     * @param penalty_container $container
     * @return float
     */
    public static function pass_mark(penalty_container $container): float {
        $gradepass = (float) $container->get_grade_item()->gradepass;
        if ($gradepass > 0) {
            return $gradepass;
        }
        $percent = get_config('gradepenalty_cap', 'passpercent');
        $percent = ($percent === false || $percent === '') ? 40.0 : (float) $percent;
        $min = (float) $container->get_min_grade();
        return $min + ($container->get_max_grade() - $min) * $percent / 100;
    }

    /**
     * When the late window closes: the due date plus N days, skipping Saturdays and Sundays unless weekends count.
     *
     * @param int $due
     * @return int
     */
    public static function window_end(int $due): int {
        $days = get_config('gradepenalty_cap', 'windowdays');
        $days = ($days === false || $days === '') ? 5 : (int) $days;
        $countweekends = (bool) get_config('gradepenalty_cap', 'countweekends');

        $end = (new \DateTimeImmutable('@' . $due))->setTimezone(\core_date::get_server_timezone_object());
        while ($days > 0) {
            $end = $end->modify('+1 day');
            if ($countweekends || (int) $end->format('N') < 6) {
                $days--;
            }
        }
        return $end->getTimestamp();
    }
}
