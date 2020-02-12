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
$string['wordcopy'] = 'העתקה';
$string['defaultsectionname'] = 'יחידת־הוראה';

$string['tasksharewith'] = 'פעילות שיתוף משימות';

$string['settingscoursecopy'] = 'העתק קורס';
$string['settingscoursecopydesc'] = 'הפעלה / השבתה של קורס העתקה';
$string['settingssectioncopy'] = 'העתק מיחידה';
$string['settingssectioncopydesc'] = 'הפעל / השבת את יחידה ההעתקה';
$string['settingsactivityteachercopy'] = 'העתק פעילות למורה';
$string['settingsactivityteachercopydesc'] = 'הפעלה / השבתה של פעילות העתקה למורה';
$string['settingsactivitycopy'] = 'העתק פעילות לעצמו';
$string['settingsactivitycopydesc'] = 'הפעלה / השבתה של פעילות העתקה לעצמו';
$string['settingsactivitysending'] = 'Send activity';
$string['settingsactivitysendingdesc'] = 'Enable/disable send activities';

$string['eventcoursecopy'] = 'העתק קורס';
$string['eventsectioncopy'] = 'העתק יחידה';
$string['eventactivitycopy'] = 'העתק פעילות';
$string['eventactivityshare'] = 'Share activity';

$string['selectioncategories'] = 'בחירת קטגוריה להעתקת הקורס';
$string['sectionselection'] = 'בחירת יחידת־הוראה';
$string['selectcourse'] = 'בחירת קורס';
$string['uploadactivity'] = 'העלאת פעילות';
$string['selectcourse_and_section'] = 'בחירת קורס ויחידה';
$string['selecttopic'] = 'בחירת יחידה';
$string['selectcourse'] = 'בחירת קורס';
$string['close'] = 'סגירה';
$string['cancel'] = 'ביטול';
$string['submit'] = 'שליחה';
$string['approve'] = 'אישור';
$string['finish'] = 'סיום';
$string['course_copied_to_section'] = 'קורס הועתק לקטגוריה ';
$string['activity_copied_to_course'] = 'פעילות הועתקה לקורס';
$string['section_copied_to_course'] = 'יחידה הועתקה לקורס';
$string['system_error_contact_administrator'] = 'שגיאת מערכת, יש לפנות למנהל';
$string['mail_subject_shared_teacher'] = "שיתוף פעילות";

$string['error_coursecopy'] = 'Course copy disabled on the plugin settigs';
$string['error_sectioncopy'] = 'Section copy disabled on the plugin settigs';
$string['error_activitycopy'] = 'Activity copy disabled on the plugin settigs';
$string['error_permission_allow_copy'] = 'Not enough permissions to copy, contact administrator';
$string['error_permission_allow_share'] = 'Not enough permissions to share, contact administrator';

$string['eventcopytomaagar'] = "העתק למאגר";
$string['eventcopytoteacher'] = "שיתוף פעילות";
$string['eventdownloadtoteacher'] = "הורדת פעילות";
$string['eventdublicatetoteacher'] = "העתקת פעילות";
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility cahnged";

$string['menu_popup_title'] = "בחר כיצד אתה רוצה לשתף";
$string['menu_popup_maagar'] = "פירסום במאגר המשותף";
$string['menu_popup_send_teacher'] = "שלח למורה";
$string['send'] = "שלח";

$string['back'] = "חזרה";
$string['share_with_teacher'] = "שיתוף עם מורה";
$string['teachers_youve_sent'] = "מורים ששלחת אליהם את הפריט";
$string['enter_teacher_here'] = "יש להקליד כאן את שם המורה...";
$string['comment_to_teacher'] = "כאן תכנס הערה שהמורה ישתף עם מי שבחר...";
$string['user_foto'] = "משתמש";
$string['nosharing'] = "עדיין לא נשלח";

$string['activity_upload_to_mr'] = 'הפעילות {$a->activitytitle} נשלחה למאגר המשותף, ותהיה זמינה לכלל המורים בהקדם
תודה על השיתוף!';
$string['subject_message_for_teacher_by'] = 'פעילות {$a->activity_name} התווספה על ידי {$a->teacher_name}';
$string['subject_message_for_teacher'] = 'You can share activity-{$a->activity_name} from teacher {$a->teacher_name}';
$string['fullmessagehtml_for_teacher'] =
        'You can share activity <a data-handler="selectCourse" data-uid="{$a->uid}" data-cmid="{$a->activityid}" href="#">here</a>';
$string['info_message_for_teacher'] = 'Message from Sharing Activity';
$string['enter_subject_name'] = 'נא להזין את שם הפריט';

$string['share'] = 'שיתוף';
$string['copy'] = 'העתקה';
$string['how_to_share'] = 'כיצד לשתף?';
$string['share_national_shared'] = 'שיתוף במאגר המשותף הארצי';
$string['send_to_teacher'] = 'שלח למורה';
$string['transfer_another_course'] = 'העתקה לקורס אחר שלי';

$string['succesfullyshared'] = 'The request was successfully updated. It will copied after number of minutes. Thank you!';

$string['activitycopy_title'] = 'Activity';
$string['sectioncopy_title'] = 'Section';
$string['coursecopy_title'] = 'Course';
$string['notification_smallmessage_copied'] = 'Successfully copied!';
$string['activitycopy_fullmessage'] = 'Your activity was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['sectioncopy_fullmessage'] = 'Your section was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';
$string['coursecopy_fullmessage'] = 'Your course was successfully copied to the <a href="{$a->link}">{$a->coursename}</a>';

$string['choose'] = 'יש לבחור...';
$string['reduce_catalog_options'] = 'צימצום אפשרויות קטלוג';
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['please_enter_item_name'] = 'נא להזין את שם הפריט';
$string['error'] = 'שגיאה';
$string['please_select_course_section'] = 'נא לבחור קורס וסעיף';
$string['sent'] = 'נשלח';
$string['fails'] = 'נכשל';
$string['sharing_sent_successfully'] = 'הזמנה לשיתוף נשלחה בהצלחה';
$string['studysection'] = 'יחידת־הוראה ';
$string['select_sub_topic'] = 'בחירת סעיף בתת נושא';

$string['selectteacher'] = 'בחירת מורה';
$string['activitydeleted'] = 'This activity was deleted by author.';
$string['sendingnotallowed'] = 'Share activities disabled by administrator.';

$string['insert_mails'] = 'Administration users';
$string['insert_mails_desc'] = 'Example: email1@google.com,email2@google.com';

$string['course_count_label'] = 'מספר קורסים להצגה';
$string['search_label'] = 'חיפוש:';
$string['searchcourses:addinstance'] = 'Add Search Courses block';
$string['searchcourses:myaddinstance'] = 'Add Search Courses block to My Home';

$string['system_error'] = 'שגיאת מערכת';
$string['course_error'] = 'מורים יקרים, לא ניתן לשתף פעילות';

$string['category_error'] = 'מורה יקר/ה,<br>
לפחות חלק מהשאלות בבוחן שלך לא שייכות לקטגוריה "בררת מחדל של בוחן".<br>
עליך לבדוק ותלקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.<br>
לשאלות/הבהרות ניתן לפנות ל: petel@weizmann.ac.il';

$string['category_error_teacher'] = 'מורה יקר/ה,<br>
לפחות חלק מהשאלות בבוחן שלך לא שייכות לקטגוריה "בררת מחדל של בוחן".<br>
עליך לבדוק ותלקן את השיוך של השאלות האלה לפני העלאת הבוחן למאגר המשותף.<br>
לשאלות/הבהרות ניתן לפנות ל: petel@weizmann.ac.il';

$string['sharing_content_materials_repository'] =
        'שימו לב! שיתוף פעילות זו, תאפשר גישה לתוכן הפעילות ללא ביצועי תלמידיכם לכלל מורי הפיזיקה המשתמשים בסביבת PeTeL. אנא הקפידו על תקניות התכנים אשר מופיעים בפעילות';
$string['item_name'] = ':שם הפריט';
$string['availability_describe'] = 'שימו לב! פעילות זו היא חלק מרצף הוראה. ברצונכם לשתף את כל שאר הפריטים ברצף למאגר המשוותף?';
$string['glossary_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['database_describe'] = 'האם ברצונך ליבא נתונים לפעולות הזאת?';
$string['define_item_cataloged'] = 'הגדירו היכן יקוטלג הפריט במאגר המשתוף';
$string['select_main_topic'] = 'בחירת נושא ראשי';
$string['assignment_appropriate_topics'] = 'בחירת תת נושא';
$string['choose'] = 'תבחר';
$string['add_association'] = 'הוספת שיוך +';
$string['remove_association'] = '- הסר שיוך';
$string['mark_recommended'] = 'סמנו מהם השימושים המומלצים לפעילות זו ?';
$string['difficulty_of_activity'] = '* רמת קושי של הפעילות';
$string['duration_of_activity'] = 'משך זמן הפעילות';
$string['rely_other_activity'] = 'האם הסתמכת על פעילויות אחרות בפיתוח הפעילות';
$string['rely_other_activity_no'] = 'לא. זכויות היוצרים של הפעילות הינם רק שלי';
$string['rely_other_activity_yes'] = 'כן. עיבדתי/ תירגמתי את הפעילות על בסיס פעילות אחרת';
$string['register_resource'] = 'רישמו מהיכן המשאב והוסיפו קישור אליו במידה ויש';
$string['advanced_catalog_options'] = 'אפשרויות קטלוג מתקדמות';
$string['advanced_catalog_options_2'] = 'קטלוג מתקדם';
$string['advanced_catalog_options_3'] = 'שדות רשות המסייעים באיתור מהיר של הפריט במאגר המשותף';
$string['summary'] = 'תקציר / מטרת הפעילות';
$string['summary_of_activity'] = 'רשמו כאן תקציר אודות הפעילות';
$string['teacherremarks'] = 'Teacher remarks';
$string['tag_item'] = 'תיגו את הפריט על מנת לאפשר איתור מהיר שלו במאגר';
$string['first_tag'] = 'תגית ראשונה';
$string['add_tag'] = 'הוספת תגית';
$string['technical_evaluations'] = 'במידה ונדרשת הערכות טכנית, יש לסמן אותה כאן';
$string['mobile_and_desktop'] = 'מובייל ומחשב';
$string['only_desktop'] = 'מחשב בלבד';
$string['feedback_activity'] = 'מהו המשוב הקיים בפעילות זו';
$string['feedback_during_activity'] = 'משוב במהלך הפעילות';
$string['includes_hints'] = 'כולל רמזים';
$string['includes_example'] = 'כולל דוגמא לפתרון';
$string['validation'] = 'תיקוף';
$string['general_comments'] = 'הערות כלליות';
$string['add_image'] = 'הוספת תמונה שתייצג את הפעילות';
$string['select_image'] = 'בחירת תמונה להעלאה';
$string['quick_drum'] = "שיתוף מהיר";
$string['write_tags_here'] = "נא להקליד שם תג";
$string['mail_subject_add_activity'] = "פעילות חדשה התווספה למאגר";

$string['subject_message_for_teacher_by'] = 'פעילות {$a->activity_name} התווספה על ידי {$a->teacher_name}';

$string['mail_subject_shared_teacher'] = "שיתוף פעילות";

$string['eventcopytomaagar'] = 'העתק למאגר';
$string['eventcopytoteacher'] = 'שיתוף פעילות';
$string['eventdownloadtoteacher'] = 'הורדת פעילות';
$string['eventdublicatetoteacher'] = 'העתקת פעילות';
$string['eventcoursemodulevisibilitychanged'] = "Course module visibility cahnged";

$string['share'] = 'שיתוף';
$string['copy'] = 'העתקה';
$string['loading'] = 'מבצע שליחה...';
$string['how_to_share'] = 'כיצד לשתף?';
$string['share_national_shared'] = 'שיתוף במאגר המשותף הארצי';
$string['send_to_teacher'] = 'שלח למורה';
$string['transfer_another_course'] = 'העתקה לקורס אחר שלי';

$string['settingscatalogcategoryid'] = 'Catalog category for upload';
$string['settingscatalogcategoryiddesc'] = 'Catalog category for upload';
$string['succesfullyrecieved'] = 'התקבל בהצלחה';

$string['select_desired_action'] = 'בחירת פעולה רצויה';

$string['ask_question_before_copying'] =
        'היי! קיבלתי קישור להעתקת הפעילות {$a->activityname}, ויש לי שאלה לגבי הפריט. רציתי לשאול...';
$string['no_accessible_category'] = 'אין קטגוריה נגישה לקורס העתקה';

$string['rolesoptions'] = 'תפקידים שניתן לשתף את הפעילות';
$string['rolesdisplayoptions'] = 'תפקידים שמאפשרים לעשות פעולה שיתוף';
// Errors.
$string['error:invalidparams'] = 'שגיאת הזנת נתונים, צור קשר עם מנהל המערכת';
$string['error:db'] = 'שגיאת בסיס נתונים, צור קשר עם מנהל המערכת';
$string['error:message'] = 'שגיאה בשליחת הודעה, צור קשר עם מנהל המערכת';
$string['error:teacherpermission'] = 'שגיאה! לאחד המורים אין זכויות גישה לשיתוף, צור קשר עם מנהל המערכת';
