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
 * The local_sharewith chapter viewed event.
 *
 * @package    local_sharewith
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sharewith\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Class course_copied
 * @package local_sharewith\event
 */
class course_copied extends \core\event\base {

    /**
     * @param $id
     * @param $eventdata
     * @return chapter_viewed
     */
    public static function create_event($id, $eventdata) {

        $contextid = \context_course::instance($id);

        $data = array(
                'context' => $contextid,
                'other' => $eventdata
        );
        /** @var chapter_viewed $event */

        $event = self::create($data);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $userid = $this->other['userid'];
        $newcourseid = $this->other['newcourseid'];
        $oldcourseid = $this->other['oldcourseid'];
        $coursename = $this->other['coursename'];

        return "The user with id '$userid' copied course id '$oldcourseid' to course '$coursename' with id '$newcourseid'";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array();
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcoursecopied', 'local_sharewith');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * @return array
     */
    public static function get_objectid_mapping() {
        return array();
    }
}