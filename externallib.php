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
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/local/sharewith/locallib.php");

/**
 * Class local_sharewith_external
 *
 * @package   local_sharewith
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_sharewith_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
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
                'sourceuserid' => new external_value(PARAM_INT, 'sourceuserid id int', VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function add_sharewith_task_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
            )
        );
    }

    /**
     * @param $sourcecourseid
     * @param $type
     * @param $categoryid
     * @param $courseid
     * @param $sectionid
     * @param $sourcesectionid
     * @param $sourceactivityid
     * @param null $sourceuserid
     * @return array
     */
    public static function add_sharewith_task(
        $sourcecourseid,
        $type,
        $categoryid,
        $courseid,
        $sectionid,
        $sourcesectionid,
        $sourceactivityid,
        $sourceuserid=null
    ) {
        global $USER, $sharingtypes;

        $params = self::validate_parameters(
            self::add_sharewith_task_parameters(),
            array(
                'sourcecourseid' => $sourcecourseid,
                'type' => $type,
                'categoryid' => $categoryid,
                'courseid' => $courseid,
                'sectionid' => $sectionid,
                'sourcesectionid' => $sourcesectionid,
                'sourceactivityid' => $sourceactivityid,
                'sourceuserid' => $sourceuserid,
            )
        );

        $result = array();

        // If type wrong.
        if (!in_array($params['type'], $sharingtypes)) {
            $result['result'] = 0;
            return $result;
        }
        // Check settings parameters.
        switch ($params['type']) {
            case 'coursecopy':
                if (!get_config('local_sharewith', 'coursecopy')) {
                    $result['result'] = 1;
                    return $result;
                }
                break;
            case 'sectioncopy':
                if (!get_config('local_sharewith', 'sectioncopy')) {
                    $result['result'] = 2;
                    return $result;
                }
                break;
            case 'activitycopy':
                if (!get_config('local_sharewith', 'activitycopy')) {
                    $result['result'] = 3;
                    return $result;
                }
                break;
        }

        $bool = local_sharewith_add_task(
            $params['type'],
            $USER->id,
            is_null($sourceuserid) ? $USER->id : $sourceuserid,
            $params['sourcecourseid'],
            $params['courseid'],
            $params['sourcesectionid'],
            $params['sectionid'],
            $params['categoryid'],
            $params['sourceactivityid']
        );

        $result['result'] = $bool ? 10 : 4;
        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function add_saveactivity_task_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id int', VALUE_DEFAULT, null),
                'sectionid' => new external_value(PARAM_INT, 'section id int', VALUE_DEFAULT, null),
                'shareid' => new external_value(PARAM_INT, 'shareid int', VALUE_DEFAULT, null),
                'type' => new external_value(PARAM_TEXT, 'type text', VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function add_saveactivity_task_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'text' => new external_value(PARAM_TEXT, 'result text'),
            )
        );
    }

    /**
     * Add task for saving new activity
     * @param int $courseid
     * @param int $sectionid
     * @param int $shareid
     * @param string $type
     * @return string
     */
    public static function add_saveactivity_task($courseid, $sectionid, $shareid, $type) {
        global $USER, $sharingtypes;

        $params = self::validate_parameters(
            self::add_saveactivity_task_parameters(),
            array(
                'courseid' => $courseid,
                'sectionid' => $sectionid,
                'shareid' => $shareid,
                'type' => $type,
            )
        );

        $result = array();

        // If type wrong.
        if (!in_array($params['type'], $sharingtypes)) {
            $result['result'] = 0;
            $result['text'] = 'wrong type';
            return $result;
        }
        // Check settings parameters.
        switch ($params['type']) {
            case 'coursecopy':
                if (!get_config('local_sharewith', 'coursecopy')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t copy course';
                    return $result;
                }
                break;
            case 'sectioncopy':
                if (!get_config('local_sharewith', 'sectioncopy')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t copy section';
                    return $result;
                }
                break;
            case 'activityshare':
                if (!get_config('local_sharewith', 'activitysending')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t share activity';
                    return $result;
                }
                break;
        }

        $result = local_sharewith_save_task($params['type'], $params['shareid'], $params['courseid'], $params['sectionid']);

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_categories_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function get_categories_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'categories' => new external_value(PARAM_RAW, 'json categories'),
            )
        );
    }

    /**
     * Get categories
     * @return array
     */
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

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function get_courses_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'courses' => new external_value(PARAM_TEXT, 'json categories'),
            )
        );
    }

    /**
     * Get courses
     * @return array
     */
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

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_sections_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'result bool'),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function get_sections_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'sections' => new external_value(PARAM_TEXT, 'json categories'),
            )
        );
    }

    /**
     * Get sections
     * @param int $courseid
     * @return array
     */
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

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_teachers_parameters() {
        return new external_function_parameters(
            array(
                'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function get_teachers_returns() {
        return new external_value(PARAM_RAW, 'Teachers form');
    }

    /**
     * Get teachers list
     * @param int $activityid
     * @param int $courseid
     * @return array
     */
    public static function get_teachers($activityid, $courseid) {

        $params = self::validate_parameters(
            self::get_teachers_parameters(),
            array(
                'activityid' => $activityid,
                'courseid' => $courseid,
            )
        );

        $teachers = local_sharewith_get_teachers($params['activityid'], $params['courseid']);

        return $teachers;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function autocomplete_teachers_parameters() {
        return new external_function_parameters(
            array(
                'searchstring' => new external_value(PARAM_TEXT, 'Search string'),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function autocomplete_teachers_returns() {
        return new external_value(PARAM_RAW, 'Teachers list');
    }

    /**
     * @param $searchstring
     * @return string
     */
    public static function autocomplete_teachers($searchstring) {

        $params = self::validate_parameters(
            self::autocomplete_teachers_parameters(),
            array(
                'searchstring' => $searchstring,
            )
        );

        $teachers = local_sharewith_autocomplete_teachers($params['searchstring']);

        return $teachers;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function submit_teachers_parameters() {
        return new external_function_parameters(
            array(
                'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'teachersid' => new external_value(PARAM_RAW, 'Teachers ID'),
                'message' => new external_value(PARAM_TEXT, 'Message to teacher'),
            )
        );
    }

    /**
     * Returns result
     * @return result
     */
    public static function submit_teachers_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'Status type'),
                'message' => new external_value(PARAM_TEXT, 'Status message'),
            )
        );
    }

    /**
     * Submit activity to teachers
     * @param int $activityid
     * @param int $courseid
     * @param int $teachersid
     * @param string $message
     * @return array
     */
    public static function submit_teachers($activityid, $courseid, $teachersid, $message) {
        global $USER;

        $context = context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(
            self::submit_teachers_parameters(),
            array(
                'activityid' => $activityid,
                'courseid' => $courseid,
                'teachersid' => $teachersid,
                'message' => $message,
            )
        );

        $result = local_sharewith_submit_teachers(
            $params['activityid'],
            $params['courseid'],
            $params['teachersid'],
            $params['message']
        );

        return $result;
    }
}
