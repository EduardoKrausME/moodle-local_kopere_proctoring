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
 * @package   proctoringpolicy_copy
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {

    /**
     * Attach handlers to block copy, paste, cut and context menu.
     *
     * @param {Object} context Contains cmid, attemptid, etc.
     * @param {Object} cfg Policy config {limit, message}.
     */
    function initCopyPolicy(context, cfg) {
        var message = cfg && cfg.message ? cfg.message : "";
        var limit = cfg && cfg.limit ? parseInt(cfg.limit, 10) : 0;
        if (isNaN(limit)) {
            limit = 0;
        }

        var warningsCount = 0;

        function showWarning() {
            if (!message) {
                return;
            }

            if (limit > 0 && warningsCount >= limit) {
                return;
            }

            warningsCount++;

            // Simple message strategy. If you have a dedicated
            // container in your overlay, you could enhance this
            // to inject HTML there instead of using alert.
            window.alert(message);
        }

        function isBlockedKeyEvent(e) {
            var key = e.key ? e.key.toLowerCase() : "";
            var ctrlOrCmd = e.ctrlKey || e.metaKey;

            if (!ctrlOrCmd) {
                return false;
            }

            if (key === "c" || key === "v" || key === "x" || key === "a" || key === "p") {
                return true;
            }

            return false;
        }

        function handleKeydown(e) {
            if (isBlockedKeyEvent(e)) {
                e.preventDefault();
                e.stopPropagation();
                showWarning();
            }
        }

        function handleClipboardEvent(e) {
            e.preventDefault();
            e.stopPropagation();
            showWarning();
        }

        function handleContextmenu(e) {
            e.preventDefault();
            e.stopPropagation();
            showWarning();
        }

        // Attach global listeners with capture to intercept early.
        $(document).on("keydown.local_kopere_proctoring_copy", handleKeydown);
        $(document).on("copy.local_kopere_proctoring_copy", handleClipboardEvent);
        $(document).on("cut.local_kopere_proctoring_copy", handleClipboardEvent);
        $(document).on("paste.local_kopere_proctoring_copy", handleClipboardEvent);
        $(document).on("contextmenu.local_kopere_proctoring_copy", handleContextmenu);
    }

    return {
        /**
         * Public AMD entrypoint.
         *
         * @param {Object} context Shared context from main proctoring start module.
         * @param {Object} cfg Policy-specific config.
         */
        init: function (context, cfg) {
            initCopyPolicy(context || {}, cfg || {});
        }
    };
});
