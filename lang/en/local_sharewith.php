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
 * Plugin strings are defined here.
 *
 * @package     local_sharewith
 * @category    string
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Share With';

$string['menucoursenode'] = 'Copy course to category';
$string['wordcopy'] = 'Share';
$string['generalsectionname'] = 'General';

// Cron.
$string['tasksharewith'] = 'Task sharewith';

// Settings.
$string['settingscoursecopy'] = 'Copy course';
$string['settingscoursecopydesc'] = 'Enable/disable copy courses';
$string['settingssectioncopy'] = 'Copy topic';
$string['settingssectioncopydesc'] = 'Enable/disable copy topics';
$string['settingsactivityteachercopy'] = 'Share activity to teacher';
$string['settingsactivityteachercopydesc'] = 'Enable/disable share activities to teacher';
$string['settingsactivitycopy'] = 'Copy activity';
$string['settingsactivitycopydesc'] = 'Enable/disable copy activities';
$string['settingsactivitysending'] = 'Send activity';
$string['settingsactivitysendingdesc'] = 'Enable/disable send activities';

// Events.
$string['eventcoursecopy'] = 'Copy course';
$string['eventsectioncopy'] = 'Copy topic';
$string['eventactivitycopy'] = 'Copy activity';
$string['eventactivityshare'] = 'Share activity';

// Modals.
$string['selectioncategories'] = 'Select category';
$string['sectionselection'] = 'Select topic';
$string['selectcourse'] = 'Select course';
$string['selectcourse_and_section'] = 'Select course and topic';
$string['selecttopic'] = 'Select topic';
$string['close'] = 'Close';
$string['cancel'] = 'Cancel';
$string['submit'] = 'Submit';
$string['approve'] = 'Ok';
$string['course_copied_to_section'] = 'Course is copied to the category';
$string['activity_copied_to_course'] = 'Activity is copied to the course';
$string['section_copied_to_course'] = 'Topic is copied to the course';
$string['system_error_contact_administrator'] = 'System error, contact administrator';
$string['eventcopytomaagar'] = "Copy to Database";
$string['eventcopytoteacher'] = "Share to teachers";
$string['eventdownloadtoteacher'] = "Download activity";
$string['eventdublicatetoteacher'] = "Copy activity";
$string['activity_copied_to_course'] = 'Activity is copied to the course';
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility cahnged";

$string['menu_popup_title'] = "Choose how you want to share";
$string['menu_popup_maagar'] = "Post in the Shared Database";
$string['menu_popup_send_teacher'] = "Send to Teacher";
$string['menu_popup_back'] = "back";
$string['menu_popup_send'] = "send";
$string['share_with_teacher'] = "Share with a teacher";
$string['teachers_youve_sent'] = "Teachers you've sent the item to";
$string['enter_teacher_here'] = "Enter the name of the teacher here ...";
$string['comment_to_teacher'] = "Here a comment will be made that the teacher will share with the person who has chosen ...";
$string['user_foto'] = "User foto";
$string['nosharing'] = "No one sent yet";

$string['subject_message_for_teacher_by'] = 'Activity {$a->activity_name} added by {$a->teacher_name}';
$string['subject_message_for_teacher'] = 'Teacher {$a->teacher_name} share to you activity {$a->activity_name}';
$string['fullmessagehtml_for_teacher'] = 'Share it to you course <a data-handler="saveActivity" data-sharing="{$a->restore_id}" href="#">link</a> <br>Ask the teacher about the activity <a href="{$a->teacherlink}">link</a>.';
$string['info_message_for_teacher'] = 'Message from Sharing Activity';
$string['enter_subject_name'] = 'Enter the name of the subject';
$string['succesfullyshared'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';

$string['activitycopy_title'] = 'Activity';
$string['sectioncopy_title'] = 'Section';
$string['coursecopy_title'] = 'Course';
$string['notification_smallmessage_copied'] = 'Successfully copied!';
$string['activitycopy_fullmessage'] = 'Your activity was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['sectioncopy_fullmessage'] = 'Your section was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['coursecopy_fullmessage'] = 'Your course was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';

$string['share'] = 'Share';
$string['copy'] = 'Copy';
$string['how_to_share'] = 'How to share ?';
$string['share_national_shared'] = 'Share the national shared database';
$string['send_to_teacher'] = 'Send to the teacher';
$string['transfer_another_course'] = 'Transfer to another course';

// Sharing popup.
$string['choose'] = 'Choose...';
$string['reduce_catalog_options'] = 'Reduce catalog options';
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['please_enter_item_name'] = 'Please enter the item name';
$string['error'] = 'Error';
$string['please_select_course_section'] = 'Please select course and section';
$string['sent'] = 'Sent';
$string['fails'] = 'Fails';
$string['sharing_sent_successfully'] = 'A sharing invitation has been sent successfully';
$string['staudysection'] = 'Study Section';

$string['selectteacher'] = 'Select Teacher';
$string['activitydeleted'] = 'This activity was deleted by author.';
$string['sendingnotallowed'] = 'Share activities disabled by administrator.';
