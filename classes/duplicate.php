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
 * External course API
 *
 * @package    local_sharewith
 * @category   external
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once(__DIR__ . '/../lib.php');

/**
 * Course external functions
 *
 * @package    local_sharewith
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class duplicate extends external_api {

    /**
     * Construct
     */
    public function __construct() {

    }

    /**
     * Duplicate a course
     *
     * @param int $userid
     * @param int $courseid
     * @param string $fullname Duplicated course fullname
     * @param string $shortname Duplicated course shortname
     * @param int $categoryid Duplicated course parent category id
     * @param int $visible Duplicated course availability
     * @param array $options List of backup options
     * @return array New course info
     * @since Moodle 2.3
     */
    public static function duplicate_course($userid, $courseid, $fullname, $shortname, $categoryid, $visible = 1,
            $options = array()) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Parameter validation.
        $params = self::validate_parameters(
                        self::duplicate_course_parameters(),
                        array(
                            'courseid' => $courseid,
                            'fullname' => $fullname,
                            'shortname' => $shortname,
                            'categoryid' => $categoryid,
                            'visible' => $visible,
                            'options' => $options
                        )
        );

        // Context validation.
        if (!($course = $DB->get_record('course', array('id' => $params['courseid'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        // Category where duplicated course is going to be created.
        $categorycontext = context_coursecat::instance($params['categoryid']);
        self::validate_context($categorycontext);

        // Course to be duplicated.
        $coursecontext = context_course::instance($course->id);
        self::validate_context($coursecontext);

        // Example 'enrolments' => backup::ENROL_WITHUSERS - default.
        // ENROL_NEVER - Backup a course with enrolment methods and restore it without user data.
        // ENROL_WITHUSERS - Backup a course with enrolment methods and restore it with user data with enrolment methods.
        // ENROL_ALWAYS - Backup a course with enrolment methods and restore it without user data with enrolment methods.

        $backupdefaults = array(
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'users' => 0,
            'enrolments' => backup::ENROL_NEVER,
            'role_assignments' => 0,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0
        );

        $backupsettings = array();
        // Check for backup and restore options.
        if (!empty($params['options'])) {
            foreach ($params['options'] as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 and $value !== 1) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                $backupsettings[$option['name']] = $value;
            }
        }

        // Check if the shortname is used.
        if ($foundcourses = $DB->get_records('course', array('shortname' => $shortname))) {
            foreach ($foundcourses as $foundcourse) {
                $foundcoursenames[] = $foundcourse->fullname;
            }

            $foundcoursenamestring = implode(',', $foundcoursenames);
            throw new moodle_exception('shortnametaken', '', '', $foundcoursenamestring);
        }

        // Backup the course.
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userid);

        foreach ($backupsettings as $name => $value) {
            if ($setting = $bc->get_plan()->get_setting($name)) {
                $bc->get_plan()->get_setting($name)->set_value($value);
            }
        }

        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        $bc->destroy();

        // Restore the backup immediately.
        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backupbasepath . "/moodle_backup.xml")) {
            $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        }

        // Create new course.
        $newcourseid = restore_dbops::create_new_course($params['fullname'], $params['shortname'], $params['categoryid']);

        $rc = new restore_controller($backupid, $newcourseid,
                backup::INTERACTIVE_NO, backup::MODE_HUB, $userid, backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                $setting->set_value($value);
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        $course = $DB->get_record('course', array('id' => $newcourseid), '*', MUST_EXIST);
        $course->fullname = $params['fullname'];
        $course->shortname = $params['shortname'];
        $course->visible = $params['visible'];

        // Set shortname and fullname back.
        $DB->update_record('course', $course);

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        // Delete the course backup file created by this WebService. Originally located in the course backups area.
        $file->delete();

        return array('id' => $course->id, 'shortname' => $course->shortname);
    }

    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @param int $newactivityid target
     * @param bool $availabilitydisable
     * @return cm_info|null cminfo object if we sucessfully duplicated the mod and found the new cm.
     */
    public static function duplicate_activity($sourceactivityid, $courseid, $sectionid, &$newactivityid,
            $availabilitydisable = false) {
        global $DB;

        if ($availabilitydisable) {
            $res = self::duplicate_activity_source($sourceactivityid, $courseid, $sectionid, $newactivityid, true);
        } else {
            $row = $DB->get_record('course_modules', array('id' => $sourceactivityid));
            $res = self::duplicate_activity_source($sourceactivityid, $courseid, $sectionid, $newactivityid);
            $DB->update_record('course_modules', $row);
        }

        return $res;
    }


    /**
     * Duplicates activity
     *
     * @param int $sourceactivityid
     * @param int $courseid
     * @param int $sectionid
     * @param int $newactivityid
     * @param bool $availabilitydisable
     * @return obj cm_info
     */
    public static function duplicate_activity_source($sourceactivityid, $courseid, $sectionid, &$newactivityid,
            $availabilitydisable = false) {
        global $USER, $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Copy activity in availability.
        if (!$availabilitydisable) {
            self::duplicate_activity_from_availability($sourceactivityid, $courseid, $sectionid, $newactivityid,
                    $availabilitydisable);
        }

        $bc = new \backup_controller(backup::TYPE_1ACTIVITY, $sourceactivityid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
                backup::MODE_IMPORT, $USER->id);
        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $bc->execute_plan();
        $bc->destroy();

        $rc = new \restore_controller($backupid, $courseid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id,
                backup::TARGET_CURRENT_ADDING);
        $cmcontext = context_module::instance($sourceactivityid);
        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }
            }
        }

        $rc->execute_plan();

        $newcmid = null;
        $tasks = $rc->get_plan()->get_tasks();
        foreach ($tasks as $task) {
            if (is_subclass_of($task, 'restore_activity_task')) {
                if ($task->get_old_contextid() == $cmcontext->id) {
                    $newcmid = $task->get_moduleid();
                    break;
                }
            }
        }

        if ($newcmid) {
            $course = get_course($courseid);
            $info = get_fast_modinfo($course);
            $newcm = $info->get_cm($newcmid);
            $section = $DB->get_record('course_sections', array('id' => $sectionid, 'course' => $courseid));
            moveto_module($newcm, $section);

            // Copy users from mod glossary.
            self::copy_users_mod_glossary($sourceactivityid, $newcmid);
        }

        rebuild_course_cache($newcm->course);
        $rc->destroy();
        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        $newactivityid[] = $newcm;

        return isset($newcm) ? $newcm : null;
    }

    /**
     * Duplicates activity
     *
     * @param int $sourcesectionid source
     * @param int $courseid target
     * @return section_info|null section object if we sucessfully duplicated the section and found the new cm.
     */
    public static function duplicate_section($sourcesectionid, $courseid) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $sourcesection = $DB->get_record('course_sections', array('id' => $sourcesectionid));
        $newsection = course_create_section($courseid, 0);

        // Add default name and summary for section.
        if ($sourcesection->name != null || $sourcesection->summary != null) {
            $newsection->name = $sourcesection->name;
            $newsection->summary = $sourcesection->summary;
            $DB->update_record('course_sections', $newsection);
        }

        // Copy files.
        $cc = $DB->get_record('context', array('instanceid' => $sourcesection->course, 'contextlevel' => 50));
        $ccnew = $DB->get_record('context', array('instanceid' => $newsection->course, 'contextlevel' => 50));

        $fs = get_file_storage();
        $files = $fs->get_area_files($cc->id, 'course', 'section', $sourcesection->id);

        // Create files.
        foreach ($files as $f) {
            if ($f->get_filesize() != 0 || $f->get_filename() != '.') {
                $fileinfo = array(
                    'contextid' => $ccnew->id,
                    'component' => $f->get_component(),
                    'filearea' => $f->get_filearea(),
                    'itemid' => $newsection->id,
                    'filepath' => $f->get_filepath(),
                    'filename' => $f->get_filename()
                );

                // Save file.
                $fs->create_file_from_string($fileinfo, $f->get_content());
            }
        }

        $activities = explode(',', $sourcesection->sequence);
        foreach ($activities as $key => $activity) {

            $row = $DB->get_record('course_modules', array('id' => $activity));
            if (!empty($row) && $row->deletioninprogress == 0) {
                $newactivities = array();
                self::duplicate_activity($activity, $courseid, $newsection->id, $newactivities);
                $DB->update_record('course_modules', $row);
            }
        }

        return isset($newsection) ? $newsection : null;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function duplicate_course_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course to duplicate id'),
                'fullname' => new external_value(PARAM_TEXT, 'duplicated course full name'),
                'shortname' => new external_value(PARAM_TEXT, 'duplicated course short name'),
                'categoryid' => new external_value(PARAM_INT, 'duplicated course category parent'),
                'visible' => new external_value(PARAM_INT, 'duplicated course visible, default to yes', VALUE_DEFAULT, 1),
                'options' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHAEXT, 'The backup option name:
                                "activities" (int) Include course activites (default to 1 that is equal to yes),
                                "blocks" (int) Include course blocks (default to 1 that is equal to yes),
                                "filters" (int) Include course filters  (default to 1 that is equal to yes),
                                "users" (int) Include users (default to 0 that is equal to no),
                                "enrolments" (int) Include enrolment methods (default to 1 - restore only with users),
                                "role_assignments" (int) Include role assignments  (default to 0 that is equal to no),
                                "comments" (int) Include user comments  (default to 0 that is equal to no),
                                "userscompletion" (int) Include user course completion information
                                (default to 0 that is equal to no),
                                "logs" (int) Include course logs  (default to 0 that is equal to no),
                                "grade_histories" (int) Include histories  (default to 0 that is equal to no)'
                            ),
                            'value' => new external_value(PARAM_RAW,
                                    'the value for the option 1 (yes) or 0 (no)'
                            )
                        )
                    ), VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Duplicates activity from availability
     *
     * @param int $activityid source
     * @param int $courseid target
     * @param int $sectionid target
     * @param int $newactivityid target
     * @param bool $availabilitydisable target
     * @return section_info|null section object if we sucessfully duplicated the section and found the new cm.
     */
    public static function duplicate_activity_from_availability($activityid, $courseid, $sectionid, &$newactivityid,
            $availabilitydisable = false) {
        global $DB;

        $row = $DB->get_record('course_modules', array('id' => $activityid, 'deletioninprogress' => 0));
        if (!empty($row) && !empty($row->availability)) {

            $obj = json_decode($row->availability);
            if (isset($obj->c) && !empty($obj->c)) {
                foreach ($obj->c as $key => $item) {
                    if ($item->type == 'completion') {
                        $newactivity = self::duplicate_activity_source($item->cm, $courseid, $sectionid, $newactivityid,
                                        $availabilitydisable);

                        $obj->c[$key]->cm = $newactivity->id;
                        $update = new stdClass();
                        $update->id = $activityid;
                        $update->availability = json_encode($obj);
                        $DB->update_record('course_modules', $update);
                    }
                }
            }
        }
    }

    /**
     * Send notification
     * @param obj $item
     * @param obj $newactivity
     */
    public static function send_notification($item, $newactivity) {
        global $DB;

        // Send notitification to a user, the shared activity successfully copied.
        $course = get_course($item->courseid);
        $a = new stdClass();
        $a->coursename = $course->fullname;
        switch ($item->type) {
            case 'coursecopy':
                $link = new moodle_url('/course/view.php', array('id' => $item->courseid));
                $a->link = $link->out(false);
                break;
            case 'sectioncopy':
                $sec = $DB->get_record('course_sections', array('id' => 21));
                $link = new moodle_url('/course/view.php', array('id' => $item->courseid . "#section-" . $sec->section));
                $a->link = $link->out(false);
                break;
            case 'activitycopy':
                $modinfo = get_fast_modinfo($item->courseid);
                $cm = $modinfo->cms[$newactivity->id];
                $link = new moodle_url('/mod/' . $cm->modname . '/view.php', array('id' => $newactivity->id));
                $a->link = $link->out(false);
                break;
            case 'activityshare':
                $modinfo = get_fast_modinfo($item->courseid);
                $cm = $modinfo->cms[$newactivity->id];
                $link = new moodle_url('/course/view.php', array('id' => $item->courseid));
                $a->link = $link->out(false);
                break;
        }

        $message = new \core\message\message();
        $message->component = 'local_sharewith';
        $message->name = 'shared_notification';
        $message->userfrom = $item->sourceuserid;
        $message->userto = $item->userid;
        $message->subject = get_string($item->type . '_title', 'local_sharewith');
        $message->fullmessage = '';
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = get_string($item->type . '_fullmessage', 'local_sharewith', $a);
        $message->smallmessage = get_string('notification_smallmessage_copied', 'local_sharewith');
        $message->notification = 1;
        $message->replyto = "";
        $message->courseid = $item->courseid;
        $messageid = message_send($message);
    }

    public static function copy_users_mod_glossary($sourceactivityid, $targetactivityid) {
        global $DB, $CFG, $USER;

        $sql = '
        SELECT m.name, cm.instance, cm.course
        FROM {course_modules} cm
        LEFT JOIN {modules} m ON(cm.module = m.id)
        WHERE cm.id = ?
    ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));
        $target = $DB->get_record_sql($sql, array($targetactivityid));

        if($source->name == 'glossary' && $target->name == 'glossary'){
            require_once($CFG->dirroot . '/mod/glossary/lib.php');

            if ($glossary = $DB->get_record('glossary', array('id'=>$source->instance))) {

                // Build xml file.
                $content = glossary_generate_export_file($glossary, NULL, 0);

                // Upload XML.
                if (! $glossary = $DB->get_record("glossary", array("id"=>$target->instance))) {
                    print_error('invalidid', 'glossary');
                }

                if ($cm = get_coursemodule_from_id('glossary', $targetactivityid)) {
                    $context = context_module::instance($cm->id);

                    $data = new stdClass();
                    $data->id = $targetactivityid;
                    $data->dest = 'current';
                    $data->catsincl = 1;

                    if ($xml = glossary_read_imported_file($content)) {
                        $importedentries = 0;
                        $importedcats = 0;
                        $entriesrejected = 0;
                        $rejections = '';
                        $glossarycontext = $context;

                        if ($data->dest == 'newglossary') {
                            // If the user chose to create a new glossary
                            $xmlglossary = $xml['GLOSSARY']['#']['INFO'][0]['#'];

                            if ($xmlglossary['NAME'][0]['#']) {
                                $glossary = new stdClass();
                                $glossary->modulename = 'glossary';
                                $glossary->module = $cm->module;
                                $glossary->name = ($xmlglossary['NAME'][0]['#']);
                                $glossary->globalglossary = ($xmlglossary['GLOBALGLOSSARY'][0]['#']);
                                $glossary->intro = ($xmlglossary['INTRO'][0]['#']);
                                $glossary->introformat =
                                        isset($xmlglossary['INTROFORMAT'][0]['#']) ? $xmlglossary['INTROFORMAT'][0]['#'] :
                                                FORMAT_MOODLE;
                                $glossary->showspecial = ($xmlglossary['SHOWSPECIAL'][0]['#']);
                                $glossary->showalphabet = ($xmlglossary['SHOWALPHABET'][0]['#']);
                                $glossary->showall = ($xmlglossary['SHOWALL'][0]['#']);
                                $glossary->cmidnumber = null;

                                // Setting the default values if no values were passed
                                if (isset($xmlglossary['ENTBYPAGE'][0]['#'])) {
                                    $glossary->entbypage = ($xmlglossary['ENTBYPAGE'][0]['#']);
                                } else {
                                    $glossary->entbypage = $CFG->glossary_entbypage;
                                }
                                if (isset($xmlglossary['ALLOWDUPLICATEDENTRIES'][0]['#'])) {
                                    $glossary->allowduplicatedentries = ($xmlglossary['ALLOWDUPLICATEDENTRIES'][0]['#']);
                                } else {
                                    $glossary->allowduplicatedentries = $CFG->glossary_dupentries;
                                }
                                if (isset($xmlglossary['DISPLAYFORMAT'][0]['#'])) {
                                    $glossary->displayformat = ($xmlglossary['DISPLAYFORMAT'][0]['#']);
                                } else {
                                    $glossary->displayformat = 2;
                                }
                                if (isset($xmlglossary['ALLOWCOMMENTS'][0]['#'])) {
                                    $glossary->allowcomments = ($xmlglossary['ALLOWCOMMENTS'][0]['#']);
                                } else {
                                    $glossary->allowcomments = $CFG->glossary_allowcomments;
                                }
                                if (isset($xmlglossary['USEDYNALINK'][0]['#'])) {
                                    $glossary->usedynalink = ($xmlglossary['USEDYNALINK'][0]['#']);
                                } else {
                                    $glossary->usedynalink = $CFG->glossary_linkentries;
                                }
                                if (isset($xmlglossary['DEFAULTAPPROVAL'][0]['#'])) {
                                    $glossary->defaultapproval = ($xmlglossary['DEFAULTAPPROVAL'][0]['#']);
                                } else {
                                    $glossary->defaultapproval = $CFG->glossary_defaultapproval;
                                }

                                // These fields were not included in export, assume zero.
                                $glossary->assessed = 0;
                                $glossary->availability = null;

                                // New glossary is to be inserted in section 0, it is always visible.
                                $glossary->section = 0;
                                $glossary->visible = 1;
                                $glossary->visibleoncoursepage = 1;
                            }
                        }

                        $xmlentries = $xml['GLOSSARY']['#']['INFO'][0]['#']['ENTRIES'][0]['#']['ENTRY'];
                        $sizeofxmlentries = sizeof($xmlentries);
                        for ($i = 0; $i < $sizeofxmlentries; $i++) {
                            // Inserting the entries
                            $xmlentry = $xmlentries[$i];
                            $newentry = new stdClass();
                            $newentry->concept = trim($xmlentry['#']['CONCEPT'][0]['#']);
                            $definition = $xmlentry['#']['DEFINITION'][0]['#'];
                            if (!is_string($definition)) {
                                print_error('errorparsingxml', 'glossary');
                            }
                            $newentry->definition = trusttext_strip($definition);
                            if (isset($xmlentry['#']['CASESENSITIVE'][0]['#'])) {
                                $newentry->casesensitive = $xmlentry['#']['CASESENSITIVE'][0]['#'];
                            } else {
                                $newentry->casesensitive = $CFG->glossary_casesensitive;
                            }

                            $permissiongranted = 1;
                            if ($newentry->concept and $newentry->definition) {
                                if (!$glossary->allowduplicatedentries) {
                                    // checking if the entry is valid (checking if it is duplicated when should not be)
                                    if ($newentry->casesensitive) {
                                        $dupentry = $DB->record_exists_select('glossary_entries',
                                                'glossaryid = :glossaryid AND concept = :concept', array(
                                                        'glossaryid' => $glossary->id,
                                                        'concept' => $newentry->concept));
                                    } else {
                                        $dupentry = $DB->record_exists_select('glossary_entries',
                                                'glossaryid = :glossaryid AND LOWER(concept) = :concept', array(
                                                        'glossaryid' => $glossary->id,
                                                        'concept' => core_text::strtolower($newentry->concept)));
                                    }
                                    if ($dupentry) {
                                        $permissiongranted = 0;
                                    }
                                }
                            } else {
                                $permissiongranted = 0;
                            }


                            if ($permissiongranted) {
                                $newentry->glossaryid = $glossary->id;
                                $newentry->sourceglossaryid = 0;
                                $newentry->approved = 1;
                                $newentry->userid = $USER->id;
                                $newentry->teacherentry = 1;
                                $newentry->definitionformat = $xmlentry['#']['FORMAT'][0]['#'];
                                $newentry->timecreated = time();
                                $newentry->timemodified = time();

                                // Setting the default values if no values were passed
                                if (isset($xmlentry['#']['USEDYNALINK'][0]['#'])) {
                                    $newentry->usedynalink = $xmlentry['#']['USEDYNALINK'][0]['#'];
                                } else {
                                    $newentry->usedynalink = $CFG->glossary_linkentries;
                                }
                                if (isset($xmlentry['#']['FULLMATCH'][0]['#'])) {
                                    $newentry->fullmatch = $xmlentry['#']['FULLMATCH'][0]['#'];
                                } else {
                                    $newentry->fullmatch = $CFG->glossary_fullmatch;
                                }

                                $newentry->id = $DB->insert_record("glossary_entries", $newentry);
                                $importedentries++;

                                $xmlaliases = @$xmlentry['#']['ALIASES'][0]['#']['ALIAS']; // ignore missing ALIASES
                                $sizeofxmlaliases = sizeof($xmlaliases);
                                for ($k = 0; $k < $sizeofxmlaliases; $k++) {
                                    /// Importing aliases
                                    $xmlalias = $xmlaliases[$k];
                                    $aliasname = $xmlalias['#']['NAME'][0]['#'];

                                    if (!empty($aliasname)) {
                                        $newalias = new stdClass();
                                        $newalias->entryid = $newentry->id;
                                        $newalias->alias = trim($aliasname);
                                        $newalias->id = $DB->insert_record("glossary_alias", $newalias);
                                    }
                                }

                                if (!empty($data->catsincl)) {
                                    // If the categories must be imported...
                                    $xmlcats = @$xmlentry['#']['CATEGORIES'][0]['#']['CATEGORY']; // ignore missing CATEGORIES
                                    $sizeofxmlcats = sizeof($xmlcats);
                                    for ($k = 0; $k < $sizeofxmlcats; $k++) {
                                        $xmlcat = $xmlcats[$k];

                                        $newcat = new stdClass();
                                        $newcat->name = $xmlcat['#']['NAME'][0]['#'];
                                        $newcat->usedynalink = $xmlcat['#']['USEDYNALINK'][0]['#'];
                                        if (!$category = $DB->get_record("glossary_categories",
                                                array("glossaryid" => $glossary->id, "name" => $newcat->name))) {
                                            // Create the category if it does not exist
                                            $category = new stdClass();
                                            $category->name = $newcat->name;
                                            $category->glossaryid = $glossary->id;
                                            $category->id = $DB->insert_record("glossary_categories", $category);
                                            $importedcats++;
                                        }
                                        if ($category) {
                                            // inserting the new relation
                                            $entrycat = new stdClass();
                                            $entrycat->entryid = $newentry->id;
                                            $entrycat->categoryid = $category->id;
                                            $DB->insert_record("glossary_entries_categories", $entrycat);
                                        }
                                    }
                                }

                                // Import files embedded in the entry text.
                                glossary_xml_import_files($xmlentry['#'], 'ENTRYFILES', $glossarycontext->id, 'entry', $newentry->id);

                                // Import files attached to the entry.
                                if (glossary_xml_import_files($xmlentry['#'], 'ATTACHMENTFILES', $glossarycontext->id, 'attachment',
                                        $newentry->id)) {
                                    $DB->update_record("glossary_entries", array('id' => $newentry->id, 'attachment' => '1'));
                                }

                            } else {
                                $entriesrejected++;
                                if ($newentry->concept and $newentry->definition) {
                                    // add to exception report (duplicated entry))
                                    $rejections .= "<tr><td>$newentry->concept</td>" .
                                            "<td>" . get_string("duplicateentry", "glossary") . "</td></tr>";
                                } else {
                                    // add to exception report (no concept or definition found))
                                    $rejections .= "<tr><td>---</td>" .
                                            "<td>" . get_string("noconceptfound", "glossary") . "</td></tr>";
                                }
                            }
                        }

                        // Reset caches.
                        \mod_glossary\local\concept_cache::reset_glossary($glossary);
                    }
                }
            }
        }
    }
}
