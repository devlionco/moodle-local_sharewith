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
 * @module     local_sharewith/modal
 * @package    local_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/templates',
    'core/notification'
], function($, Templates, Notification) {

    var SELECTORS = {
        modalWrapper: '#modalSharewith',
        modalContent: '#modalContentSharewith',
        triggerBtn: '#triggerModalSharewith',
        editMenu: '.course-content li.activity [role="menu"]',
        actionMenu: '.course-content li.activity .actions .action-menu',
        actionBlock: '.course-content li.activity .actions > div',
        menuSection: '.course-content .section_action_menu [role="menu"]',
    };

    return /** @alias module:local_sharewith/modal */ {

        template: {
            modalwrapper: 'local_sharewith/modalwrapper',
            selector: 'local_sharewith/selector',
            copyinstance: 'local_sharewith/copyinstance',
            error: 'local_sharewith/error',
            confirm: 'local_sharewith/confirm',
            shareteacher: 'local_sharewith/shareteacher',
            uploadactivity: 'local_sharewith/uploadactivity'
        },

        actions: '',

        modalInit: false,

        triggerBtn: '',

        modalContent: '',

        modalWrapper: '',

        /**
         * Clone and add the button for copying activity.
         *
         * @method addShareButtonEditMenu
         * @param {boolean} activitysending send activity to teacher.
         */
        addShareButtonEditMenu: function(activitysending) {

            var string = M.util.get_string('use_activity', 'local_sharewith'),
                menu = $(document).find(SELECTORS.editMenu);

            menu.each(function() {
                var clone = $(this).children().last().clone();
                clone
                    .find('.menu-action-text')
                    .text(string);
                clone
                    .attr('href', '#')
                    .attr('data-handler', 'openShareWith')
                    .attr('data-activitysending', activitysending)
                    .removeAttr('data-action')
                    .addClass('sharingact_item');
                clone
                    .find('.icon')
                    .attr('title', string)
                    .attr('aria-label', string)
                    .removeAttr('class')
                    .addClass('icon fa fa-share-alt fa-fw');

                $(this).append(clone);
            });
        },

        /**
         * Add share button to the activity.
         *
         * @method addShareButtonActivity
         */
        addShareButtonActivity: function() {

            var menu = $(document).find(SELECTORS.actionMenu),
                string = M.util.get_string('share', 'local_sharewith');

            menu.each(function() {
                var shareBtn = $('<button><i class = "icon"></i>' + string + '</button>');
                    shareBtn
                        .attr('data-handler', 'openShareWith')
                        .addClass('btn btn-outline-primary btn-sm');
                    shareBtn
                        .find('.icon')
                        .attr('title', string)
                        .attr('aria-label', string)
                        .addClass('fa fa-share-alt fa-fw');
                shareBtn.insertAfter($(this));
            });
        },

        /**
         * Clone and add the button for copying section.
         *
         * @method addCopyActivityButton
         * @param {jquery} root The root element.
         */
        addCopySectionButton: function() {

            var string = M.util.get_string('eventsectioncopy', 'local_sharewith'),
                menu = $(document).find(SELECTORS.menuSection);

            menu.each(function() {
                var clone = $(this).children().last().clone();
                clone
                    .attr('href', '#')
                    .attr('data-handler', 'selectCourseForSection');
                clone
                    .find('.menu-action-text')
                    .text(string);
                clone
                    .find('.icon')
                    .removeAttr('class')
                    .attr('title', string)
                    .attr('aria-label', string)
                    .addClass('icon fa fa-copy fa-fw');

                $(this).append(clone);
            });

        },

        /**
         * Insert modal markup on the page.
         *
         * @method insertTemplates
         * @return {Promise|boolean}
         * @param {object} actions Available actions.
         */
        insertTemplates: function(actions) {
            this.actions = actions;
            var context = {},
                self = this;

            return Templates.render('local_sharewith/modalwrapper', context)
              .done(function(html, js) {
                    if (!self.modalInit) {
                        Templates.appendNodeContents('body', html, js);
                        self.modalInit = true;
                        self.modalWrapper = document.querySelector(SELECTORS.modalWrapper);
                        self.modalContent = document.querySelector(SELECTORS.modalContent);
                        self.triggerBtn = document.querySelector(SELECTORS.triggerBtn);
                    }
                })
                .fail(Notification.exception);
        },

        /**
         * Insert modal markup on the page.
         *
         * @method render
         * @param {string} template The template name.
         * @param {object} context The context for template.
         * @return {Promise}
         */
        render: function(template, context) {
            var self = this;
            return Templates.render(template, context)
                    .done(function(html, js) {
                        Templates.replaceNodeContents(self.modalContent, html, js);
                    })
                    .fail(Notification.exception);
        },

        /**
         * Add necessary nodes to the DOM.
         *
         * @method addActionNode
         */
        addActionNode: function() {


            if (this.actions.sectioncopy) {
                this.addCopySectionButton();
            }
            if (this.actions.activitycopy) {
                // Adding share btn to edit menu
                this.addShareButtonEditMenu(this.actions.activitysending);
                // Add share button to activity block
                // this.addShareButtonActivity();
            }

        },

        /**
         * Show spinner.
         *
         * @method addSpinner
         */
        addBtnSpinner: function() {
            $('#modalspinner').removeClass('d-none');
            $('#modalspinner').addClass('loading');
            $('#modalspinner').parent().prop('disabled', true);
        },

        /**
         * Return to the main window.
         *
         * @method goBack
         */
        goBack: function() {
            var context = {
                activitysending: Number(this.actions.activitysending)
            };
            this.render(this.template.selector, context);
        },
    };
});
