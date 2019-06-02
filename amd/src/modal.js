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
    'core/str',
    'core/templates',
    'core/notification',
    'theme_boost/modal'
], function ($, Str, templates, notification, Modal) {

    return /** @alias module:local_sharewith/modal */ {

        MODAL: {
            selectors: {
                modal: '#selectItem',
                content: '.modal-content',
                body: '.modal-body',
                title: '.modal-title',
                submit: '[data-ref="submit"]',
                cancel: '[data-ref="cancel"]',
                approve: '[data-ref="approve"]',
                close: '[data-ref="close"]',
                error: '[data-ref="error"]'
            },
            config: {}
        },

        /**
         * Insert modal markaps on the page.
         *
         * @method insertTemplates
         */
        insertTemplates: function () {
            var context = {};

            templates.render('local_sharewith/modal', context)
                .then(function (html, js) {
                    return templates.appendNodeContents('body', html, js);
                })
                .fail(notification.exception);
        },

        /**
         * Initialization and show modal window.
         *
         * @method init
         */
        init: function () {
            var element = document.querySelector(this.MODAL.selectors.modal),
                modal = new Modal.default(element, this.MODAL.config);
            this.initState();
            modal.show();
        },

        /**
         * Hide and unset prop of the modal window.
         *
         * @method hide
         */
        hide: function () {
            this.initState();
            this.getClose().trigger('click');
        },

        /**
         * Drop current state for modal window.
         *
         * @method initState
         */
        initState: function () {
            this.getBody().html('');
            this.getCancel().show();
            this.getSubmit().show();
            this.getApprove().hide();
            this.getError().hide();
            this.getContent().removeClass('alert-danger');
        },

        /**
         * Set approve state to the modal window.
         *
         * @method approveState
         */
        approveState: function () {
            this.getCancel().fadeOut();
            this.getSubmit().fadeOut();
            this.getApprove().delay('slow').fadeIn();
            setTimeout(this.hide.bind(this), 5000);
        },

        /**
         * Set cancel state to the modal window.
         *
         * @method approveState
         */
        cancelState: function () {
            this.getSubmit().fadeOut();
        },

        /**
         * Set error state to the modal window.
         *
         * @method errorState
         */
        errorState: function () {
            this.getCancel().fadeOut();
            this.getSubmit().fadeOut();
            this.getError().delay('slow').fadeIn();
            this.getContent().addClass('alert-danger');
            Str.get_strings([{
                key: 'system_error_contact_administrator', component: 'local_sharewith'
            }]).done(function (s) {
                this.getBody().text(s);
            }.bind(this));
        },

        /**
         * Set error text state to the modal window.
         *
         * @method errorState
         */
        errorTextState: function () {
            this.getCancel().fadeOut();
            this.getSubmit().fadeOut();
            this.getError().delay('slow').fadeIn();
            this.getContent().addClass('alert-danger');
        },

        /**
         * Gets the wrapper of the modal window.
         *
         * @method getModal.
         * @return {jQuery}.
         */
        getModal: function () {
            return $(this.MODAL.selectors.modal);
        },

        /**
         * Gets the body of a modal window.
         *
         * @method getBody.
         * @return {jQuery}.
         */
        getBody: function () {
            return $(this.getModal()).find(this.MODAL.selectors.body);
        },

        /**
         * Gets the Submit btn of a modal window.
         *
         * @method getSubmit.
         * @return {jQuery}.
         */
        getSubmit: function () {
            return $(this.getModal()).find(this.MODAL.selectors.submit);
        },

        /**
         * Gets the Cancel btn of a modal window.
         *
         * @method getCancel.
         * @return {jQuery}.
         */
        getCancel: function () {
            return $(this.getModal()).find(this.MODAL.selectors.cancel);
        },

        /**
         * Gets the Approve btn of a modal window.
         *
         * @method getCancel.
         * @return {jQuery}.
         */
        getApprove: function () {
            return $(this.getModal()).find(this.MODAL.selectors.approve);
        },

        /**
         * Gets the Close btn of a modal window.
         *
         * @method getClose.
         * @return {jQuery}.
         */
        getClose: function () {
            return $(this.getModal()).find(this.MODAL.selectors.close);
        },

        /**
         * Gets the Error btn of a modal window.
         *
         * @method getError.
         * @return {jQuery}.
         */
        getError: function () {
            return $(this.getModal()).find(this.MODAL.selectors.error);
        },

        /**
         * Gets the content block of the modal window.
         *
         * @method getContent.
         * @return {jQuery}.
         */
        getContent: function () {
            return $(this.getModal()).find(this.MODAL.selectors.content);
        },

        /**
         * Gets the title block of the modal window.
         *
         * @method getTitle.
         * @return {jQuery}.
         */
        getTitle: function () {
            return $(this.getModal()).find(this.MODAL.selectors.title);
        }

    };
});
