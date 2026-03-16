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

        let style = document.createElement("style");
        style.id = "kopere-proctoring-contract-styles";
        style.textContent = ""
            + ".kopere-proctoring-footer {"
            + "    border-radius: .95rem;"
            + "    transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;"
            + "}"
            + ".kopere-proctoring-footer.is-pending {"
            + "    border: 1px solid rgba(176, 42, 55, .28);"
            + "    background: rgba(176, 42, 55, .05);"
            + "    box-shadow: 0 0 0 .35rem rgba(176, 42, 55, .08);"
            + "    padding: 1rem;"
            + "}"
            + ".kopere-proctoring-accept {"
            + "    display: inline-flex;"
            + "    align-items: center;"
            + "    gap: .85rem;"
            + "    margin: 0;"
            + "    padding: .7rem .95rem;"
            + "    border-radius: .85rem;"
            + "    border: 1px solid rgba(108, 117, 125, .2);"
            + "    background: #fff;"
            + "    cursor: pointer;"
            + "}"
            + ".kopere-proctoring-accept input[data-role=\"contract-accept\"] {"
            + "    appearance: none;"
            + "    -webkit-appearance: none;"
            + "    width: 1.35rem;"
            + "    height: 1.35rem;"
            + "    margin: 0;"
            + "    border: 2px solid #dc3545;"
            + "    border-radius: .35rem;"
            + "    background: #fff;"
            + "    position: relative;"
            + "    transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease;"
            + "}"
            + ".kopere-proctoring-accept input[data-role=\"contract-accept\"]:checked {"
            + "    background: #198754;"
            + "    border-color: #198754;"
            + "}"
            + ".kopere-proctoring-accept input[data-role=\"contract-accept\"]:checked::after {"
            + "    content: '';"
            + "    position: absolute;"
            + "    left: .30rem;"
            + "    top: .05rem;"
            + "    width: .35rem;"
            + "    height: .7rem;"
            + "    border: solid #fff;"
            + "    border-width: 0 .16rem .16rem 0;"
            + "    transform: rotate(45deg);"
            + "}"
            + ".kopere-proctoring-footer.is-pending input[data-role=\"contract-accept\"] {"
            + "    animation: kopereProctoringContractPulse 1.25s infinite;"
            + "    box-shadow: 0 0 0 .12rem rgba(220, 53, 69, .12);"
            + "}"
            + ".kopere-proctoring-footer.is-pending .kopere-proctoring-accept span {"
            + "    font-weight: 700;"
            + "    color: #842029;"
            + "}"
            + "@keyframes kopereProctoringContractPulse {"
            + "    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, .38); transform: scale(1); }"
            + "    70% { box-shadow: 0 0 0 .55rem rgba(220, 53, 69, 0); transform: scale(1.02); }"
            + "    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); transform: scale(1); }"
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

            let $container = $("#kopere-proctoring-overlay");
            if ($container.length === 0) {
                return;
            }

            ensureStyles();

            let $footer = $container.find(".kopere-proctoring-footer");
            let $checkbox = $container.find("[data-role=\"contract-accept\"]");
            let $error = $container.find("[data-role=\"contract-error\"]");
            let accepted = $checkbox.is(":checked");

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
