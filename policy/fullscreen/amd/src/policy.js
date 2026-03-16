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
        let element = document.documentElement;

        let fn = element.requestFullscreen ||
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

    function getViolationHtml() {
        return $.trim($("#proctoringpolicy_fullscreen-message").html() || "");
    }

    function showViolationMessage(ctx) {
        let html = getViolationHtml();

        $("body").hasClass("")

        if (!html) {
            return;
        }

        if (ctx.api && typeof ctx.api.showViolationMessage === "function") {
            ctx.api.showViolationMessage("fullscreen", html);
        }
    }

    function hideViolationMessage(ctx) {
        if (ctx.api && typeof ctx.api.hideViolationMessage === "function") {
            ctx.api.hideViolationMessage();
        }
    }

    function warnStatus(ctx, text) {
        if (ctx.api && typeof ctx.api.showViolationMessage === "function") {
            ctx.api.showViolationMessage("fullscreen", "<p><strong>" + String(text || "") + "</strong></p>" + getViolationHtml());
        }
    }

    function emitLog(ctx, action, value) {
        $(document).trigger("local_kopere_proctoring:policy_log", [{
            cmid: ctx.cmid,
            attemptid: ctx.attemptid,
            policy: "fullscreen",
            action: action,
            value: value || ""
        }]);
    }

    function bindFullscreenExit(ctx, state) {
        $(document).on("fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange", function () {
            if (!state.inexam) {
                return;
            }

            if (!isFullscreenActive()) {
                state.exits = state.exits + 1;
                emitLog(ctx, "fullscreen_exit", String(state.exits));
                showViolationMessage(ctx);

                if (state.limit > 0 && state.exits > state.limit) {
                    warnStatus(ctx, "Fullscreen limit exceeded.");
                    emitLog(ctx, "fullscreen_blocked", "limit");
                    if (typeof ctx.lock === "function") {
                        ctx.lock("fullscreen", getViolationHtml());
                    }
                }
            }
        });
    }

    function registerStartGatekeeper(ctx, state) {
        if (!ctx.api || typeof ctx.api.registerGatekeeper !== "function") {
            return;
        }

        ctx.api.registerGatekeeper(function () {
            let deferred = $.Deferred();

            if (isFullscreenActive()) {
                hideViolationMessage(ctx);
                state.inexam = true;
                deferred.resolve(true);
                return deferred.promise();
            }

            if (!requestFullscreen()) {
                showViolationMessage(ctx);
                warnStatus(ctx, "Your browser does not support fullscreen.");
                emitLog(ctx, "fullscreen_not_supported", "");
                deferred.resolve(false);
                return deferred.promise();
            }

            let start = Date.now();
            let timer = window.setInterval(function () {
                if (isFullscreenActive()) {
                    window.clearInterval(timer);
                    hideViolationMessage(ctx);
                    state.inexam = true;
                    emitLog(ctx, "fullscreen_entered", "");
                    deferred.resolve(true);
                    return;
                }

                if ((Date.now() - start) > 3000) {
                    window.clearInterval(timer);
                    showViolationMessage(ctx);
                    emitLog(ctx, "fullscreen_failed", "");
                    deferred.resolve(false);
                }
            }, 100);

            return deferred.promise();
        });
    }

    return {
        init: function (ctx, cfg) {
            let parsedlimit = Number(cfg.limit || 0);
            let state = {
                inexam: false,
                exits: 0,
                limit: isNaN(parsedlimit) ? 0 : parsedlimit
            };

            bindFullscreenExit(ctx, state);
            registerStartGatekeeper(ctx, state);
            emitLog(ctx, "fullscreen_policy_loaded", "");
        }
    };
});
