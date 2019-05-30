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

function local_sharewith_save_task($type, $shareid, $courseid, $sectionid, $categoryid = null, $metadata = null, 
        $sourcesectionid = null) {
    global $DB, $USER;

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


function local_sharewith_get_teachers($activityid, $courseid) {
    global $DB, $USER, $OUTPUT, $PAGE;

    $context = context_course::instance($courseid);
    $PAGE->set_context($context);
    
    //Find teachers sended message
    $sql = "
        SELECT
        ass.id,
        ass.useridto AS user_id,
        u.firstname AS firstname,
        u.lastname AS lastname,
        CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
        CONCAT('/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
        DATE_FORMAT(FROM_UNIXTIME(ass.timecreated), '%d.%m.%Y') AS date,
        DATE_FORMAT(FROM_UNIXTIME(ass.timecreated), '%k:%i') AS time

        FROM
        (
            SELECT *
            FROM {local_sharewith_shared}
            WHERE source IS NULL
            ORDER BY timecreated DESC
        ) AS ass

        LEFT JOIN {user} AS u ON (ass.useridto=u.id)
        WHERE ass.useridfrom=? AND ass.activityid=?
        GROUP BY ass.useridto
    ";
    $arrteachers = $DB->get_records_sql($sql, array($USER->id, $activityid));
    
    $result = ($arrteachers) ? 1 : 0;

    $arr = array('activityid' => $activityid, 'courseid' => $courseid, 'teachers' => array_values($arrteachers));
    $html = $OUTPUT->render_from_template('local_sharewith/select_teacher', $arr);

    return json_encode(array('result' => $result, 'html' => $html));
}    
    
function local_sharewith_autocomplete_teachers($searchstring) {
    global $USER, $DB;

    $result = '';
    if(!empty($searchstring)){
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
            FROM {course} AS c,
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

        $search_str_query = '%'.$searchstring.'%';
        $arr_teachers = $DB->get_records_sql($sql, array($search_str_query, $search_str_query, $search_str_query, $search_str_query, $search_str_query));
        $result = json_encode($arr_teachers);
    }

    return $result;
}

function local_sharewith_submit_teachers($activityid, $courseid, $teachersid, $message) {
    global $USER, $DB;

    $teachers_id = json_decode($teachersid);
    if(!empty($teachers_id) && !empty($activityid) && $activityid != 0 && !empty($courseid) && $courseid != 0){

        $arr_teachers = $DB->get_records_sql("
            SELECT DISTINCT u.id AS teacher_id
            FROM {course} AS c,
                 {role_assignments} AS ra,
                 {user} AS u, mdl_context AS ct
            WHERE
                c.id = ct.instanceid
                AND ra.roleid IN(1,2,3,4)
                AND ra.userid = u.id
                AND ct.id = ra.contextid
            GROUP BY u.id;
        ");

        $arr_for_test = array();
        foreach($arr_teachers as $item){
            $arr_for_test[] = $item->teacher_id;
        }

        foreach($teachers_id as $teacher_id){
            //Check if present teacher
            if(in_array($teacher_id, $arr_for_test)){

                //Save in message DB
                //prepare message for user
                $a = new stdClass;
                $a->activity_name = $activityid;
                $a->teacher_name = $USER->firstname.' '.$USER->lastname;
                $subject = get_string('subject_message_for_teacher', 'local_sharewith', $a);

                $obj_insert = new stdClass();
                $obj_insert->useridfrom = $USER->id;
                $obj_insert->useridto = $teacher_id;
                $obj_insert->subject = $subject;
                $obj_insert->fullmessage = $message;
                $obj_insert->fullmessageformat = 2;
                $obj_insert->fullmessagehtml = '';
                $obj_insert->smallmessage = get_string('info_message_for_teacher', 'local_sharewith');
                $obj_insert->notification = 1;
                $obj_insert->timecreated = time();
                $obj_insert->component = 'local_sharewith';
                $obj_insert->eventtype = 'sharewith_notification';
                $message_id = $DB->insert_record('notifications', $obj_insert);

                /////////////////////////////////////
                $obj_insert = new stdClass();
                $obj_insert->notificationid = $message_id;
                $DB->insert_record('message_popup_notifications', $obj_insert);

                //Save in local_sharewith_shared
                $obj_insert = new stdClass();
                $obj_insert->useridto = $teacher_id;
                $obj_insert->useridfrom = $USER->id;
                $obj_insert->courseid = $courseid;
                $obj_insert->activityid = $activityid;
                $obj_insert->messageid = $message_id;
                $obj_insert->restoreid = null;
                $obj_insert->complete = 0;
                $obj_insert->timecreated = time();

                $row_id = $DB->insert_record('local_sharewith_shared', $obj_insert);

                //Update full message and fullmessagehtml
                $a = new stdClass;
                $a->restore_id = $row_id;
                $fullmessage = get_string('fullmessagehtml_for_teacher', 'local_sharewith', $a);

                $obj = new stdClass();
                $obj->id = $message_id;
                $obj->fullmessage = $message;
                $obj->fullmessagehtml = $fullmessage . ' <br> ' . $message;
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
        // Insert
        $item = new stdClass;
        $item->instanceid = $cmid;
        $item->fieldid = $fieldid->id;
        $item->data = $cmid;
        $DB->insert_record('local_metadata', $item);
        return true;
    } else if ($srcmetadatafieldid->data == '') {
        // Update
        $srcmetadatafieldid->data = $cmid;
        $DB->update_record('local_metadata', $srcmetadatafieldid);
        return true;
    }

    return false;
}