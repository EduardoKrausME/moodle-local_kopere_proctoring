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

            let $footer = $container.find(".kopere-proctoring-footer");
            let $checkbox = $container.find("[data-role=\"contract-accept\"]");
            let $error = $container.find("[data-role=\"contract-error\"]");
            let accepted = $checkbox.is(":checked");

            ctx.api.registerRequirement("contract", {
                label: M.util.get_string("requirement_label", "proctoringpolicy_contract"),
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
