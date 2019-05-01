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
 * @module     local_sharewith/events
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'local_sharewith/modal'
], function ($, Str, Ajax, modal) {

    return /** @alias module:local_sharewith/events */ {

        ICON: {
            spinner: 'circle-loading',
            component: 'local_sharewith'
        },

        /**
         * Choose a category for copying the course.
         *
         * @method chooseCategory
         */
        selectCategory: function () {
            var modalBody = modal.getBody();

            var renderPopup = function (responce) {

                var categories = Object.values(JSON.parse(responce.categories));
                modal.init();
                Str.get_string('selectioncategories', 'local_sharewith').done(function (s) {
                    modal.getTitle().text(s);
                });
                modalBody
                    .append($('<select class = "categories form-control"></select>'));
                categories.forEach(function (category) {
                    modalBody.find('.categories')
                        .append($('<option data-categoryid =' + category.id + '>' + category.name + '</option>'));
                });
                this.setHandler('copyCourseToCategory');
            }.bind(this);

            Ajax.call([{
                methodname: 'get_categories',
                args: {},
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Copy course to selected categories.
         *
         * @method copyCourseToCategory
         */
        copyCourseToCategory: function () {
            var self = this,
                categoryid = modal.getModal().find(':selected').attr('data-categoryid'),
                modalBody = modal.getBody();
            self.addSpinner(modalBody);

            var renderPopup = function (responce) {
                if (responce.result) {
                    modal.approveState();
                    Str.get_string('course_copied_to_section', 'local_sharewith').done(function (s) {
                        modalBody.text(s);
                    });
                } else {
                    modal.errorState();
                }
            };

            Ajax.call([{
                methodname: 'add_sharewith_task',
                args: {
                    sourcecourseid: Number(this.getCurrentCourse()),
                    categoryid: Number(categoryid),
                    type: 'coursecopy'
                },
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Choose a course for copying the activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        selectCourse: function (target) {
            var cmid = $(target).parents('.activity').find('[data-itemtype="activityname"]').data('itemid'),
                sectionid = $(target).parents('.section').find('[data-itemtype="sectionname"]').data('itemid'),
                modalBody = modal.getBody();

            var renderPopup = function (responce) {
                var courses = JSON.parse(responce.courses);

                modal.init();

                if ($(target).data('ref') === 'copySection') {
                    this.setHandler('copySectionToCourse');
                    Str.get_string('selectcourse', 'local_sharewith')
                        .done(function (s) {
                            modal.getTitle().text(s);
                        });
                } else {
                    this.setHandler('copySectionToCourse');
                    Str.get_string('selectcourse_and_section', 'local_sharewith')
                        .done(function (s) {
                            modal.getTitle().text(s);
                        });
                }
                modalBody
                    .append($('<p>select course</p><select data-handler="selectSection" class = "courses form-control"></select>'))
                    .attr('data-cmid', cmid)
                    .attr('data-sectionid', sectionid)
                    .css('min-height', '100px');
                courses.forEach(function (course) {
                    modalBody.find('.courses')
                        .append($('<option data-courseid =' + course.id + '>' + course.fullname + '</option>'));
                });
                if ($(target).data('ref') !== 'copySection') {
                    modalBody.append($('<p>select section</p><select class = "sections form-control"></select>'));
                    this.setHandler('copyActivityToCourse');
                    this.selectSection($('[data-handler="selectSection"]'));
                }

            }.bind(this);

            Ajax.call([{
                methodname: 'get_courses',
                args: {},
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Choose a section for copying the activity.
         *
         * @method selectSection
         */
        selectSection: function () {
            var modalBody = modal.getBody(),
                courseid = modalBody.find(':selected').attr('data-courseid');

            var renderPopup = function (responce) {
                var sections = JSON.parse(responce.sections);
                modalBody.find('.sections').html('');
                sections.forEach(function (section) {
                    modalBody.find('.sections')
                        .append($('<option data-sectionid =' + section.section_id + '>' + section.section_name + '</option>'));
                });
            };

            Ajax.call([{
                methodname: 'get_sections',
                args: {courseid: Number(courseid)},
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Copy activity to selected course.
         *
         * @method copyActivityToCourse
         */
        copyActivityToCourse: function () {
            var modalBody = modal.getBody(),
                cmid = modalBody.data('cmid'),
                courseid = modalBody.find(':selected')[0].dataset.courseid,
                courseName = modalBody.find(':selected')[0].innerHTML,
                sectionid = modalBody.find(':selected')[1].dataset.sectionid;

            var renderPopup = function (responce) {
                if (responce.result) {
                    modal.approveState();
                    Str.get_string('activity_copied_to_course', 'local_sharewith').done(function (s) {
                        modalBody.text(s + ' ' + courseName);
                    });
                } else {
                    modal.errorState();
                }
            };

            Ajax.call([{
                methodname: 'add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(this.getCurrentCourse()),
                    sectionid: Number(sectionid),
                    sourceactivityid: Number(cmid),
                    type: 'activityhimselfcopy'
                },
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Copy section to selected course.
         *
         * @method copySectionToCourse
         */
        copySectionToCourse: function () {
            var modalBody = modal.getBody(),
                sectionid = modalBody.data('sectionid'),
                courseid = modalBody.find(':selected').data('courseid'),
                courseName = modalBody.find(':selected').text();

            var renderPopup = function (responce) {
                if (responce.result) {
                    modal.approveState();
                    Str.get_string('section_copied_to_course', 'local_sharewith').done(function (s) {
                        modalBody.text(s + ' ' + courseName);
                    });
                } else {
                    modal.errorState();
                }
            };

            Ajax.call([{
                methodname: 'add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(this.getCurrentCourse()),
                    sourcesectionid: Number(sectionid),
                    type: 'sectioncopy'
                },
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Create spinner image.
         *
         * @method addSpinner
         * @param {Node} $node target element.
         * @returns {*|jQuery}.
         */
        addSpinner: function ($node) {
            var spinner = $('<img/>').attr('src', M.util.image_url(this.ICON.spinner, this.ICON.component))
                .addClass('mx-auto spinner');
            $node.html('');
            $node.append(spinner);
            spinner.fadeIn().css('display', 'block');
            return spinner;
        },

        /**
         * Get current course on which the system is located.
         *
         * @method getCurrentCourse
         * @param {string} handler name of the handler.
         * @return {int} id number of the course.
         */
        getCurrentCourse: function () {
            var str = $('body').attr('class'),
                result = str.match(/course-\d+/gi)[0].replace(/\D+/, '');
            return result;
        },

        /**
         * Set handler to the data attribute of the specific node element.
         *
         * @method setHandler
         * @param {string} handler name of the handler.
         */
        setHandler: function (handler) {
            modal.getSubmit().attr('data-handler', handler);
        }

    };
});
