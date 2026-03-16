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
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {
    "use strict";

    function ensureStyles() {
        if (document.getElementById("kopere-proctoring-contract-styles")) {
            return;
        }

        var style = document.createElement("style");
        style.id = "kopere-proctoring-contract-styles";
        style.textContent = ""
            + ".kopere-proctoring-contract-footer {"
            + "    border-radius: .75rem;"
            + "    transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;"
            + "}"
            + ".kopere-proctoring-contract-footer.is-pending {"
            + "    border: 1px solid rgba(176, 42, 55, .22);"
            + "    background: rgba(176, 42, 55, .04);"
            + "    box-shadow: 0 0 0 .25rem rgba(176, 42, 55, .08);"
            + "    padding: 1rem;"
            + "}"
            + ".kopere-proctoring-contract-accept {"
            + "    display: flex;"
            + "    align-items: center;"
            + "    gap: .75rem;"
            + "    margin: 0;"
            + "}"
            + ".kopere-proctoring-contract-footer.is-pending [data-role=\"contract-accept\"] {"
            + "    border-radius: .3rem;"
            + "    animation: kopereProctoringContractPulse 1.3s infinite;"
            + "}"
            + ".kopere-proctoring-contract-footer.is-pending .kopere-proctoring-contract-accept span {"
            + "    font-weight: 600;"
            + "    color: #842029;"
            + "}"
            + "@keyframes kopereProctoringContractPulse {"
            + "    0% { box-shadow: 0 0 0 0 rgba(176, 42, 55, .35); }"
            + "    70% { box-shadow: 0 0 0 .5rem rgba(176, 42, 55, 0); }"
            + "    100% { box-shadow: 0 0 0 0 rgba(176, 42, 55, 0); }"
            + "}";

        document.head.appendChild(style);
    }

    function updatePendingState($footer, $checkbox, $error, accepted) {
        $footer.toggleClass("is-pending", !accepted);
        $checkbox.attr("aria-invalid", accepted ? "false" : "true");

        if (accepted) {
            $error.hide();
        }
    }

    return {
        init: function (ctx, cfg) {
            if (!cfg) {
                return;
            }
            if (!ctx || !ctx.api || typeof ctx.api.registerGatekeeper !== "function") {
                return;
            }

            var $container = $("#kopere-proctoring-contract-overlay");
            if ($container.length === 0) {
                return;
            }

            ensureStyles();

            var $footer = $container.find(".kopere-proctoring-contract-footer");
            var $checkbox = $container.find("[data-role=\"contract-accept\"]");
            var $error = $container.find("[data-role=\"contract-error\"]");
            var accepted = $checkbox.is(":checked");

            ctx.api.registerRequirement("contract", {
                label: cfg.requirementlabel || "Read and accept the proctoring terms",
                satisfied: accepted
            });

            updatePendingState($footer, $checkbox, $error, accepted);

            $checkbox.on("change", function () {
                accepted = $(this).is(":checked");
                ctx.api.updateRequirement("contract", {
                    satisfied: accepted
                });
                updatePendingState($footer, $checkbox, $error, accepted);
            });

            ctx.api.registerGatekeeper(function () {
                if (accepted) {
                    return true;
                }

                $error.show();
                updatePendingState($footer, $checkbox, $error, accepted);
                $checkbox.trigger("focus");
                return false;
            });
        }
    };
});
