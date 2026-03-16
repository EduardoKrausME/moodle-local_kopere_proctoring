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

define(["jquery"], function ($) {
    "use strict";

    function getString(key, fallback) {
        try {
            if (window.M && window.M.util && window.M.util.get_string) {
                return window.M.util.get_string(key, "local_kopere_proctoring");
            }
        } catch (e) {
            // Ignore string loading errors and use fallback.
        }

        return fallback || "";
    }

    function escapeHtml(text) {
        return String(text || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function init(cmid, attemptid, policies) {

        var $container = $("[data-kopere-proctoring=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        var $messages = $container.find("[data-kopere-proctoring=\"messages\"]");
        var $startButton = $container.find("[data-kopere-proctoring=\"start-button\"]");
        var pendingModules = 0;

        // Gatekeepers registered by policies (ex: contract).
        var gatekeepers = [];
        var requirements = {};

        function getMissingRequirements() {
            return Object.keys(requirements).map(function (key) {
                return requirements[key];
            }).filter(function (item) {
                return !item.satisfied;
            });
        }

        function renderRequirementsDescription() {
            if ($messages.length === 0) {
                return;
            }

            var missing = getMissingRequirements();
            if (missing.length === 0) {
                $messages.html(
                    escapeHtml(getString(
                        "description_ready",
                        M.util.get_string("description_ready", "local_kopere_proctoring")
                    ))
                );
                return;
            }

            var items = missing.map(function (requirement) {
                return "<li>" + escapeHtml(requirement.label || "") + "</li>";
            }).join("");

            $messages.html(
                "<div class=\"mb-2\"><strong>" +
                escapeHtml(getString(
                    "description_pending",
                    M.util.get_string("description_pending", "local_kopere_proctoring")
                )) +
                "</strong></div>" +
                "<ul class=\"mb-0 pl-3\">" + items + "</ul>"
            );
        }

        function refreshStartState() {
            var ready = getMissingRequirements().length === 0;
            $startButton.prop("disabled", !ready);
            renderRequirementsDescription();
        }

        /**
         * Run all registered gatekeepers in sequence.
         *
         * Each gatekeeper can:
         *  - return true/undefined  -> continue to the next one
         *  - return false           -> abort the sequence (final result = false)
         *  - return jQuery.Promise  -> resolve(true/false) controlling the sequence
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
                    deferred.resolve(false);
                    return;
                }

                if (result && typeof result.then === "function") {
                    result.then(function (ok) {
                        if (ok === false) {
                            deferred.resolve(false);
                        } else {
                            next();
                        }
                    }).fail(function () {
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

        // Shared context passed to all policies.
        var context = {
            cmid: cmid,
            attemptid: attemptid
        };

        /**
         * Shared API exposed to all policies.
         */
        context.api = {
            registerGatekeeper: function (fn) {
                if (typeof fn === "function") {
                    gatekeepers.push(fn);
                }
            },
            runGatekeepers: runGatekeepers,
            registerRequirement: function (key, requirement) {
                if (!key) {
                    return;
                }

                requirements[key] = $.extend({
                    key: key,
                    label: key,
                    satisfied: false
                }, requirement || {});

                refreshStartState();
            },
            updateRequirement: function (key, updates) {
                if (!key || !requirements[key]) {
                    return;
                }

                requirements[key] = $.extend(requirements[key], updates || {});
                refreshStartState();
            },
            refreshStartState: refreshStartState,
            isReady: function () {
                return getMissingRequirements().length === 0;
            }
        };

        $startButton.on("click", function (e) {
            if (!context.api.isReady()) {
                e.preventDefault();
                e.stopPropagation();
                refreshStartState();
                return;
            }

            runGatekeepers().done(function (ok) {
                if (ok === false) {
                    refreshStartState();
                }
            });
        });

        function markModuleLoaded() {
            pendingModules = pendingModules - 1;
            if (pendingModules <= 0) {
                refreshStartState();
            }
        }

        if (!policies || policies.length === 0) {
            refreshStartState();
            return;
        }

        pendingModules = policies.length;

        policies.forEach(function (p) {
            if (!p.amd) {
                markModuleLoaded();
                return;
            }

            requirejs([p.amd], function (mod) {
                if (mod && typeof mod.init === "function") {
                    mod.init(context, p.config || {});
                }

                markModuleLoaded();
            }, function () {
                markModuleLoaded();
            });
        });
    }

    return {
        init: init
    };
});
