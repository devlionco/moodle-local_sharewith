<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External interface library for customfields component
 *
 * @package   local_sharewith
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/local/sharewith/locallib.php");

/**
 * Class local_sharewith_external
 *
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_sharewith_external extends external_api {

    public static function add_sharewith_task_parameters() {
        return new external_function_parameters(

                array(
                        'sourcecourseid' => new external_value(PARAM_INT, 'sourcecourse id int', VALUE_DEFAULT, null),
                        'type' => new external_value(PARAM_TEXT, 'type text', VALUE_DEFAULT, null),
                        'categoryid' => new external_value(PARAM_INT, 'category id int', VALUE_DEFAULT, null),
                        'courseid' => new external_value(PARAM_INT, 'course id int', VALUE_DEFAULT, null),
                        'sectionid' => new external_value(PARAM_INT, 'section id int', VALUE_DEFAULT, null),
                        'sourcesectionid' => new external_value(PARAM_INT, 'sourcesection id int', VALUE_DEFAULT, null),
                        'sourceactivityid' => new external_value(PARAM_INT, 'sourceactivity id int', VALUE_DEFAULT, null),
                )
        );
    }

    public static function add_sharewith_task_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                )
        );
    }

    public static function add_sharewith_task($sourcecourseid, $type, $categoryid, $courseid, $sectionid, $sourcesectionid,
            $sourceactivityid) {
        global $USER, $sharingtypes;

        $result = array();

        // If type wrong.
        if (!in_array($type, $sharingtypes)) {
            $result['result'] = 0;
            return $result;
        }

        // Check settings parameters.
        switch ($type) {
            case 'coursecopy':
                if (!get_config('local_sharewith', 'coursecopy')) {
                    $result['result'] = 0;
                    return $result;
                }

                // Check capability for course.
                $context = \context_course::instance($sourcecourseid);
                if (!has_capability('moodle/course:update', $context)) {
                    $result['result'] = 0;
                    return $result;
                }
                break;
            case 'sectioncopy':
                if (!get_config('local_sharewith', 'sectioncopy')) {
                    $result['result'] = 0;
                    return $result;
                }
                break;
            case 'activityhimselfcopy':
                if (!get_config('local_sharewith', 'activityhimselfcopy')) {
                    $result['result'] = 0;
                    return $result;
                }
                break;

        }

        $bool = local_sharewith_add_task($type, $USER->id, $USER->id, $sourcecourseid, $courseid, $sourcesectionid,
                $sectionid, $categoryid, $sourceactivityid);

        $result['result'] = $bool;
        return $result;
    }

    public static function get_categories_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    public static function get_categories_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'categories' => new external_value(PARAM_TEXT, 'json categories'),
                )
        );
    }

    public static function get_categories() {
        $result = array();

        $categories = local_sharewith_get_categories();

        if (!empty($categories)) {
            $result['result'] = 1;
            $result['categories'] = json_encode($categories);
        } else {
            $result['result'] = 0;
            $result['categories'] = json_encode($categories);
        }

        return $result;
    }

    public static function get_courses_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    public static function get_courses_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'courses' => new external_value(PARAM_TEXT, 'json categories'),
                )
        );
    }

    public static function get_courses() {
        $result = array();

        $courses = local_sharewith_get_courses();

        if (!empty($courses)) {
            $result['result'] = 1;
            $result['courses'] = json_encode($courses);
        } else {
            $result['result'] = 0;
            $result['courses'] = json_encode($courses);
        }

        return $result;
    }

    public static function get_sections_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'result bool'),
                )
        );
    }

    public static function get_sections_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'sections' => new external_value(PARAM_TEXT, 'json categories'),
                )
        );
    }

    public static function get_sections($courseid) {
        $result = array();

        $sections = local_sharewith_get_section_bycourse($courseid);

        if (!empty($sections)) {
            $result['result'] = 1;
            $result['sections'] = json_encode($sections);
        } else {
            $result['result'] = 0;
            $result['sections'] = json_encode($sections);
        }

        return $result;
    }
}
