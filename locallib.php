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
 * Plugin general functions are defined here.
 *
 * @package     local_sharewith
 * @category    admin
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $sharingtypes;
$sharingtypes = array(
        'coursecopy',
        'sectioncopy',
        'activityhimselfcopy',
);

function local_sharewith_permission_allow($courseid, $userid) {

    if (has_capability('moodle/course:update', context_course::instance($courseid), $userid)) {
        return true;
    }

    return false;
}

function local_sharewith_add_task($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $sourcesectionid, $sectionid,
        $categoryid = null, $sourceactivityid = null, $metadata = null) {
    global $DB, $USER, $COURSE;

    // Check permission.
    if (local_sharewith_permission_allow($sourceuserid, $sourcecourseid)) {
        $obj = new \stdClass();
        $obj->type = $type;
        $obj->userid = $userid;
        $obj->sourceuserid = $sourceuserid;
        $obj->sourcecourseid = $sourcecourseid;
        $obj->courseid = $courseid;
        $obj->sourcesectionid = $sourcesectionid;
        $obj->sectionid = $sectionid;
        $obj->categoryid = $categoryid;
        $obj->metadata = $metadata;
        $obj->sourceactivityid = $sourceactivityid;
        $obj->status = 0;
        $obj->timemodified = time();

        return $DB->insert_record('local_sharewith_task', $obj);
    }
}

function local_sharewith_get_categories() {
    global $DB;

    // Get all categories.
    $categories = $DB->get_records('course_categories', array('visible' => 1));

    return $categories;
}

function local_sharewith_get_courses() {
    global $DB, $USER;

    $mycourses = enrol_get_my_courses('*', 'id DESC');

    // Sort courses by last access of current user.
    $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');

    foreach ($lastaccesscourses as $c) {
        if (isset($mycourses[$c->courseid])) {
            $mycourses[$c->courseid]->lastaccess = $c->timeaccess;
        }
    }

    // Sort by user's lastaccess to course.
    usort($mycourses, function($a, $b) {
        return $b->lastaccess - $a->lastaccess;
    });

    $result = array();
    foreach ($mycourses as $course) {
        $tmp = array();
        $tmp['id'] = $course->id;
        $tmp['fullname'] = $course->fullname;
        $tmp['shortname'] = $course->shortname;
        $result[] = $tmp;
    }

    return $result;
}

function local_sharewith_get_section_bycourse($courseid) {
    global $DB;

    $result = [];
    $sql = "SELECT cs.id AS section_id, cs.name AS section_name
            FROM {course} c
            LEFT JOIN {course_sections} cs ON c.id=cs.course
            WHERE c.id=?";
    $arr = $DB->get_records_sql($sql, array($courseid));
    $preresult = array_values($arr);
    foreach ($preresult as $key => $obj) {
        if (empty($obj->section_name)) {
            $objtmp = $obj;
            $keytmp = $key + 1;
            $objtmp->section_name = get_string('defaultsectionname', 'local_sharewith') . ' ' . $keytmp;
            $result[] = $objtmp;
        } else {
            $result[] = $obj;
        }
    }

    return $result;
}
