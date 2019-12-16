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

$string['pluginname'] = 'שיתוף פעילויות';

$string['menucoursenode'] = 'העתק קורס ליחידה';
$string['wordcopy'] = 'העתק';
$string['defaultsectionname'] = 'יחידה';

// Cron.
$string['tasksharewith'] = 'פעילות שיתוף משימות';

// Settings.
$string['settingscoursecopy'] = 'העתק קורס';
$string['settingscoursecopydesc'] = 'הפעלה / השבתה של קורס העתקה';
$string['settingssectioncopy'] = 'העתק מיחידה';
$string['settingssectioncopydesc'] = 'הפעל / השבת את יחידה ההעתקה';
$string['settingsactivityteachercopy'] = 'העתק פעילות למורה';
$string['settingsactivityteachercopydesc'] = 'הפעלה / השבתה של פעילות העתקה למורה';
$string['settingsactivitycopy'] = 'העתק פעילות לעצמו';
$string['settingsactivitycopydesc'] = 'הפעלה / השבתה של פעילות העתקה לעצמו';
$string['settingsactivitysending'] = 'שליחת פעילות';
$string['settingsactivitysendingdesc'] = 'הפעלה / השבתה שליחת פעילות';

// Events.
$string['eventcoursecopy'] = 'העתק קורס';
$string['eventsectioncopy'] = 'העתק יחידה';
$string['eventactivitycopy'] = 'העתק פעילות';
$string['eventactivityshare'] = 'שיתוף פעילות';

// Modals.
$string['selectioncategories'] = 'בחר קטגוריה להעתקת הקורס';
$string['sectionselection'] = 'בחירת יחידה';
$string['selectcourse'] = 'בחירת קורס';
$string['selectcourse_and_section'] = 'בחירת קורס ויחידה';
$string['selecttopic'] = 'בחירת יחידה';
$string['close'] = 'סגור';
$string['cancel'] = 'ביטול';
$string['submit'] = 'שלח';
$string['approve'] = 'אישור';
$string['course_copied_to_section'] = 'קורס הועתק לקטגוריה ';
$string['activity_copied_to_course'] = 'פעילות הועתקה לקורס';
$string['section_copied_to_course'] = 'יחידה הועתקה לקורס';
$string['system_error_contact_administrator'] = 'שגיאת מערכת, פנה למנהל';
$string['mail_subject_shared_teacher'] = "שיתוף פעילות";

$string['eventcopytomaagar'] = "העתק למאגר";
$string['eventcopytoteacher'] = "שיתוף פעילות";
$string['eventdownloadtoteacher'] = "הורדת פעילות";
$string['eventdublicatetoteacher'] = "העתקת פעילות";
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility changed";

$string['menu_popup_title'] = "בחר כיצד אתה רוצה לשתף";
$string['menu_popup_maagar'] = "פירסום במאגר המשותף";
$string['menu_popup_send_teacher'] = "שלח למורה";
$string['menu_popup_back'] = "חזרה";
$string['share_with_teacher'] = "שיתוף עם מורה";
$string['teachers_youve_sent'] = "מורים ששלחת אליהם את הפריט";
$string['enter_teacher_here'] = "יש להקליד כאן את שם המורה...";
$string['comment_to_teacher'] = "כאן תכנס הערה שהמורה ישתף עם מי שבחר...";
$string['user_foto'] = "משתמש";
$string['nosharing'] = "עדיין לא נשלח";

$string['subject_message_for_teacher_by'] = 'פעילות {$a->activity_name} התווספה על ידי {$a->teacher_name}';
$string['subject_message_for_teacher'] = '{$a->teacher_name} תוכל לשתף פעילות {$a->activity_name} ממשתמש  ';
$string['fullmessagehtml_for_teacher'] = '<a data-handler="saveActivity" data-sharing="{$a->restore_id}" href="#">לחץ לשיתוף</a> תוכל לשתף פעילות ';
$string['info_message_for_teacher'] = 'הודעה מתוסף משיתוף';
$string['enter_subject_name'] = 'נא להזין את שם הפריט';

$string['share'] = 'שיתוף';
$string['copy'] = 'העתקה';
$string['how_to_share'] = 'כיצד לשתף?';
$string['share_national_shared'] = 'שיתוף במאגר המשותף הארצי';
$string['send_to_teacher'] = 'שלח למורה';
$string['transfer_another_course'] = 'העתקה לקורס אחר שלי';

$string['succesfullyshared'] = 'הבקשה עודכנה בהצלחה. יועתק לאחר מספר דקות. תודה!';

$string['activitycopy_title'] = 'פעילות';
$string['sectioncopy_title'] = 'יחדיה';
$string['coursecopy_title'] = 'קורס';
$string['notification_smallmessage_copied'] = 'מועתק בהצלחה';
$string['activitycopy_fullmessage'] = '<a href="{$a->link}">{$a->coursename}</a> מועתק בהצלחה לקורס';
$string['sectioncopy_fullmessage'] = '<a href="{$a->link}">{$a->coursename} מועתק בהצלחה לקורס</a> ';
$string['coursecopy_fullmessage'] = '<a href="{$a->link}">{$a->coursename}</a> מועתק בהצלחה לקורס';

// Sharing popup.
$string['choose'] = 'יש לבחור...';
$string['reduce_catalog_options'] = 'צימצום אפשרויות קטלוג';
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['please_enter_item_name'] = 'נא להזין את שם הפריט';
$string['error'] = 'שגיע';
$string['please_select_course_section'] = 'נא לבחור קורס וסעיף';
$string['sent'] = 'נשלח';
$string['fails'] = 'נכשל';
$string['sharing_sent_successfully'] = 'הזמנה לשיתוף נשלחה בהצלחה';
$string['staudysection'] = 'יחידת־הוראה ';
$string['select_sub_topic'] = 'בחירת סעיף בתת נושא';

$string['selectteacher'] = 'בחר';
$string['activitydeleted'] = 'הפעילות נמחק';
$string['sendingnotallowed'] = 'אין הרשאה לשלוח';

$string['rolesoptions'] = 'תפקידים שניתן לשתף את הפעילות';
$string['rolesdisplayoptions'] = 'תפקידים שמאפשרים לעשות פעולה שיתוף';
