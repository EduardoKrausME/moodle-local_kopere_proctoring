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

    function getString(key) {
        return M.util.get_string(key, "proctoringpolicy_password");
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
        if (!cfg) {
            return;
        }

        let cmid = Number(ctx.cmid || 0);
        let attemptid = Number(ctx.attemptid || 0);
        let ajaxurl = M.cfg.wwwroot + "/local/kopere_proctoring/policy/password/ajax.php";

        let $container = $("[data-kppass=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        let $status = $container.find("[data-kppass=\"status\"]");
        let $inputPassword = $container.find("[data-kppass=\"password\"]");
        let $btnSubmit = $container.find("[data-kppass=\"submit\"]");

        let polling = null;

        if (ctx.api && typeof ctx.api.registerRequirement === "function") {
            ctx.api.registerRequirement("password", {
                label: cfg.requirementlabel || "Teacher approval or exam password",
                satisfied: false
            });
        }

        function setRequirementSatisfied(satisfied) {
            if (ctx.api && typeof ctx.api.updateRequirement === "function") {
                ctx.api.updateRequirement("password", {
                    satisfied: !!satisfied
                });
            }
        }

        function setStatus(kind, text) {
            if (!text) {
                $status.empty().hide();
                return;
            }

            let classname = "alert-info";
            if (kind === "success") {
                classname = "alert-success";
            } else if (kind === "warning") {
                classname = "alert-warning";
            } else if (kind === "danger") {
                classname = "alert-danger";
            }

            $status.html('<div class="alert ' + classname + ' mb-2">' + text + '</div>').show();
        }

        function stopPolling() {
            if (polling) {
                window.clearInterval(polling);
                polling = null;
            }
        }

        function handleStatusResponse(resp) {
            if (!resp) {
                setRequirementSatisfied(false);
                setStatus("warning", getString("js_status_pending"));
                return;
            }

            if (resp.error === "blocked" || resp.status === "blocked") {
                setRequirementSatisfied(false);
                setStatus("danger", getString("js_status_blocked"));
                stopPolling();
                return;
            }

            if (resp.status === "approved") {
                setRequirementSatisfied(true);
                setStatus("success", getString("js_status_approved"));
                $inputPassword.prop("disabled", true);
                $(".kopere-password-policy .kopere-proctoring-footer").hide(300);
                $btnSubmit.prop("disabled", true);
                window.dispatchEvent(new CustomEvent("kopere_proctoring_password_authorized", {
                    detail: {
                        cmid: cmid,
                        attemptid: attemptid
                    }
                }));
                stopPolling();
                return;
            }

            setRequirementSatisfied(false);
            setStatus("warning", getString("js_status_pending"));
        }

        function checkStatus() {
            return postAjax(ajaxurl, {
                action: "check",
                cmid: cmid,
                attemptid: attemptid
            }).done(function (response) {
                handleStatusResponse(response);
            }).fail(function () {
                setRequirementSatisfied(false);
            });
        }

        function startPolling() {
            if (polling) {
                return;
            }

            checkStatus();
            polling = window.setInterval(checkStatus, 3000);
        }

        function ensureRequestExists() {
            return postAjax(ajaxurl, {
                action: "request",
                cmid: cmid,
                attemptid: attemptid,
                browserinfo: window.navigator.userAgent || ""
            }).done(function (response) {
                handleStatusResponse(response);
            }).always(function () {
                startPolling();
            });
        }

        ensureRequestExists();

        $btnSubmit.on("click", function () {
            let code = ($inputPassword.val() || "").replace(/\D/g, "");
            if (code.length !== 8) {
                setRequirementSatisfied(false);
                setStatus("danger", getString("js_wrong_password"));
                return;
            }

            postAjax(ajaxurl, {
                action: "submitcode",
                cmid: cmid,
                attemptid: attemptid,
                code: code
            }).done(function (resp) {
                if (!resp) {
                    return;
                }
                if (resp.error === "blocked") {
                    setRequirementSatisfied(false);
                    setStatus("danger", getString("js_toomany_errors"));
                    stopPolling();
                    return;
                }
                if (resp.error === "wrong") {
                    setRequirementSatisfied(false);
                    setStatus("danger", getString("js_wrong_password"));
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
