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

            var accepted = false;
            // ... pega checkbox, botões etc.

            ctx.api.registerGatekeeper(function () {
                if (accepted) {
                    return true;
                }

                // Mostra overlay e retorna um jQuery.Promise que resolve true/false
                var d = $.Deferred();

                // ex.: ao clicar "aceitar", marcar accepted = true e d.resolve(true);
                // ao clicar "cancelar", d.resolve(false);

                return d.promise();
            });
        }
    };
});
