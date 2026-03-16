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

define(["jquery"], function($) {
    "use strict";

    function showProofLink(overlay, proofurl) {
        if (!proofurl) {
            return;
        }

        overlay.find('[data-role="contract-proof-link"]').attr('href', proofurl);
        overlay.find('[data-role="contract-proof"]').show();
    }

    return {
        init: function(ctx, cfg) {
            if (!cfg || !cfg.enabled) {
                return;
            }
            if (!ctx || !ctx.api || typeof ctx.api.registerGatekeeper !== "function") {
                return;
            }

            var overlay = $("#kopere-proctoring-contract-overlay");
            if (!overlay.length) {
                return;
            }

            var accepted = !!cfg.accepted;
            var checkbox = overlay.find('[data-role="contract-accept"]');
            var errorbox = overlay.find('[data-role="contract-error"]');

            if (accepted) {
                checkbox.prop("checked", true).prop("disabled", true);
                showProofLink(overlay, cfg.proofurl || "");
            }

            ctx.api.registerGatekeeper(function() {
                if (accepted) {
                    return true;
                }

                if (!checkbox.is(":checked")) {
                    errorbox.show();
                    return false;
                }

                errorbox.hide();

                return $.ajax({
                    url: cfg.ajaxurl,
                    method: "POST",
                    dataType: "json",
                    data: {
                        action: "accept",
                        cmid: ctx.cmid,
                        attemptid: ctx.attemptid,
                        sesskey: cfg.sesskey,
                        screenresolution: (window.screen ? (window.screen.width + "x" + window.screen.height) : "")
                    }
                }).then(function(response) {
                    if (!response || !response.accepted) {
                        errorbox.show();
                        return false;
                    }

                    accepted = true;
                    checkbox.prop("disabled", true);
                    showProofLink(overlay, response.pdfurl || cfg.proofurl || "");
                    return true;
                }).fail(function() {
                    errorbox.show();
                    return false;
                });
            });
        }
    };
});
