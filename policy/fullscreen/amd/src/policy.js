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
 * @package   proctoringpolicy_fullscreen
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {

    function isFullscreenActive() {
        return !!(
            document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement ||
            document.msFullscreenElement
        );
    }

    function requestFullscreen() {
        var element = document.documentElement;

        var fn = element.requestFullscreen ||
            element.webkitRequestFullscreen ||
            element.mozRequestFullScreen ||
            element.msRequestFullscreen;

        if (!fn) {
            return false;
        }

        try {
            fn.call(element);
            return true;
        } catch (e) {
            return false;
        }
    }

    function showMessage(cfg) {
        $("#proctoringpolicy_fullscreen-message").show();
    }

    function hideMessage(cfg) {
        $("#proctoringpolicy_fullscreen-message").hide();
    }

    function warnStatus(cfg, text) {
        $("#status-danger").show().html(text);
    }

    function emitLog(ctx, action, value) {
        // No fixed coupling to parent: emit a jQuery event.
        // The core may listen and persist logs.
        $(document).trigger("local_kopere_proctoring:policy_log", [{
            cmid: ctx.cmid,
            attemptid: ctx.attemptid,
            policy: "fullscreen",
            action: action,
            value: value || ""
        }]);
    }

    function bindFullscreenExit(ctx, cfg, state) {
        $(document).on("fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange", function () {
            if (!state.inexam) {
                return;
            }
            if (!isFullscreenActive()) {
                state.exits = state.exits + 1;
                emitLog(ctx, "fullscreen_exit", String(state.exits));

                showMessage(cfg);

                if (state.exits > state.limit) {
                    warnStatus(cfg, "Fullscreen limit exceeded.");
                    emitLog(ctx, "fullscreen_blocked", "limit");
                }
            }
        });
    }

    function gateStart(ctx, cfg, state) {
        var startsel = "#start-exam,#return-exam-1";

        $(document).on("click", startsel, function (e) {
            // If already fullscreen, just allow the core flow (do not block).
            if (isFullscreenActive()) {
                hideMessage(cfg);
                state.inexam = true;
                return;
            }

            // Try request fullscreen (must be in user gesture).
            var ok = requestFullscreen();
            if (!ok) {
                e.preventDefault();
                e.stopPropagation();
                showMessage(cfg);
                warnStatus(cfg, "Your browser does not support fullscreen.");
                emitLog(ctx, "fullscreen_not_supported", "");
                return;
            }

            // If request was accepted, wait a short time to confirm.
            e.preventDefault();
            e.stopPropagation();

            var start = Date.now();
            var t = setInterval(function () {
                if (isFullscreenActive()) {
                    clearInterval(t);
                    hideMessage(cfg);
                    state.inexam = true;
                    emitLog(ctx, "fullscreen_entered", "");
                    // Trigger a custom event so the core can continue start flow if it wants.
                    $(document).trigger("local_kopere_proctoring:fullscreen_ready");
                    return;
                }

                if ((Date.now() - start) > 3000) {
                    clearInterval(t);
                    showMessage(cfg);
                    emitLog(ctx, "fullscreen_failed", "");
                }
            }, 100);
        });
    }

    return {
        init: function (ctx, cfg) {
            var state = {
                inexam: false,
                exits: 0,
                limit: Number(cfg.limit || 2)
            };

            bindFullscreenExit(ctx, cfg, state);
            gateStart(ctx, cfg, state);

            // Initial UI hint (optional).
            emitLog(ctx, "fullscreen_policy_loaded", "");
        }
    };
});
