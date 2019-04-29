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
 * @module     local_sharewith/buildtree
 * @package    local_sharewith
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str'
], function ($, Str) {

    return /** @alias module:local_sharewith/buildtree */ {
        SELECTORS: {
            editBlock: '.section-cm-edit-actions.commands',
            menuActivity: '.course-content li.activity [role="menu"]',
            activityItem: '.course-content li.activity',
            menuSection: '.course-content li.section .section_action_menu [role="menu"]',
            modal: '#selectItem'
        },

        /**
         * Clone and add the button for copying activity.
         *
         * @method addCopyActivityButton
         * @param {jquery} root The root element.
         */
        addCopyActivityButton: function (root) {
            root = $(root);
            var string = Str.get_string('eventactivitycopy', 'local_sharewith'),
                menu = root.find(this.SELECTORS.menuActivity),
                self = this;
            menu.each(function () {
                var clone = $(this).children().last().clone();
                string.done(function (s) {
                    clone
                        .find('.menu-action-text')
                        .text(s);
                    clone
                        .attr('data-toggle', 'modal')
                        .attr('data-target', self.SELECTORS.modal)
                        .attr('href', '#')
                        .attr('data-handler', 'selectCourse')
                        .removeAttr('data-action')
                        .addClass('sharingact_item');
                    clone
                        .find('.icon')
                        .attr('title', s)
                        .attr('aria-label', s)
                        .removeAttr('class')
                        .addClass('icon fa fa-copy fa-fw');
                });
                $(this).append(clone);
            });
        },

        /**
         * Clone and add the button for copying section.
         *
         * @method addCopyActivityButton
         * @param {jquery} root The root element.
         */
        addCopySectionButton: function (root) {
            root = $(root);
            var string = Str.get_string('eventsectioncopy', 'local_sharewith'),
                menu = root.find(this.SELECTORS.menuSection),
                self = this;
            menu.each(function () {
                var clone = $(this).children().last().clone();
                clone
                    .attr('data-toggle', 'modal')
                    .attr('data-target', self.SELECTORS.modal)
                    .attr('href', '#')
                    .attr('data-handler', 'selectCourse')
                    .attr('data-ref', 'copySection');
                string.done(function (s) {
                    clone.find('.menu-action-text').text(s);
                    clone
                        .find('.icon')
                        .removeAttr('class')
                        .attr('title', s)
                        .attr('aria-label', s)
                        .addClass('icon fa fa-copy fa-fw');
                });
                $(this).append(clone);
            });
        }

    };
});
