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

$string['tasksharewith'] = 'Task sharewith';

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

$string['eventcoursecopy'] = 'Copy course';
$string['eventsectioncopy'] = 'Copy topic';
$string['eventactivitycopy'] = 'Copy activity';
$string['eventactivityshare'] = 'Share activity';

$string['selectioncategories'] = 'Select category';
$string['sectionselection'] = 'Select topic';
$string['selectcourse'] = 'Select course';
$string['uploadactivity'] = 'Upload activity';
$string['selectcourse_and_section'] = 'Select course and topic';
$string['selecttopic'] = 'Select topic';
$string['selectcourse'] = 'Select course';
$string['close'] = 'Close';
$string['cancel'] = 'Cancel';
$string['submit'] = 'Submit';
$string['approve'] = 'Ok';
$string['finish'] = 'Ok';
$string['course_copied_to_section'] = 'Course is copied to the category';
$string['activity_copied_to_course'] = 'Activity is copied to the course';
$string['section_copied_to_course'] = 'Topic is copied to the course';
$string['system_error_contact_administrator'] = 'System error, contact administrator';

$string['error_coursecopy'] = 'Course copy disabled on the plugin settigs';
$string['error_sectioncopy'] = 'Section copy disabled on the plugin settigs';
$string['error_activitycopy'] = 'Activity copy disabled on the plugin settigs';
$string['error_permission_allow_copy'] = 'Not enough permissions to copy, contact administrator';
$string['error_permission_allow_share'] = 'Not enough permissions to share, contact administrator';

$string['eventcopytomaagar'] = "Copy to Database";
$string['eventcopytoteacher'] = "Share to teachers";
$string['eventdownloadtoteacher'] = "Download activity";
$string['eventdublicatetoteacher'] = "Copy activity";
$string['activity_copied_to_course'] = 'Activity is copied to the course';
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility cahnged";

$string['menu_popup_title'] = "Choose how you want to share";
$string['menu_popup_maagar'] = "Post in the Shared Database";
$string['menu_popup_send_teacher'] = "Send to Teacher";
$string['back'] = "back";
$string['send'] = "Send";
$string['share_with_teacher'] = "Share with a teacher";
$string['teachers_youve_sent'] = "Teachers you've sent the item to";
$string['enter_teacher_here'] = "Enter the name of the teacher here ...";
$string['comment_to_teacher'] = "Here a comment will be made that the teacher will share with the person who has chosen ...";
$string['user_foto'] = "User foto";
$string['nosharing'] = "No one sent yet";

$string['activity_upload_to_mr'] = 'Activity {$a->activitytitle} has been sent to the shared repository and will be available to all teachers as soon as possible
thanks for sharing!';
$string['subject_message_for_teacher_by'] = 'Activity {$a->activity_name} added by {$a->teacher_name}';
$string['subject_message_for_teacher'] = 'Teacher {$a->teacher_name} share to you activity {$a->activity_name}';
$string['fullmessagehtml_for_teacher'] =
        'Share it to you course <a data-handler="selectCourse" data-cm="{$a->activityid}" href="#">link</a> <br>Ask the teacher about the activity <a href="{$a->teacherlink}">link</a>.';
$string['info_message_for_teacher'] = 'Message from Sharing Activity';
$string['enter_subject_name'] = 'Enter the name of the subject';
$string['succesfullyshared'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';
$string['succesfullycopied'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';

$string['activitycopy_title'] = 'Activity';
$string['activityshare_title'] = 'Activity';
$string['sectioncopy_title'] = 'Topic';
$string['coursecopy_title'] = 'Course';
$string['notification_smallmessage_copied'] = 'Successfully copied!';
$string['activitycopy_fullmessage'] = 'Your activity was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['activityshare_fullmessage'] = 'Your activity was successfully saved to the <a href="{$a->link}">{$a->coursename}</a>';
$string['sectioncopy_fullmessage'] = 'Your topic was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['coursecopy_fullmessage'] = 'Your course was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';

$string['share'] = 'Share';
$string['copy'] = 'Copy';
$string['how_to_share'] = 'How to share ?';
$string['share_national_shared'] = 'Share the national shared database';
$string['send_to_teacher'] = 'Send to the teacher';
$string['transfer_another_course'] = 'Transfer to another course';

$string['choose'] = 'Choose...';
$string['reduce_catalog_options'] = 'Reduce catalog options';
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['please_enter_item_name'] = 'Please enter the item name';
$string['error'] = 'Error';
$string['please_select_course_section'] = 'Please select course and topic';
$string['sent'] = 'Sent';
$string['fails'] = 'Fails';
$string['sharing_sent_successfully'] = 'A sharing invitation has been sent successfully';
$string['studysection'] = 'Study Topic';

$string['selectteacher'] = 'Select Teacher';
$string['activitydeleted'] = 'This activity was deleted by author.';
$string['sendingnotallowed'] = 'Share activities disabled by administrator.';

$string['insert_mails'] = 'Administration users';
$string['insert_mails_desc'] = 'Example: email1@google.com,email2@google.com';

$string['course_count_label'] = 'Number of Courses to show';
$string['search_label'] = 'Search:';
$string['searchcourses:addinstance'] = 'Add Search Courses block';
$string['searchcourses:myaddinstance'] = 'Add Search Courses block to My Home';
$string['setting_inserticonswithlinks'] = 'Insert icons with links';
$string['setting_inserticonswithlinks_desc'] = 'Setup the menu (only text), each item in a new line.';

$string['system_error'] = 'System error';
$string['course_error'] = 'Dear teachers, activity can not be shared';

$string['category_error'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.<br>
You must check and categorize these questions before uploading the examiner to the shared repository.<br>
For questions / clarifications, please contact: petel@weizmann.ac.il';

$string['category_error_teacher'] = 'Dear teacher,<br>
At least some of the questions in your exam do not belong to the "Default examiner" category.<br>
You must check and categorize these questions before uploading the examiner to the shared repository.<br>
For questions / clarifications, please contact: petel@weizmann.ac.il';

$string['sharing_content_materials_repository'] =
        'Pay attention! By sharing this activity, you will be able to access the activity content without the performance of your students to all physics teachers who use the PeTeL environment. Please ensure that the content that appears in the activity is standardized';
$string['item_name'] = 'item name:';
$string['availability_describe'] = 'שימו לב! פעילות זו היא חלק מרצף הוראה. ברצונכם לשתף את כל שאר הפריטים ברצף למאגר המשוותף?';
$string['define_item_cataloged'] = 'Define where the item will be cataloged in the shareport';
$string['select_main_topic'] = 'Select Main Topic';
$string['assignment_appropriate_topics'] = 'Assignment to appropriate topics';
$string['select_sub_topic'] = 'Select a sub-topic';
$string['choose'] = 'select';
$string['add_association'] = '+ Add association';
$string['remove_association'] = '- Remove association';
$string['mark_recommended'] = 'Check what are the recommended uses for this activity ?';
$string['difficulty_of_activity'] = 'Activity difficulty *';
$string['duration_of_activity'] = 'Duration of activity';
$string['rely_other_activity'] = 'Did you rely on other activity development activities';
$string['rely_other_activity_no'] = 'No. The copyright of the activity is only mine';
$string['rely_other_activity_yes'] = 'Yes. I translated / translated the activity on the basis of another activity';
$string['register_resource'] = 'Record where the resource is from and add a link to it if there is';
$string['advanced_catalog_options'] = 'Advanced Catalog Options';
$string['advanced_catalog_options_2'] = 'Advanced Catalog';
$string['advanced_catalog_options_3'] = 'Permission fields to help locate the item in the shared repository ';
$string['summary'] = 'Summary / Purpose of Activity';
$string['summary_of_activity'] = 'Record a summary here about the activity';
$string['teacherremarks'] = 'Teacher remarks';
$string['tag_item'] = 'Tag the item to enable quick detection in the repository';
$string['first_tag'] = 'first tag';
$string['add_tag'] = 'Add tag';
$string['technical_evaluations'] = 'If technical evaluations are required, please mark it here';
$string['mobile_and_desktop'] = 'Mobile and Computer';
$string['only_desktop'] = 'Computer only';
$string['feedback_activity'] = 'What is the feedback in this activity';
$string['feedback_during_activity'] = 'Feedback during activity';
$string['includes_hints'] = 'Includes hints';
$string['includes_example'] = 'Includes example of solution';
$string['validation'] = 'Validation';
$string['general_comments'] = 'General Comments';
$string['add_image'] = 'Add an image to represent the activity';
$string['select_image'] = 'Select image to upload';
$string['quick_drum'] = "Quick Share";
$string['write_tags_here'] = "Type a tag name";
$string['mail_subject_add_activity'] = "New activity added to repository";
$string['mail_subject_shared_teacher'] = "Share activity";

$string['subject_message_for_teacher_by'] = 'Activity {$a->activity_name} added by {$a->teacher_name}';

$string['eventcopytomaagar'] = "Copy to Database";
$string['eventcopytoteacher'] = "Share activity";
$string['eventdownloadtoteacher'] = "Download activity";
$string['eventdublicatetoteacher'] = "Copy activity";
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility cahnged";

$string['share'] = 'Share';
$string['copy'] = 'Copy';
$string['how_to_share'] = 'How to share ?';
$string['share_national_shared'] = 'Share the national shared database';
$string['send_to_teacher'] = 'Send to the teacher';
$string['transfer_another_course'] = 'Transfer to another course';

$string['choose'] = 'Choose...';
$string['reduce_catalog_options'] = 'Reduce catalog options';
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['please_enter_item_name'] = 'Please enter the item name';
$string['error'] = 'Error';
$string['please_select_course_section'] = 'Please select course and section';
$string['sent'] = 'Sent';
$string['fails'] = 'Fails';
$string['loading'] = 'Loading...';
$string['sharing_sent_successfully'] = 'A sharing invitation has been sent successfully';

$string['settingscatalogcategoryid'] = 'Catalog category for upload';
$string['settingscatalogcategoryiddesc'] = 'Catalog category for upload';
$string['succesfullyrecieved'] = 'Succesfully recieved';

$string['select_desired_action'] = 'Select the desired action';
$string['messageprovider:sharewith_notification'] = 'Share with';
$string['messageprovider:shared_notification'] = 'Shared';

$string['ask_question_before_copying'] =
        'Hi! I got a link to copy the activity {$a->activityname}, And I have a question about the item. I wanted to ask...';
$string['no_accessible_category'] = 'There is no accessible category for copying course';

$string['rolesoptions'] = 'Sharing roles permissions ';
$string['rolesdisplayoptions'] = 'Roles that can be shared activities';
// Errors.
$string['error:invalidparams'] = 'Data entry error, contact administrator';
$string['error:db'] = 'Database error, contact administrator';
$string['error:message'] = 'Error sending message, contact administrator';
$string['error:teacherpermission'] = 'Error! One of the teachers has no access rights for sharing, contact administrator';
