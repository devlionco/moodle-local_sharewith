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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_sharewith
 * @category    upgrade
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_sharewith_install() {
    global $DB;
    $dbman = $DB->get_manager();

    if (!$dbman->table_exists('local_sharewith_task')) {
        $table = new xmldb_table('local_sharewith_task');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('sourceuserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourcecourseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourcesectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourceactivityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('metadata', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    //============================================================================================================================================
    if (!$dbman->table_exists('local_sharewith_shared')) {
        $table = new xmldb_table('local_sharewith_shared');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('useridfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('restoreid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('complete', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('source', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    return true;
}
