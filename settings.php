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

/**
 * Settings. Core includes this file from the Grade penalties category, only while the plugin is enabled.
 *
 * @package    gradepenalty_cap
 * @copyright  2026 DevLMS
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('gradepenalty_cap', new lang_string('pluginname', 'gradepenalty_cap'), 'moodle/grade:manage');

$page->add(new admin_setting_configtext(
    'gradepenalty_cap/passpercent',
    new lang_string('passpercent', 'gradepenalty_cap'),
    new lang_string('passpercent_desc', 'gradepenalty_cap'),
    40,
    PARAM_FLOAT
));
$page->add(new admin_setting_configtext(
    'gradepenalty_cap/windowdays',
    new lang_string('windowdays', 'gradepenalty_cap'),
    new lang_string('windowdays_desc', 'gradepenalty_cap'),
    5,
    PARAM_INT
));
$page->add(new admin_setting_configcheckbox(
    'gradepenalty_cap/countweekends',
    new lang_string('countweekends', 'gradepenalty_cap'),
    new lang_string('countweekends_desc', 'gradepenalty_cap'),
    0
));
$page->add(new admin_setting_configselect(
    'gradepenalty_cap/afterwindow',
    new lang_string('afterwindow', 'gradepenalty_cap'),
    new lang_string('afterwindow_desc', 'gradepenalty_cap'),
    'minimum',
    [
        'minimum' => new lang_string('afterwindow_minimum', 'gradepenalty_cap'),
        'cap' => new lang_string('afterwindow_cap', 'gradepenalty_cap'),
    ]
));

$ADMIN->add('gradepenalty', $page);
