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
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/ajax',
    'core/notification',
    'local_sharewith/modal',
], function($, Ajax, Notification, modal) {

    var SELECTORS = {
        root: 'body',
    };

    var STORAGE = {};

    /** @alias module:local_sharewith/sharewithteacher */
    return {

        resultBlock: '',
        tagWrapper: '',
        input: '',
        numOfSings: 3,

        init: function() {

            var root = $(SELECTORS.root)[0];
            root.addEventListener('click', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'addTag':
                            this.addTag(target);
                            break;
                        case 'removeTag':
                            this.removeTag(target);
                            break;
                        case 'sendLinkToTeachers':
                            this.submitTeachers();
                            break;
                        case 'shareActivity':
                            this.shareActivity(target);
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

            root.addEventListener('input', function(e) {
                var target = e.target;
                while (root.contains(target)) {
                    switch (target.dataset.handler) {
                        case 'selectTeacher':
                            this.autocompleteTeachers(target);
                            break;
                    }
                    target = target.parentNode;
                }
            }.bind(this));

        },

        /**
         * Choose a teacher for copying the activity.
         *
         * @method shareActivity
         * @param {Node} target element.
         */
        shareActivity: function(target) {
            STORAGE.cmid = $(target).attr('data-cmid');
            STORAGE.courseid = this.getCurrentCourse();
            var self = this;
            var renderPopup = function(response) {
                var context = JSON.parse(response);
                modal.render(modal.template.shareteacher, context)
                    .done(function() {
                        self.resultBlock = document.querySelector('.result-block');
                        self.tagWrapper = document.querySelector('.tag-wrapper');
                        self.input = document.querySelector('input[data-handler = "selectTeacher"]');
                        modal.triggerBtn.click();
                    });
            };

            Ajax.call([{
                methodname: 'local_sharewith_get_teachers',
                args: {
                    activityid: Number(STORAGE.cmid),
                    courseid: Number(STORAGE.courseid)
                },
                done: renderPopup,
                fail: Notification.exception
            }]);
        },

        keySelect: function(container) {

            var currentItem = 0;
            var tagWrapper = document.querySelector('.tag-wrapper');
            var items = Array.from(container.children);
            items.forEach(function(item) {
                item.tabIndex = 0;
            });

            container.onmouseover = function(e) {
                e.target.focus();
                items.forEach(function(item, index) {
                    item.onfocus = function() {
                        currentItem = index;
                    };
                });
            };

            var setBlur = function() {
                items[currentItem].blur();
            };
            var setFocus = function() {
                items[currentItem].focus();
            };

            var goUp = function() {
                if (currentItem <= 0) {
                    return;
                } else {
                    setBlur();
                    currentItem--;
                    setFocus();
                }
            };
            var goDown = function() {
                if (currentItem >= items.length - 1) {
                    return;
                } else {
                    setBlur();
                    currentItem++;
                    setFocus();
                }
            };
            var selectItem = function() {
                var event = new Event('click', {bubbles: true});
                items[currentItem].dispatchEvent(event);

            };
            var hideAll = function() {
                container.innerHTML = '';
                container.classList.add('d-none');
                currentItem = -1;
                document.removeEventListener('click', closeBlockResult);
                document.removeEventListener('keydown', keyCodeHandler);
            };

            var keyCodeHandler = function(e) {
                switch (e.keyCode) {
                    case 38: // Arrow up.
                        goUp();
                        break;
                    case 40: // Arrow down.
                        goDown();
                        break;
                    case 13: // Enter.
                        selectItem();
                        break;
                    case 27: // Esc.
                        hideAll();
                        break;
                }
            };

            var closeBlockResult = function(e) {
                if (container.contains(e.target) || e.path.indexOf(tagWrapper) != -1) {
                    return;
                }
                hideAll();
            };

            document.addEventListener('click', closeBlockResult);
            document.addEventListener('keydown', keyCodeHandler);
        },

        showSearchResult: function(response) {
            this.resultBlock.innerHTML = '';
            var teachers = JSON.parse(response);

            teachers.forEach(function(teacher) {
                var unit = document.createElement('li');
                unit.dataset.teacherid = teacher.id;
                unit.dataset.teachername = teacher.teacher_name;
                unit.dataset.handler = 'addTag';
                unit.classList.add('btn', 'btn-secondary', 'd-flex', 'mb-1');
                unit.innerHTML = '<div class = "sw-img" >' +
                    '<img src = "' + M.cfg.wwwroot + teacher.teacher_url + '" alt = "">' +
                    '</div><span class = "pl-2">' + teacher.teacher_name + '</span>';
                if (this.tagWrapper.querySelector('.btn[data-id="' + teacher.id + '"]')) {
                    unit.classList.add('active');
                }
                this.resultBlock.classList.remove('d-none');
                this.resultBlock.appendChild(unit);
            }.bind(this));

            this.keySelect(this.resultBlock);
        },

        autocompleteTeachers: function(target) {
            var inputValue = target.value;

            if (!this.resultBlock.childElementCount && !inputValue) {
                this.resultBlock.classList.add('d-none');
            }

            if (inputValue.length >= this.numOfSings) {
                Ajax.call([{
                    methodname: 'local_sharewith_autocomplete_teachers',
                    args: {
                        searchstring: inputValue
                    },
                    done: this.showSearchResult.bind(this),
                    fail: Notification.exception
                }]);
            }
        },

        submitTeachers: function() {
            var myForm = modal.modalWrapper.querySelector('form');
            var formData = new FormData(myForm);

            if (!this.isValidData(formData)) {
                return;
            }
            modal.addBtnSpinner();

            var renderPopup = function(response) {
                var template = modal.template.error;
                var context = {
                    title: M.util.get_string('eventcopytoteacher', 'local_sharewith'),
                    text: M.util.get_string('system_error_contact_administrator', 'local_sharewith'),
                };
                if (!response.status) {
                    context.text = response.message;
                } else {
                    template = modal.template.confirm;
                    context.text = M.util.get_string('succesfullyshared', 'local_sharewith');
                }
                modal.render(template, context);
            };

            Ajax.call([{
                methodname: 'local_sharewith_submit_teachers',
                args: {
                    activityid: STORAGE.cmid,
                    courseid: STORAGE.courseid,
                    teachersid: JSON.stringify(formData.getAll('teacherid')),
                    message: formData.get('message')
                },
                done: renderPopup,
                fail: Notification.exception
            }]);

        },

        addTag: function(target) {
            var teacherid = target.dataset.teacherid,
                tag = $(this.tagWrapper).find('[data-teacherid=' + teacherid + ']');

            if (tag.length) {
                this.removeTag(tag[0]);
                return;
            }

            var teacherTag = $(this.tagWrapper).find('.example').clone();
            $(teacherTag).attr('data-teacherid', teacherid);
            $(teacherTag)
              .find('input')
              .attr('value', teacherid)
              .attr('checked', 'checked');
            teacherTag.append('<span>' + target.dataset.teachername + '</span>');
            teacherTag.removeClass('example d-none');

            target.classList.add('active');
            $(this.tagWrapper).append(teacherTag);
            this.input.value = '';
        },

        removeTag: function(target) {
            var teacherid = target.dataset.teacherid;
            $(this.resultBlock).find('[data-teacherid=' + teacherid + ']')
                .removeClass('active');
            $(target).remove();
        },

        isValidData: function(formData) {
            var errors = [];
            if (!formData.getAll('teacherid').length) {
                errors.push($('#selectteacher'));
            }

            errors.forEach(function(el) {
                $(el).popover({
                    template: '<div class="popover" role="tooltip">' +
                    '<div class="arrow"></div>' +
                    '<div class="popover-body text-danger" style="font-size:1rem;"></div>' +
                    '</div>'
                });
                $(el).popover('show');
                $(el).on('click remove', function() {
                    $(this).popover('hide');
                });
            });

            return errors.length ? false : true;
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
