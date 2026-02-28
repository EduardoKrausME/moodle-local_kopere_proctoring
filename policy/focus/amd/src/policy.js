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
 * policy.js
 *
 * @package   proctoringpolicy_focus
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {

    /**
     * Initialize focus policy on quiz attempt.
     *
     * @param {Object} ctx   Shared context created by the parent plugin
     * @param {Object} cfg   Configuration for this policy
     */
    function init(ctx, cfg) {
        if (!ctx || !cfg || !cfg.enabled) {
            return;
        }

        var limit = parseInt(cfg.limit || 0, 10);
        if (!limit || limit < 0) {
            limit = 3;
        }

        var count = 0;
        var namespace = ".kopere_proctoring_focus";

        /**
         * Register one focus-loss event.
         *
         * @param {String} kind
         * @param {String} detail
         */
        function registerEvent(kind, detail) {
            count++;

            if (typeof ctx.sendEvent === "function") {
                ctx.sendEvent("focus_" + kind, {
                    count: count,
                    detail: detail || ""
                });
            }

            if (count > limit && typeof ctx.lock === "function") {
                ctx.lock("focus", cfg.message || "");
            }
        }

        // Window blur.
        $(window).on("blur" + namespace, function () {
            registerEvent("blur", "window");
        });

        // Visibility change (tab change, minimize, etc.).
        $(document).on("visibilitychange" + namespace, function () {
            if (document.hidden) {
                registerEvent("visibilitychange", "hidden");
            }
        });
    }

    return {
        init: init
    };
});
