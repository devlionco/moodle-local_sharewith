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
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
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
            $lock->release();
        }
    }

    /**
     * run_cron_sharewith
     */
    public function run_cron_sharewith() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/sharewith/classes/duplicate.php');

        $obj = $DB->get_records('local_sharewith_task', array('status' => 0));

        foreach ($obj as $item) {
            switch ($item->type) {
                case 'coursecopy':
                    // Required.
                    // $item->sourcecourseid.

                    if (!empty($item->sourcecourseid) && !empty($item->categoryid) &&
                            get_config('local_sharewith', 'coursecopy') &&
                            has_capability('moodle/backup:backupcourse', context_course::instance($item->sourcecourseid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restorecourse', context_course::instance($item->sourcecourseid),
                                    $item->userid)) {

                        $tc = get_course($item->sourcecourseid);
                        $category = $DB->get_record('course_categories', array('id' => $item->categoryid));
                        $fullname = $tc->fullname . ' ' . $category->name . ' ' . get_string('wordcopy', 'local_sharewith');

                        $shortnamedefault = $tc->shortname . '-' . $item->categoryid;
                        $shortname = $this->create_relevant_shortname($shortnamedefault);

                        $adminid = isset($CFG->adminid) ? $CFG->adminid : 2;

                        // Copy course.
                        $newcourse = \duplicate::duplicate_course($adminid, $tc->id, $fullname, $shortname,
                                        $item->categoryid);

                        // Set user to course.
                        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
                        if (!empty($role)) {
                            enrol_try_internal_enrol($newcourse['id'], $item->sourceuserid, $role->id);
                        }

                        // Sent event.
                        $eventdata = array(
                            'userid' => $item->sourceuserid,
                            'courseid' => $tc->id,
                            'categoryid' => $item->categoryid,
                            'targetcourseid' => $newcourse['id']
                        );

                        \local_sharewith\event\course_copy::create_event($newcourse['id'], $eventdata)->trigger();
                    }
                    break;

                case 'sectioncopy':
                    // Required.
                    // $item->sourcesectionid.
                    // $item->courseid.

                    if (!empty($item->sourcesectionid) && !empty($item->courseid) &&
                            get_config('local_sharewith', 'sectioncopy') &&
                            has_capability('moodle/backup:backupcourse', context_course::instance($item->sourcecourseid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restorecourse', context_course::instance($item->courseid),
                                    $item->userid)) {

                        $newsection = \duplicate::duplicate_section($item->sourcesectionid, $item->courseid);

                        $roles = array();
                        $context = \context_course::instance($item->courseid);
                        if ($userroles = get_user_roles($context, $item->sourceuserid)) {
                            foreach ($userroles as $role) {
                                $roles[] = $role->shortname;
                            }
                        }

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
                    }
                    break;

                case 'activitycopy':
                    // Required.
                    // $item->sourceactivityid.
                    // $item->courseid.
                    // $item->sectionid.

                    if (!empty($item->sourceactivityid) && !empty($item->courseid) && !empty($item->sectionid) &&
                            get_config('local_sharewith', 'activitycopy') &&
                            has_capability('moodle/backup:backupactivity', context_module::instance($item->sourceactivityid),
                                    $item->sourceuserid) &&
                            has_capability('moodle/restore:restoreactivity', context_course::instance($item->courseid),
                                    $item->userid)) {

                        $newactivity = $this->copy_activity($item->sourceactivityid, $item->courseid, $item->sectionid);

                        // Sent event.
                        $eventdata = array(
                            'userid' => $item->sourceuserid,
                            'courseid' => $item->courseid,
                            'sectionid' => $item->sectionid,
                            'activityid' => $item->sourceactivityid,
                            'targetactivityid' => $newactivity->id
                        );

                        \local_sharewith\event\activity_copy::create_event($item->courseid, $eventdata)->trigger();
                    }
                    break;
            }

            // End working.
            $item->status = 1;
            $DB->update_record('local_sharewith_task', $item);

            if ($item->sourceuserid != $item->userid) {
                \duplicate::send_notification($item, $newactivity);
            }
        }
    }

    /**
     * Copy
     *
     * @return int
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
     *
     * @return
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

