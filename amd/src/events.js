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
    'local_sharewith/modal',
    'core/notification',
], function ($, Str, Ajax, modal, notification) {


    Str.get_strings([
        {key: 'sent', component: 'local_sharewith'},
        {key: 'fails', component: 'local_sharewith'},
        {key: 'sharing_sent_successfully', component: 'local_sharewith'},
    ]).done(function () {});

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
         * Choose a teacher for copying the activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        selectTeacher: function (target) {

            var cmid = $(target).parents('.activity').find('[data-itemtype="activityname"]').data('itemid'),
                    courseid = $('#course').val(),
                    modalBody = modal.getBody();

            modal.cancelState();

            var renderPopup = function (res) {

                var response = JSON.parse(res);

                modal.init();

                this.setHandler('copySectionToCourse');
                Str.get_string('selectteacher', 'local_sharewith')
                        .done(function (s) {
                            modal.getTitle().text(s);
                        });
                modalBody.append(response.html);

                this.selectDestination(cmid);

            }.bind(this);

            Ajax.call([{
                methodname: 'local_sharewith_get_teachers',
                args: {
                    'activityid': cmid,
                    'courseid': courseid
                },
                done: renderPopup,
                fail: renderPopup
            }]);
        },

        /**
         * Choose a course for saving the new activity.
         *
         * @method selectCourse
         * @param {Node} target element.
         */
        saveActivity: function (target) {

            var actid = target.dataset.sharing;
            var modalBody = modal.getBody();


            var renderPopup = function (responce) {
                var courses = JSON.parse(responce.courses);

                modal.init();
                modalBody.append($('<input type="hidden" name="shareid" value="' + actid + '">'));

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
                        //.attr('data-cmid', cmid)
                        //.attr('data-sectionid', sectionid)
                        .css('min-height', '100px');
                courses.forEach(function (course) {
                    modalBody.find('.courses')
                            .append($('<option data-courseid =' + course.id + '>' + course.fullname + '</option>'));
                });
                if ($(target).data('ref') !== 'copySection') {
                    modalBody.append($('<p>select section</p><select class = "sections form-control"></select>'));
                    this.setHandler('saveActivityToCourse');
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
         * Copy activity to selected course.
         *
         * @method copyActivityToCourse
         */
        saveActivityToCourse: function () {
            var modalBody = modal.getBody(),
                    shareid = $('input[name="shareid"]').val(),
                    courseid = modalBody.find(':selected')[0].dataset.courseid,
                    courseName = modalBody.find(':selected')[0].innerHTML,
                    sectionid = modalBody.find(':selected')[1].dataset.sectionid;

            var renderPopup = function (response) {
                if (response.result > 0) {
                    Str.get_string('activity_copied_to_course', 'local_sharewith').done(function (s) {
                        modalBody.text(s + ' ' + courseName);
                    });
                    modal.approveState();
                } else {
                    modal.errorTextState();
                    modalBody.text(response.text);
                }
            };

            Ajax.call([{
                methodname: 'add_saveactivity_task',
                args: {
                    courseid: Number(courseid),
                    sectionid: Number(sectionid),
                    shareid: Number(shareid),
                    type: 'activityhimselfcopy'
                },
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
        },

        selectDestination: (activity_id) => {
            const NUM_OF_SIGNS = 3;
            const main = document.querySelector(`.prof__form`);
            const input = document.querySelector(`input.prof__input`);
            // const inputWrapper = document.querySelector(`label.dest__insert-wrapper`);
            const tagWrapper = document.querySelector(`.prof__tag-wrap`);
            const resultBlock = document.createElement(`ul`);
            const btnSendResult = document.querySelector(`#btnSendResult`);
            const sesskey = M.cfg.sesskey;

            if (!main)
                return;

            resultBlock.classList.add(`dest__result-block`);
            main.appendChild(resultBlock);

            const keySelect = (container, close) => {

                const forms = Array.from(document.querySelectorAll(`form`));
                forms.forEach((form) => {
                    if (form.contains(container)) {
                        form.onkeydown = (event) => {
                            if (event.keyCode == 13) {
                                event.preventDefault();
                            }
                        }
                    }
                });

                let currentItem = 0;
                const items = Array.from(container.children);
                items.forEach((item) => {
                    item.tabIndex = 0;

                });

                container.onmouseover = (e) => {
                    e.target.focus();
                    items.forEach((item, index) => {
                        item.onfocus = () => {
                            currentItem = index;
                        }
                    });
                }

                const setBlur = () => {
                    items[currentItem].blur();
                }
                const setFocus = () => {
                    items[currentItem].focus();
                }

                const goUp = () => {
                    if (currentItem <= 0) {
                        return;
                    }
                    if (items[currentItem - 1].classList.contains(`dest__selected`)) {
                        currentItem--;
                        goUp();
                    } else {
                        setBlur();
                        currentItem--;
                        setFocus();
                    }
                }
                const goDown = () => {
                    if (currentItem >= items.length - 1) {
                        return;
                    }
                    if (items[currentItem + 1].classList.contains(`dest__selected`)) {
                        currentItem++;
                        goDown();
                    } else {
                        setBlur();
                        currentItem++;
                        setFocus();
                    }
                }
                const selectItem = () => {
                    let event = new Event('click', {bubbles: true});
                    items[currentItem].dispatchEvent(event);

                }
                const hideAll = () => {
                    container.innerHTML = '';
                    container.style.display = 'none';
                    currentItem = -1;
                    document.removeEventListener('keydown', keyCodeHandler);
                }

                const keyCodeHandler = (event) => {
                    switch (event.keyCode) {
                        case 38: // arrow up
                            goUp();
                            break;
                        case 40:  // arrow down
                            goDown();
                            break;
                        case 13: // enter
                            selectItem();
                            break;
                        case 27: // esc
                            hideAll();
                            break;
                    }
                }

                const closeBlockResult = (event) => {
                    if (!resultBlock.contains(event.target) && !tagWrapper.contains(event.target)) {
                        resultBlock.style.display = `none`;
                        resultBlock.innerHTML = ``;
                        document.onclick = false;
                    }
                }

                if (close) {
                    document.onkeydown = false;
                    document.onclick = false;
                } else {
                    document.onclick = closeBlockResult;
                    document.onkeydown = keyCodeHandler;
                }

            }

            const closeSearchResult = () => {
                resultBlock.style.display = `none`;
                resultBlock.innerHTML = ``;
                keySelect(resultBlock, true)
            }

            const showSearchResult = (item) => {
                resultBlock.innerHTML = ``;
                keySelect(resultBlock, true) // stop old event listeners before reloading
                const units = JSON.parse(item);
                for (id in units) {
                    const unit = document.createElement(`li`);
                    unit.dataset.id = units[id].teacher_id;
                    unit.classList.add(`dest__unit`);
                    unit.innerHTML = `<div class = "dest__img" ><img src = "${M.cfg.wwwroot + units[id].teacher_url}" alt = ""></div><span class = "dest__name">${units[id].teacher_name}</span>`;
                    if (tagWrapper.querySelector(`div.dest__dummy[data-id='${units[id].teacher_id}']`)) {
                        unit.classList.add(`dest__selected`);
                    }
                    resultBlock.style.display = `block`;
                    resultBlock.appendChild(unit);
                }
                resultBlock.onclick = function (e) {
                    let targetEl = e.target;
                    while (targetEl.tagName != `UL`) {
                        if (targetEl.tagName == `LI`) {
                            if (targetEl.classList.contains(`dest__selected`)) {
                                return;
                            }
                            const destinationName = setCurrentDestination(units[targetEl.dataset.id]);
                            targetEl.classList.add(`dest__selected`);
                            tagWrapper.appendChild(destinationName);
                            btnSendResult.disabled = false;
                            input.value = ``;

                        }
                        targetEl = targetEl.parentNode;
                    }
                }

                keySelect(resultBlock);

            }

            const setCurrentDestination = (teacher) => { //add choosed name to the input block
                let dummy = document.createElement(`div`);
                dummy.classList.add(`dest__dummy`);
                dummy.dataset.id = teacher.teacher_id;
                dummy.innerHTML = `<span class = "dest__dummy-del"></span><span>${teacher.teacher_name}</span>`;

                dummy.querySelector(`.dest__dummy-del`).onclick = function (e) {
                    e.target.parentNode.remove();
                    if (document.querySelector(`li.dest__unit[data-id='${teacher.teacher_id}']`)) {
                        document.querySelector(`li.dest__unit[data-id='${teacher.teacher_id}']`).classList.remove(`dest__selected`);
                    }
                    // document.querySelector(`input[data-id='${teacher.teacher_id}']`).remove();
                    if (!document.querySelector(`.dest__dummy`)) {
                        closeSearchResult();
                        btnSendResult.disabled = true;
                    }
                    e.stopPropagation();

                };

                return dummy;
            }

            // todo add pouse to ajax request
            input.addEventListener('input', function (e) {

                const inputValue = this.value;
                if (!document.querySelector(`div.dest__dummy`) && !inputValue) {
                    closeSearchResult();
                }

                if (inputValue.length >= NUM_OF_SIGNS) {

                    let courseId = $('#course').val();
                    Ajax.call([{
                            methodname: 'local_sharewith_autocomplete_teachers',
                            args: {
                                activityid: activity_id,
                                courseid: courseId,
                                searchstring: inputValue
                            },
                            done: function (res) {
                                showSearchResult(res);
                            },
                            fail: notification.exception
                        }]);

                }
            });

            btnSendResult.addEventListener(`click`, function () {
                const loadingIcon = document.querySelector(`.loading_dots`);
                const chosenBlocks = Array.from(document.querySelectorAll(`.dest__dummy`));
                const teachersId = [];
                const commentToTeachers = document.querySelector(`textarea.prof__comment`).value.trim();

                btnSendResult.disabled = true;
                loadingIcon.classList.remove(`d-none`);
                chosenBlocks.forEach(function (item) {
                    teachersId.push(item.dataset.id);
                });

                let activity_id = $("#send_popup_activity_id").val();
                let course_id = $("#send_popup_course_id").val();
                let message = $("#send_popup_message").val();

                Ajax.call([{
                    methodname: 'local_sharewith_submit_teachers',
                    args: {
                        activityid: activity_id,
                        courseid: course_id,
                        teachersid: JSON.stringify(teachersId),
                        message: message
                    },
                    done: function (res) {
                        loadingIcon.classList.add(`d-none`);
                        btnSendResult.classList.add(`prof__btn--success`);
                        btnSendResult.innerHTML = M.util.get_string('sent', 'local_sharewith');
                        chosenBlocks.forEach(function (item) {
                            item.remove();
                        });
                        modal.approveState();
                    },
                    fail: function () {
                        btnSendResult.classList.add(`prof__btn--error`);
                        btnSendResult.innerHTML = M.util.get_string('fails', 'local_sharewith');
                    }
                }]);

                resultBlock.style.display = `none`;

            });
        }

    };
});
