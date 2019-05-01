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
 * Javascript controller for the "Actions" panel at the bottom of the page.
 *
 * @module     local_sharewith/init
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'local_sharewith/buildtree',
    'local_sharewith/events',
    'local_sharewith/modal'
], function ($, buildtree, events, modal) {

    var root = document.querySelector('body');
    return {
        init: function (sectioncopy, activitycopyhimself) {
            events.getCurrentCourse();
            modal.insertTemplates();

            if (Number(activitycopyhimself) === 1) {
                buildtree.addCopyActivityButton(root);
            }

            if (Number(sectioncopy) === 1) {
                buildtree.addCopySectionButton(root);
            }

            root.addEventListener('click', function (e) {
                var target = e.target;
                while (target !== root) {
                    // Open popup and choose a category for copying the current course.
                    if (target.classList.contains('selectCategory')) {
                        events.selectCategory();
                        return;
                    }
                    if (target.dataset.handler === 'copyCourseToCategory') {
                        events.copyCourseToCategory();
                        return;
                    }
                    if (target.dataset.handler === 'selectCourse') {
                        events.selectCourse(target);
                        return;
                    }
                    if (target.dataset.handler === 'selectSection') {
                        events.selectSection();
                        return;
                    }
                    if (target.dataset.handler === 'copyActivityToCourse') {
                        events.copyActivityToCourse();
                        return;
                    }
                    if (target.dataset.handler === 'copySectionToCourse') {
                        events.copySectionToCourse();
                        return;
                    }
                    target = target.parentNode;
                }
            });
        }
    };

});
