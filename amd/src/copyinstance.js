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
 * @module     local_sharewith/copyactivity
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/ajax',
    'core/notification',
    'local_sharewith/modal',

], function($, Str, Ajax, Notification, modal) {

    var STORAGE = {};

    return /** @alias module:local_sharewith/copyInstance */ {

        init: function() {
            var root = document.querySelector('body');

            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {

                    if (target.classList.contains('selectCategory')) {
                        this.selectCategory();
                        return;
                    }

                    switch (target.dataset.handler) {
                        case 'selectCourseForSection':
                            this.selectCourseForSection(target);
                            break;
                        case 'copySectionToCourse':
                            this.copySectionToCourse();
                            break;
                        case 'openShareWith':
                            this.openShareWith(target);
                            break;
                        case 'selectCourse':
                            this.selectCourse(target);
                            break;
                        case 'copyActivityToCourse':
                            this.copyActivityToCourse();
                            break;
                        case 'copyCourseToCategory':
                            this.copyCourseToCategory();
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

            this.typeMessage();
        },

        selectCourseForSection: function(target) {
            var sectionid = $(target).parents('.section').find('[data-itemtype="sectionname"]').data('itemid');

            STORAGE.sectionid = sectionid;
            var renderPopup = function(response) {
                var context = {
                    copySection: true,
                    courses: JSON.parse(response.courses)
                };
                modal.render(modal.template.copyinstance, context)
                    .done(modal.triggerBtn.click());
            };

            Ajax.call([{
                methodname: 'local_sharewith_get_courses',
                args: {},
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Copy section to selected course.
         *
         * @method copySectionToCourse
         */
        copySectionToCourse: function() {
            var modalContent = $(modal.modalContent),
                courseid = modalContent.find(':selected').data('courseid');

            modal.addBtnSpinner();
            var renderPopup = function(response) {
                var template = modal.template.error;
                var context = {
                    title: M.util.get_string('eventsectioncopy', 'local_sharewith'),
                    text: M.util.get_string('system_error_contact_administrator', 'local_sharewith'),
                };
                if (response.result) {
                    template = modal.template.confirm;
                    context = {
                        title: M.util.get_string('eventsectioncopy', 'local_sharewith'),
                        text: M.util.get_string('section_copied_to_course', 'local_sharewith'),
                    };
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'local_sharewith_add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(this.getCurrentCourse()),
                    sourcesectionid: Number(STORAGE.sectionid),
                    type: 'sectioncopy'
                },
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Choose a course for copying the activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        selectCourse: function(target) {
            var self = this;

            var renderPopup = function(response) {
                var context = {
                    courses: JSON.parse(response.courses),
                    copyActivity: true
                };

                STORAGE.cmid = target.dataset.cmid;
                STORAGE.uid = target.dataset.uid;
                context.hidebackbtn = true;
                modal.render(modal.template.copyinstance, context)
                    .done(modal.triggerBtn.click())
                    .done(() => {
                        $('.courses').on('change', () => {
                            self.selectSection();
                        });
                    })
                    .done(self.selectSection);

            };

            Ajax.call([{
                methodname: 'local_sharewith_get_courses',
                args: {},
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Choose a section for copying the activity.
         *
         * @method selectSection
         */
        selectSection: function() {
            var modalContent = $(modal.modalContent),
                courseid = modalContent.find(':selected').attr('data-courseid');

            if (!courseid) {
                courseid = this.getCurrentCourse();
            }

            var renderPopup = function(response) {
                var sections = JSON.parse(response.sections);
                modalContent.find('.sections').html('');
                sections.forEach(function(section) {
                    modalContent.find('.sections')
                        .append($('<option data-sectionid =' + section.section_id + '>' + section.section_name + '</option>'));
                });
            };

            Ajax.call([{
                methodname: 'local_sharewith_get_sections',
                args: {
                    courseid: Number(courseid)
                },
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Copy activity to selected course.
         *
         * @method copyActivityToCourse
         */

        copyActivityToCourse: function() {
            var modalContent = $(modal.modalContent),
                courseid = modalContent.find('.courses option:selected').attr('data-courseid'),
                sectionid = modalContent.find('.sections option:selected').attr('data-sectionid');
            modal.addBtnSpinner();
            var renderPopup = function(response) {
                var context = {
                    title: M.util.get_string('eventdublicatetoteacher', 'local_sharewith'),
                };
                var template = modal.template.error;

                switch (response.result) {
                    case 0:
                        context.text = M.util.get_string('system_error_contact_administrator', 'local_sharewith');
                        break;
                    case 1:
                        context.text = M.util.get_string('error_coursecopy', 'local_sharewith');
                        break;
                    case 2:
                        context.text = M.util.get_string('error_sectioncopy', 'local_sharewith');
                        break;
                    case 3:
                        context.text = M.util.get_string('error_activitycopy', 'local_sharewith');
                        break;
                    case 4:
                        context.text = M.util.get_string('error_permission_allow_copy', 'local_sharewith');
                        break;
                    case 10:
                        context.text = M.util.get_string('activity_copied_to_course', 'local_sharewith');
                        template = modal.template.confirm;
                        break;
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'local_sharewith_add_sharewith_task',
                args: {
                    courseid: Number(courseid),
                    sourcecourseid: Number(this.getCurrentCourse()),
                    sectionid: Number(sectionid),
                    sourceactivityid: Number(STORAGE.cmid),
                    sourceuserid: Number(STORAGE.uid),
                    type: 'activitycopy'
                },
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Choose a category for copying the course.
         *
         * @method selectCategory
         */
        selectCategory: function() {

            var renderPopup = function(response) {
                var context = {
                    hidebackbtn: true,
                    copyCourse: true,
                    categories: JSON.parse(response.categories)
                };
                var template = modal.template.copyinstance;
                if (!response.result) {
                    context = {
                        hidebackbtn: true,
                        copyCourse: true,
                        title: M.util.get_string('eventcoursecopy', 'local_sharewith'),
                        text: M.util.get_string('no_accessible_category', 'local_sharewith')
                    };
                    template = modal.template.error;
                }

                modal.render(template, context)
                    .done(modal.triggerBtn.click());
            };

            Ajax.call([{
                methodname: 'local_sharewith_get_categories',
                args: {},
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        /**
         * Copy course to selected categories.
         *
         * @method copyCourseToCategory
         */
        copyCourseToCategory: function() {
            var categoryid = $(modal.modalContent).find(':selected').attr('data-categoryid');

            var renderPopup = function(response) {
                var context = {
                    title: M.util.get_string('eventcoursecopy', 'local_sharewith'),
                    text: M.util.get_string('system_error_contact_administrator', 'local_sharewith')
                },
                    template = modal.template.error;
                if (response.result) {
                    context = {
                        title: M.util.get_string('eventcoursecopy', 'local_sharewith'),
                        text: M.util.get_string('course_copied_to_section', 'local_sharewith'),
                    };
                    template = modal.template.confirm;
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'local_sharewith_add_sharewith_task',
                args: {
                    sourcecourseid: Number(this.getCurrentCourse()),
                    categoryid: Number(categoryid),
                    type: 'coursecopy'
                },
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        typeMessage: function() {
            var urlString = window.location.href;
            var url = new URL(urlString);
            var param = url.searchParams.get('swactivityname');
            if (param) {
                var textarea = $('textarea[data-region="send-message-txt"]')[0];
                var speed = 30;
                /* The speed/duration of the effect in milliseconds */
                var data = {activityname: param};
                Str.get_string('ask_question_before_copying', 'local_sharewith', data).done(function(message) {
                    var i = 0;
                    (function typeWriter() {
                        if (i < message.length) {
                            textarea.innerHTML += message.charAt(i);
                            i++;
                            setTimeout(typeWriter, speed);
                        } else {
                            var val = textarea.value;
                            $(textarea)
                              .focus()
                              .val("")
                              .val(val);
                        }
                    })();
                }).fail(Notification.exception);
            }
        },

        /**
         * Get current course on which the system is located.
         *
         * @method getCurrentCourse
         * @param {string} handler name of the handler.
         * @return {int} id number of the course.
         */
        getCurrentCourse: function() {
            var str = $('body').attr('class'),
                result = str.match(/course-\d+/gi)[0].replace(/\D+/, '');
            return result;
        },
    };
});
