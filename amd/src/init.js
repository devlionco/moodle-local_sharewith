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
    'local_sharewith/modal',
    'local_sharewith/sharewithteacher',
    'local_sharewith/copyinstance'
], function ($, modal, shareWithTeacher, copyInstance) {

    var root = document.querySelector('body');

    return {
        init: function (actions) {
            modal.insertTemplates(actions).done(function () {
                modal.addActionNode();
                shareWithTeacher.init();
                copyInstance.init();

                root.addEventListener('click', function (e) {
                    var target = e.target;
                    while (root.contains(target)) {
                        switch (target.dataset.handler) {
                            case 'goBack':
                                modal.goBack();
                                break;
                        }
                        target = target.parentNode;
                    }
                });
            });
        }
    };
});
