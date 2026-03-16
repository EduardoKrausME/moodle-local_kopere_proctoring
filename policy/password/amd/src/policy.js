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
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {
    "use strict";

    function getString(key, fallback) {
        let component = "proctoringpolicy_password";
        try {
            if (window.M && window.M.util && window.M.util.get_string) {
                return window.M.util.get_string(key, component);
            }
        } catch (e) {
            // ignore
        }
        return fallback || "";
    }

    function buildBrowserInfo() {
        let info = {
            userAgent: navigator.userAgent || "",
            platform: navigator.platform || "",
            language: navigator.language || "",
            screen: String(screen.width) + "x" + String(screen.height),
            timezoneOffset: new Date().getTimezoneOffset()
        };
        return JSON.stringify(info);
    }

    function postAjax(url, data) {
        return $.ajax({
            url: url,
            method: "POST",
            dataType: "json",
            data: data
        });
    }

    function init(ctx, cfg) {
        if (!cfg ) {
            return;
        }

        let cmid = Number(ctx.cmid || 0);
        let attemptid = Number(ctx.attemptid || 0);

        let $container = $("[data-kppass=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        let $status = $container.find("[data-kppass=\"status\"]");
        let $inputPassword = $container.find("[data-kppass=\"password\"]");
        let $btnSubmit = $container.find("[data-kppass=\"submit\"]");

        let polling = null;

        function setStatus(text) {
            $status.text(text || "");
        }

        function handleStatusResponse(resp) {
            if (!resp || !resp.status) {
                return;
            }

            if (resp.status === "pending") {
                setStatus(getString("js_status_pending", "Request sent. Waiting approval."));
            } else if (resp.status === "approved") {
                setStatus(getString("js_status_approved", "Approved. You can start the exam."));
                // Notify core (generic event, sem chamada direta ao plugin mãe).
                let ev = new CustomEvent("kopere_proctoring_password_authorized", {
                    detail: {
                        cmid: cmid,
                        attemptid: attemptid
                    }
                });
                window.dispatchEvent(ev);
                if (polling) {
                    window.clearInterval(polling);
                    polling = null;
                }
            } else if (resp.status === "blocked") {
                setStatus(getString("js_status_blocked", "You are temporarily blocked."));
                if (polling) {
                    window.clearInterval(polling);
                    polling = null;
                }
            }
        }

        function startPolling() {
            if (polling) {
                return;
            }
            polling = window.setInterval(function () {
                postAjax(`${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/ajax.php`, {
                    action: "check",
                    cmid: cmid,
                    attemptid: attemptid
                }).done(function (r) {
                    handleStatusResponse(r);
                });
            }, 3000);
        }
        startPolling();

        $btnSubmit.on("click", function () {
            let code = ($inputPassword.val() || "").replace(/\D/g, "");
            if (code.length !== 8) {
                setStatus(getString("js_wrong_password", "Invalid password."));
                return;
            }

            postAjax(`${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/ajax.php`, {
                action: "submitcode",
                cmid: cmid,
                attemptid: attemptid,
                code: code
            }).done(function (resp) {
                if (!resp) {
                    return;
                }
                if (resp.error === "blocked") {
                    setStatus(getString("js_toomany_errors", "Too many wrong attempts. Please wait 10 minutes."));
                    return;
                }
                if (resp.error === "wrong") {
                    setStatus(getString("js_wrong_password", "Invalid password."));
                    return;
                }
                if (resp.status === "approved") {
                    handleStatusResponse(resp);
                }
            });
        });
    }

    return {
        init: init
    };
});
