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
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $sharingtypes;
$sharingtypes = array(
    'coursecopy',
    'sectioncopy',
    'activitycopy',
);

/**
 *
 * @return
 */
function local_sharewith_permission_allow($courseid, $userid) {

    if (has_capability('moodle/course:update', context_course::instance($courseid), $userid)) {
        return true;
    }

    return false;
}

/**
 *
 * @return
 */
function local_sharewith_add_task($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $sourcesectionid, $sectionid,
        $categoryid = null, $sourceactivityid = null, $metadata = null) {
    global $DB;

    // Check permission.
    if (local_sharewith_permission_allow($sourcecourseid, $sourceuserid)) {
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

/**
 *
 * @return
 */
function local_sharewith_save_task($type, $shareid, $courseid, $sectionid, $categoryid = null, $metadata = null,
        $sourcesectionid = null) {
    global $DB, $USER;

    $sendenable = get_config('local_sharewith', 'activitysending');

    if ($sendenable == 1) {

        $share = $DB->get_record('local_sharewith_shared', array('useridto' => $USER->id, 'id' => $shareid));

        if ($share) {
            $activity = $DB->get_record('course_modules', array('id' => $share->activityid));
            if ($activity) {
                // Check permission.
                if (local_sharewith_permission_allow($share->courseid, $share->useridfrom)) {
                    $obj = new \stdClass();
                    $obj->type = $type;
                    $obj->userid = $USER->id;
                    $obj->sourceuserid = $share->useridfrom;
                    $obj->sourcecourseid = $share->courseid;
                    $obj->courseid = $courseid;
                    $obj->sourcesectionid = $sourcesectionid;
                    $obj->sectionid = $sectionid;
                    $obj->categoryid = $categoryid;
                    $obj->metadata = $metadata;
                    $obj->sourceactivityid = $share->activityid;
                    $obj->status = 0;
                    $obj->timemodified = time();

                    return array('result' => $DB->insert_record('local_sharewith_task', $obj), 'text' => '');
                }
            } else {
                return array('result' => 0, 'text' => get_string('activitydeleted', 'local_sharewith'));
            }
        }
    } else {
        return array('result' => 0, 'text' => get_string('sendingnotallowed', 'local_sharewith'));
    }
}

/**
 *
 * @return
 */
function local_sharewith_get_categories() {
    global $DB;

    // Get all categories.
    $categories = $DB->get_records('course_categories', array('visible' => 1));

    return $categories;
}

/**
 *
 * @return
 */
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

/**
 *
 * @return
 */
function local_sharewith_get_section_bycourse($courseid) {
    global $DB;

    $course = get_course($courseid);

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
            if ($key == 0) {
                $objtmp->section_name = get_string('generalsectionname', 'local_sharewith');
            } else {
                $objtmp->section_name = get_string('sectionname', 'format_' . $course->format) . ' ' . $key;
            }
            $result[] = $objtmp;
        } else {
            $result[] = $obj;
        }
    }

    return $result;
}

/**
 *
 * @return
 */
function local_sharewith_get_teachers($activityid, $courseid) {
    global $DB, $USER, $OUTPUT, $PAGE, $CFG;

    $context = context_course::instance($courseid);
    $PAGE->set_context($context);

    // Find teachers sended message.
    $sql = "
        SELECT
        ass.id,
        ass.useridto AS user_id,
        u.firstname AS firstname,
        u.lastname AS lastname,
        CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
        CONCAT('" . $CFG->wwwroot . "/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
        DATE_FORMAT(FROM_UNIXTIME(ass.timecreated), '%d.%m.%Y') AS date,
        DATE_FORMAT(FROM_UNIXTIME(ass.timecreated), '%k:%i') AS time

        FROM
        (
            SELECT *
            FROM {local_sharewith_shared}
            WHERE source IS NULL
            ORDER BY timecreated DESC
        ) AS ass

        LEFT JOIN {user} u ON (ass.useridto=u.id)
        WHERE ass.useridfrom=? AND ass.activityid=?
        GROUP BY ass.useridto
    ";
    $arrteachers = $DB->get_records_sql($sql, array($USER->id, $activityid));

    $result = ($arrteachers) ? 1 : 0;

    $arr = array('activityid' => $activityid, 'courseid' => $courseid, 'teachers' => array_values($arrteachers));
    $html = $OUTPUT->render_from_template('local_sharewith/select_teacher', $arr);

    return json_encode(array('result' => $result, 'html' => $html));
}

/**
 *
 * @return
 */
function local_sharewith_autocomplete_teachers($searchstring) {
    global $USER, $DB;

    $result = '';
    if (!empty($searchstring)) {
        $sql = "
            SELECT
                DISTINCT u.id AS teacher_id,
                c.id AS course_id,
                c.fullname AS full_name,
                u.username AS user_name,
                u.firstname AS firstname,
                u.lastname AS lastname,
                CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
                CONCAT('/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
                u.email AS teacher_mail
            FROM {course} c,
                 {role_assignments} AS ra,
                 {user} AS u, mdl_context AS ct
            WHERE c.id = ct.instanceid
                AND ra.roleid IN(1,2,3,4)
                AND ra.userid = u.id
                AND ct.id = ra.contextid
                AND ( u.email LIKE(?)
                    OR u.lastname LIKE(?)
                    OR u.firstname LIKE(?)
                    OR u.username LIKE(?)
                    OR CONCAT(u.firstname, ' ', u.lastname) LIKE(?))
            GROUP BY u.id;
        ";

        $searchstrquery = '%' . $searchstring . '%';
        $arrteachers = $DB->get_records_sql($sql, array($searchstrquery, $searchstrquery,
            $searchstrquery, $searchstrquery, $searchstrquery));
        $result = json_encode($arrteachers);
    }

    return $result;
}

/**
 *
 * @return
 */
function local_sharewith_submit_teachers($activityid, $courseid, $teachersid, $message) {
    global $USER, $DB;

    $modinfo = get_fast_modinfo($courseid);
    $cm = $modinfo->cms[$activityid];

    $teachersid = json_decode($teachersid);
    if (!empty($teachersid) && !empty($activityid) && $activityid != 0 && !empty($courseid) && $courseid != 0) {

        $arrteachers = $DB->get_records_sql("
            SELECT DISTINCT u.id AS teacher_id
            FROM {course} c,
                 {role_assignments} AS ra,
                 {user} AS u, mdl_context AS ct
            WHERE
                c.id = ct.instanceid
                AND ra.roleid IN(1,2,3,4)
                AND ra.userid = u.id
                AND ct.id = ra.contextid
            GROUP BY u.id;
        ");

        $arrfortest = array();
        foreach ($arrteachers as $item) {
            $arrfortest[] = $item->teacher_id;
        }

        foreach ($teachersid as $teacherid) {
            // Check if present teacher.
            if (in_array($teacherid, $arrfortest)) {

                // Save in message DB.
                // Prepare message for user.
                $a = new stdClass;
                $a->activity_name = $cm->name;
                $a->teacher_name = $USER->firstname . ' ' . $USER->lastname;
                $subject = get_string('subject_message_for_teacher', 'local_sharewith', $a);

                $objinsert = new stdClass();
                $objinsert->useridfrom = $USER->id;
                $objinsert->useridto = $teacherid;
                $objinsert->subject = $subject;
                $objinsert->fullmessage = $message;
                $objinsert->fullmessageformat = 2;
                $objinsert->fullmessagehtml = '';
                $objinsert->smallmessage = get_string('info_message_for_teacher', 'local_sharewith');
                $objinsert->notification = 1;
                $objinsert->timecreated = time();
                $objinsert->component = 'local_sharewith';
                $objinsert->eventtype = 'sharewith_notification';
                $messageid = $DB->insert_record('notifications', $objinsert);

                $objinsert = new stdClass();
                $objinsert->notificationid = $messageid;
                $DB->insert_record('message_popup_notifications', $objinsert);

                // Save in local_sharewith_shared.
                $objinsert = new stdClass();
                $objinsert->useridto = $teacherid;
                $objinsert->useridfrom = $USER->id;
                $objinsert->courseid = $courseid;
                $objinsert->activityid = $activityid;
                $objinsert->messageid = $messageid;
                $objinsert->restoreid = null;
                $objinsert->complete = 0;
                $objinsert->timecreated = time();

                $rowid = $DB->insert_record('local_sharewith_shared', $objinsert);

                // Update full message and fullmessagehtml.
                $a = new stdClass;
                $a->restore_id = $rowid;
                $a->teacherlink = "$CFG->wwwroot/message/index.php?id=" . $USER->id;
                $fullmessage = get_string('fullmessagehtml_for_teacher', 'local_sharewith', $a);

                $obj = new stdClass();
                $obj->id = $messageid;
                $obj->fullmessage = $message;
                $obj->fullmessagehtml = $message . '<br>' . $fullmessage;
                $DB->update_record('notifications', $obj);
            }
        }
    }
    return '';
}

/**
 * Check ID value in metadata, updates if needed.
 *
 * @param string $cmid source activity ID
 * @return bool
 */
function local_sharewith_check_metadata_id($cmid) {
    global $DB;

    $fieldid = $DB->get_record_sql("
        SELECT id
          FROM {local_metadata_field}
         WHERE shortname = 'ID'
        ");

    $sql = "SELECT *
              FROM {local_metadata}
             WHERE instanceid = :instanceid
                   AND fieldid = :fieldid";

    $srcmetadatafieldid = $DB->get_record_sql($sql, array(
        'instanceid' => $cmid,
        'fieldid' => $fieldid->id
    ));

    if (empty($srcmetadatafieldid)) {
        // Insert.
        $item = new stdClass;
        $item->instanceid = $cmid;
        $item->fieldid = $fieldid->id;
        $item->data = $cmid;
        $DB->insert_record('local_metadata', $item);
        return true;
    } else if ($srcmetadatafieldid->data == '') {
        // Update.
        $srcmetadatafieldid->data = $cmid;
        $DB->update_record('local_metadata', $srcmetadatafieldid);
        return true;
    }

    return false;
}
