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
 * start.js
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function($) {
    "use strict";

    function init(payload) {
        if (!payload || !payload.policies) {
            return;
        }

        // Gatekeepers registered by policies (ex: contract).
        var gatekeepers = [];

        // Shared context passed to all policies.
        var context = {
            cmid: payload.cmid,
            attemptid: payload.attemptid
        };

        /**
         * Run all registered gatekeepers in sequence.
         *
         * Each gatekeeper can:
         *  - return true/undefined  -> continua para o próximo
         *  - return false           -> aborta a sequência (resultado final = false)
         *  - return jQuery.Promise  -> resolve(true/false) controlando a sequência
         *
         * @returns {jQuery.Promise} resolves(true|false)
         */
        function runGatekeepers() {
            var deferred = $.Deferred();
            var index = 0;

            function next() {
                if (index >= gatekeepers.length) {
                    deferred.resolve(true);
                    return;
                }

                var fn = gatekeepers[index++];
                var result = true;

                try {
                    result = fn(context);
                } catch (e) {
                    // On error, we fail safe: do not start the exam.
                    deferred.resolve(false);
                    return;
                }

                // Async gatekeeper using Promise-like (ex: jQuery.Promise).
                if (result && typeof result.then === "function") {
                    result.then(function(ok) {
                        if (ok === false) {
                            deferred.resolve(false);
                        } else {
                            next();
                        }
                    }).fail(function() {
                        deferred.resolve(false);
                    });
                } else if (result === false) {
                    deferred.resolve(false);
                } else {
                    next();
                }
            }

            next();
            return deferred.promise();
        }

        /**
         * Shared API exposed to all policies.
         * - registerGatekeeper(fn): policies can add pre-start checks.
         * - runGatekeepers(): code that actually starts the exam can call this.
         */
        context.api = {
            registerGatekeeper: function(fn) {
                if (typeof fn === "function") {
                    gatekeepers.push(fn);
                }
            },
            runGatekeepers: runGatekeepers
        };

        // Load and init all policies.
        payload.policies.forEach(function(p) {
            if (!p.amd) {
                return;
            }

            requirejs([p.amd], function(mod) {
                if (mod && typeof mod.init === "function") {
                    mod.init(context, p.config || {});
                }
            });
        });
    }

    return {
        init: init
    };
});
