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
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $sharingtypes;
$sharingtypes = array(
    'coursecopy',
    'sectioncopy',
    'activitycopy',
    'activityshare',
);

/**
 * Check permisstions for copy
 * @param str $type
 * @param int $userid
 * @param int $sourceuserid
 * @param int $sourcecourseid
 * @param int $courseid
 * @param int $categoryid
 * @return boolean
 */
function local_sharewith_permission_allow_copy($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $categoryid=null) {

    switch ($type) {
        case "coursecopy":

            return true;
            break;
        case "sectioncopy":
            if (has_capability('local/sharewith:copysection', context_course::instance($sourcecourseid), $sourceuserid)
                    AND has_capability('local/sharewith:copysection', context_course::instance($courseid), $userid)) {
                return true;
            }
            break;
        case "activitycopy":
            if (has_capability('local/sharewith:copyactivity', context_course::instance($sourcecourseid), $sourceuserid)
                    AND has_capability('local/sharewith:copyactivity', context_course::instance($courseid), $userid)) {
                return true;
            }
            break;
    }
    return false;
}

/**
 * Check permisstions for share activity
 * @param int $userid
 * @param int $sourceuserid
 * @param int $sourcecourseid
 * @param int $courseid
 * @return boolean
 */
function local_sharewith_permission_allow_share($userid, $sourceuserid, $sourcecourseid, $courseid) {

    if (has_capability('local/sharewith:shareactivity', context_course::instance($sourcecourseid), $sourceuserid)
            AND has_capability('local/sharewith:copyactivity', context_course::instance($courseid), $userid)) {
        return true;
    }
    return false;
}

/**
 * Add task
 * @param string $type
 * @param int $userid
 * @param int $sourceuserid
 * @param int $sourcecourseid
 * @param int $courseid
 * @param int $sourcesectionid
 * @param int $sectionid
 * @param int $categoryid
 * @param int $sourceactivityid
 * @param obj $metadata
 * @return obj
 */
function local_sharewith_add_task($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $sourcesectionid, $sectionid,
        $categoryid = null, $sourceactivityid = null, $metadata = null) {
    global $DB;

    $result = false;
    // Check permission.
    if (local_sharewith_permission_allow_copy($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $categoryid)) {
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

        $result = $DB->insert_record('local_sharewith_task', $obj);
    }
    return $result;
}

/**
 * Add new task for saving activity
 * @param string $type
 * @param int $shareid
 * @param int $courseid
 * @param int $sectionid
 * @param int $categoryid
 * @param obj $metadata
 * @param int $sourcesectionid
 * @return array
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
                if (local_sharewith_permission_allow_share($USER->id, $share->useridfrom, $share->courseid, $courseid)) {
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
 * Get categories
 * @return obj
 */
function local_sharewith_get_categories() {
    global $DB;

    // Get all visible categories.
    $categories = $DB->get_records('course_categories', array('visible' => 1));

    return array_values($categories);
}

/**
 * Get user courses
 * @return obj
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
        if (!has_capability('moodle/course:update', context_course::instance($course->id), $USER->id)) {
            continue;
        }
        $tmp = array();
        $tmp['id'] = $course->id;
        $tmp['fullname'] = $course->fullname;
        $tmp['shortname'] = $course->shortname;
        $result[] = $tmp;
    }

    return $result;
}

/**
 * Get sections by course
 * @param int $courseid
 * @return obj
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
 * Get teachers
 * @param int $activityid
 * @param int $courseid
 * @return obj
 */
function local_sharewith_get_teachers($activityid, $courseid) {
    global $DB, $PAGE;

    $context = context_course::instance($courseid);
    $PAGE->set_context($context);

    // Find teachers whom sent message previously.
    $sql = "
        SELECT
            DISTINCT(u.id) AS user_id,
            u.firstname AS firstname,
            u.lastname AS lastname,
            CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
            CONCAT('" . $CFG->wwwroot . "/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url
            FROM {local_sharewith_shared} lss
            LEFT JOIN {user} u
                ON (lss.useridto=u.id)
            WHERE lss.useridfrom=? AND lss.activityid=?
                 AND (lss.source IS NULL OR lss.source = '')
    ";
    $result = $DB->get_records_sql($sql, array($USER->id, $activityid));
    $teachers['teachers'] = array_values($result);
    return json_encode($teachers);
}

/**
 * Get teachers
 * @param string $searchstring
 * @return string
 */
function local_sharewith_autocomplete_teachers($searchstring) {
    global $DB;

    $result = '';
    if (!empty($searchstring)) {

        $roles = get_config('local_sharewith', 'roles');
        $teachers = [];
        if ($roles) {
            $sql = "
                SELECT
                    DISTINCT u.id AS id,
                    c.id AS courseid,
                    c.fullname AS full_name,
                    u.username AS user_name,
                    u.firstname AS firstname,
                    u.lastname AS lastname,
                    CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
                    CONCAT('/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
                    u.email AS teacher_mail
                FROM {course} c,
                     {role_assignments} AS ra,
                     {user} AS u, {context} AS ct
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
            $teachers = $DB->get_records_sql($sql, array($searchstrquery, $searchstrquery,
                $searchstrquery, $searchstrquery, $searchstrquery));
        }
        $result = json_encode(array_values($teachers));
    }
    return $result;
}

/**
 * Submit new task to add activity
 * @param int $activityid
 * @param int $courseid
 * @param int $teachersid
 * @param string $message
 * @return string
 */
function local_sharewith_submit_teachers($activityid, $courseid, $teachersid, $message) {
    global $USER, $DB, $CFG;

    $modinfo = get_fast_modinfo($courseid);
    $cm = $modinfo->cms[$activityid];

    $teachersid = json_decode($teachersid);
    if (!empty($teachersid) && !empty($activityid) && $activityid != 0 && !empty($courseid) && $courseid != 0) {

        $roles = get_config('local_sharewith', 'roles');
        $teachers = [];

        if ($roles) {

            $sql = "SELECT DISTINCT u.id
                FROM {course} c,
                     {role_assignments} AS ra,
                     {user} AS u, {context} AS ct
                WHERE
                    c.id = ct.instanceid
                    AND ra.roleid IN ($roles)
                    AND ra.userid = u.id
                    AND ct.id = ra.contextid;";
            $teachers = $DB->get_records_sql($sql);
        }

        $teacherlist = array();
        foreach ($teachers as $item) {
            $teacherlist[] = $item->id;
        }

        foreach ($teachersid as $teacherid) {
            // Check if present teacher.
            if (in_array($teacherid, $teacherlist)) {

                // Save in local_sharewith_shared.
                $objinsert = new stdClass();
                $objinsert->useridto = $teacherid;
                $objinsert->useridfrom = $USER->id;
                $objinsert->courseid = $courseid;
                $objinsert->activityid = $activityid;
                $objinsert->messageid = null;
                $objinsert->restoreid = null;
                $objinsert->complete = 0;
                $objinsert->timecreated = time();

                $rowid = $DB->insert_record('local_sharewith_shared', $objinsert);
                if (!$rowid) {
                    return false;
                }
                // Prepare message for user.
                $a = new stdClass;
                $a->activity_name = $cm->name;
                $a->teacher_name = $USER->firstname . ' ' . $USER->lastname;
                $subject = get_string('subject_message_for_teacher', 'local_sharewith', $a);
                $a = new stdClass;
                $a->restore_id = $rowid;
                $a->activityid = $activityid;
                $a->teacherlink = "$CFG->wwwroot/message/index.php?id=" . $USER->id.'&swactivityname='.$cm->name;
                $fullmessage = $message . "<br>" . get_string('fullmessagehtml_for_teacher', 'local_sharewith', $a);

                $notif = new \core\message\message();
                $notif->component = 'local_sharewith';
                $notif->name = 'sharewith_notification';
                $notif->userfrom = 1;
                $notif->userto = $teacherid;
                $notif->subject = $subject;
                $notif->fullmessage = $fullmessage;
                $notif->fullmessageformat = FORMAT_HTML;
                $notif->fullmessagehtml = $fullmessage;
                $notif->smallmessage = get_string('info_message_for_teacher', 'local_sharewith');
                $notif->notification = 1;
                $notif->replyto = "";
                $notif->courseid = $courseid;
                $messageid = message_send($notif);
                if (!$messageid) {
                    return false;
                }
            }
        }
    }
    return $messageid;
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
