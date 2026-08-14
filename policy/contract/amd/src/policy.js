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

define(["jquery", "core/ajax"], function ($, Ajax) {
    "use strict";

    function call(methodname, args) {
        return Ajax.call([{methodname: methodname, args: args}])[0];
    }

    return {init: function (ctx, cfg) {
        if (!cfg || !ctx || !ctx.api || typeof ctx.api.registerGatekeeper !== "function") {
            return;
        }
        let $container = $("#kopere-proctoring-overlay");
        if ($container.length === 0) {
            return;
        }
        let $footer = $container.find(".kopere-proctoring-footer");
        let $checkbox = $container.find('[data-role="contract-accept"]');
        let $error = $container.find('[data-role="contract-error"]');
        let $proof = $container.find('[data-role="proof"]');
        let $proofLink = $container.find('[data-role="proof-link"]');
        let accepted = false;

        function screenResolution() {
            return String(screen.width || 0) + "x" + String(screen.height || 0);
        }
        function satisfied() {
            return accepted || $checkbox.is(":checked");
        }
        function updateProof(result) {
            if (!result || !result.accepted) {
                return;
            }
            accepted = true;
            $checkbox.prop("checked", true).prop("disabled", true);
            $error.hide();
            if (result.pdfurl) {
                $proofLink.attr("href", result.pdfurl);
                $proof.show();
            }
            ctx.api.updateRequirement("contract", {satisfied: true});
            updatePendingState($footer, $checkbox, $error, true);
        }

        ctx.api.registerRequirement("contract", {
            label: M.util.get_string("requirement_label", "proctoringpolicy_contract"),
            satisfied: satisfied()
        });
        updatePendingState($footer, $checkbox, $error, satisfied());

        $checkbox.on("change", function () {
            ctx.api.updateRequirement("contract", {satisfied: satisfied()});
            updatePendingState($footer, $checkbox, $error, satisfied());
        });

        call("proctoringpolicy_contract_get_status", {
            cmid: Number(ctx.cmid || 0),
            attemptid: Number(ctx.attemptid || 0)
        }).done(updateProof);

        ctx.api.registerGatekeeper(function () {
            if (accepted) {
                return true;
            }
            if (!$checkbox.is(":checked")) {
                $error.show();
                updatePendingState($footer, $checkbox, $error, false);
                $checkbox.trigger("focus");
                return false;
            }

            let deferred = $.Deferred();
            call("proctoringpolicy_contract_accept_contract", {
                cmid: Number(ctx.cmid || 0),
                attemptid: Number(ctx.attemptid || 0),
                screenresolution: screenResolution(),
                geo: ""
            }).done(function (result) {
                updateProof(result);
                deferred.resolve(true);
            }).fail(function () {
                $error.show();
                updatePendingState($footer, $checkbox, $error, false);
                deferred.resolve(false);
            });
            return deferred.promise();
        });
    }};
});
