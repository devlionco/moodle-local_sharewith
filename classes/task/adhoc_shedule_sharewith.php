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
 * Local plugin "sandbox" - Task definition
 *
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sharewith\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_shedule_sharewith extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'local_sharewith';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/sharewith/locallib.php');

        $lockkey = 'sharewith_cron';
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_sharewith_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_sharewith();
        }
        $lock->release();
    }

    /**
     * run_cron_sharewith
     */
    public function run_cron_sharewith() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/sharewith/classes/duplicate.php');

        mtrace("Sharewith | - Getting tasks");
        $obj = $DB->get_records('local_sharewith_task', array('status' => 0));

        mtrace("Sharewith | - number of tasks: " . count($obj));

        foreach ($obj as $item) {

            mtrace("Sharewith | -- task #$item->id set status 2 (processing)");

            // Setting processing status '2'.
            $taskprocesserror = true;
            $item->status = 2;
            $DB->update_record('local_sharewith_task', $item);

            mtrace("Sharewith | -- task #$item->id set status 2 (processing) - OK");

            mtrace("Sharewith | -- task #$item->id type $item->type");

            switch ($item->type) {
                case 'coursecopy':
                    // Required.
                    // $item->sourcecourseid.

                    mtrace("Sharewith | -- task #$item->id checking capability");

                    if (!empty($item->sourcecourseid) && !empty($item->categoryid) &&
                            get_config('local_sharewith', 'coursecopy') &&
                            has_capability('moodle/backup:backupcourse', context_course::instance($item->sourcecourseid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restorecourse', context_course::instance($item->sourcecourseid),
                                    $item->userid)) {

                        mtrace("Sharewith | -- task #$item->id checking capability - OK");

                        mtrace("Sharewith | -- task #$item->id preparing names");

                        $tc = get_course($item->sourcecourseid);
                        $category = $DB->get_record('course_categories', array('id' => $item->categoryid));
                        $fullname = $tc->fullname . ' ' . $category->name . ' ' . get_string('wordcopy', 'local_sharewith');

                        $shortnamedefault = $tc->shortname . '-' . $item->categoryid;
                        $shortname = $this->create_relevant_shortname($shortnamedefault);

                        $adminid = isset($CFG->adminid) ? $CFG->adminid : 2;

                        mtrace("Sharewith | -- task #$item->id preparing names - OK");

                        mtrace("Sharewith | -- task #$item->id COPING");

                        // Copy course.
                        $newcourse = \duplicate::duplicate_course($adminid, $tc->id, $fullname, $shortname,
                                $item->categoryid);

                        mtrace("Sharewith | -- task #$item->id COPING - OK");

                        mtrace("Sharewith | -- task #$item->id Set user to course");

                        // Set user to course.
                        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
                        if (!empty($role)) {
                            enrol_try_internal_enrol($newcourse['id'], $item->sourceuserid, $role->id);
                        }

                        mtrace("Sharewith | -- task #$item->id Set user to course - OK");

                        mtrace("Sharewith | -- task #$item->id Sent event");

                        // Sent event.
                        $eventdata = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $tc->id,
                                'categoryid' => $item->categoryid,
                                'targetcourseid' => $newcourse['id']
                        );

                        \local_sharewith\event\course_copy::create_event($newcourse['id'], $eventdata)->trigger();

                        mtrace("Sharewith | -- task #$item->id Sent event - OK");

                        $taskprocesserror = false;

                    } else {
                        mtrace("Sharewith | -- task #$item->id checking capability - FAIL");
                    }
                    break;

                case 'sectioncopy':
                    // Required.
                    // $item->sourcesectionid.
                    // $item->courseid.

                    mtrace("Sharewith | -- task #$item->id checking capability");

                    if (!empty($item->sourcesectionid) && !empty($item->courseid) &&
                            get_config('local_sharewith', 'sectioncopy') &&
                            has_capability('moodle/backup:backupcourse', context_course::instance($item->sourcecourseid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restorecourse', context_course::instance($item->courseid),
                                    $item->userid)) {

                        mtrace("Sharewith | -- task #$item->id checking capability - OK");

                        mtrace("Sharewith | -- task #$item->id COPING");

                        $newsection = \duplicate::duplicate_section($item->sourcesectionid, $item->courseid);

                        mtrace("Sharewith | -- task #$item->id COPING - OK");

                        $roles = array();
                        $context = \context_course::instance($item->courseid);
                        if ($userroles = get_user_roles($context, $item->sourceuserid)) {
                            foreach ($userroles as $role) {
                                $roles[] = $role->shortname;
                            }
                        }

                        mtrace("Sharewith | -- task #$item->id Sent event");

                        // Sent event.
                        $eventdata = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $item->courseid,
                                'sectionid' => $item->sourcesectionid,
                                'targetuserid' => $item->userid,
                                'targetcourseid' => $item->courseid,
                                'targetsectionid' => $item->sectionid,
                        );

                        \local_sharewith\event\section_copy::create_event($item->courseid, $eventdata)->trigger();

                        mtrace("Sharewith | -- task #$item->id Sent event - OK");

                        $taskprocesserror = false;

                    } else {
                        mtrace("Sharewith | -- task #$item->id checking capability - FAIL");
                    }
                    break;

                case 'activitycopy':
                    // Required.
                    // $item->sourceactivityid.
                    // $item->courseid.
                    // $item->sectionid.

                    mtrace("Sharewith | -- task #$item->id checking capability");

                    if (!empty($item->sourceactivityid) && !empty($item->courseid) && !empty($item->sectionid) &&
                            get_config('local_sharewith', 'activitycopy') &&
                            has_capability('moodle/backup:backupactivity', context_module::instance($item->sourceactivityid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restoreactivity', context_course::instance($item->courseid),
                                    $item->userid)) {

                        mtrace("Sharewith | -- task #$item->id checking capability - OK");

                        mtrace("Sharewith | -- task #$item->id COPING");

                        $newactivity = $this->copy_activity($item->sourceactivityid, $item->courseid, $item->sectionid);

                        mtrace("Sharewith | -- task #$item->id COPING - OK");

                        mtrace("Sharewith | -- task #$item->id Sent event");

                        // Sent event.
                        $eventdata = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $item->courseid,
                                'sectionid' => $item->sectionid,
                                'activityid' => $item->sourceactivityid,
                                'targetactivityid' => $newactivity->id
                        );

                        \local_sharewith\event\activity_copy::create_event($item->courseid, $eventdata)->trigger();

                        mtrace("Sharewith | -- task #$item->id Sent event - OK");

                        $taskprocesserror = false;

                    } else {
                        mtrace("Sharewith | -- task #$item->id checking capability - FAIL");
                    }
                    break;

                case 'activityshare':

                    mtrace("Sharewith | -- task #$item->id checking capability");

                    if (!empty($item->sourceactivityid) && !empty($item->courseid) && !empty($item->sectionid) &&
                            get_config('local_sharewith', 'activitysending') &&
                            has_capability('moodle/backup:backupactivity', context_module::instance($item->sourceactivityid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restoreactivity', context_course::instance($item->courseid),
                                    $item->userid)) {

                        mtrace("Sharewith | -- task #$item->id checking capability - OK");

                        mtrace("Sharewith | -- task #$item->id COPING");

                        $newactivityshared = $this->copy_activity($item->sourceactivityid, $item->courseid, $item->sectionid);

                        mtrace("Sharewith | -- task #$item->id COPING - OK");

                        mtrace("Sharewith | -- task #$item->id Sent event");

                        // Send event.
                        $eventdatashared = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $item->courseid,
                                'sectionid' => $item->sectionid,
                                'activityid' => $item->sourceactivityid,
                                'targetactivityid' => $newactivityshared->id
                        );
                        \local_sharewith\event\activity_copy::create_event($item->courseid, $eventdatashared)->trigger();

                        mtrace("Sharewith | -- task #$item->id Sent event - OK");

                        $taskprocesserror = false;

                    } else {
                        mtrace("Sharewith | -- task #$item->id checking capability - FAIL");
                    }
                    break;
            }

            if ($taskprocesserror) {
                continue;
            }

            mtrace("Sharewith | -- task #$item->id set status 1 (End working)");

            // End working.
            $item->status = 1;
            $DB->update_record('local_sharewith_task', $item);

            mtrace("Sharewith | -- task #$item->id set status 1 (End working) - OK");

            if ($item->sourceuserid != $item->userid) {
                \duplicate::send_notification($item, $newactivity);
            }
        }

        mtrace("Sharewith | - tasks processed");

    }

    /**
     * Copy activity
     *
     * @param int $sourceactivityid
     * @param int $courseid
     * @param int $sectionid
     * @return obj $newactivity
     */
    public function copy_activity($sourceactivityid, $courseid, $sectionid) {
        global $DB;

        $newactivities = array();
        $newactivity = \duplicate::duplicate_activity($sourceactivityid, $courseid, $sectionid, $newactivities);

        // Update field added in course_modules.
        $newrow = $DB->get_record('course_modules', array('id' => $newactivity->id));
        $newrow->added = time();
        $DB->update_record('course_modules', $newrow);

        return $newactivity;
    }

    /**
     * Create shortname
     *
     * @param string $shortname
     * @return int
     */
    public function create_relevant_shortname($shortname) {
        global $DB;

        $i = 1;
        do {
            $arr = $DB->get_records('course', array('shortname' => $shortname));
            if (!empty($arr)) {
                $shortname .= $i;
                $i++;
            } else {
                break;
            }
        } while (1);

        return $shortname;
    }
}
