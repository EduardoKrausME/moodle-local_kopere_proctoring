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

    function ensurePopupStyles() {
        if (document.getElementById("kopere-proctoring-shared-styles")) {
            return;
        }

        let style = document.createElement("style");
        style.id = "kopere-proctoring-shared-styles";
        style.textContent = ""
            + ".kopere-proctoring-popup {"
            + "    border-radius: .9rem;"
            + "    box-shadow: 0 .85rem 2rem rgba(0, 0, 0, .16);"
            + "    border: 1px solid rgba(220, 53, 69, .2);"
            + "}"
            + ".kopere-proctoring-popup__title {"
            + "    font-size: 1rem;"
            + "    font-weight: 700;"
            + "    margin-bottom: .5rem;"
            + "}";

        document.head.appendChild(style);
    }

    function init(payload) {
        payload = payload || {};

        let cmid = Number(payload.cmid || 0);
        let attemptid = Number(payload.attemptid || 0);
        let policies = Array.isArray(payload.policies) ? payload.policies : [];

        let $container = $("[data-kopere-proctoring=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        let $description = $container.find("[data-kopere-proctoring=\"description\"]");
        let $messages = $container.find("[data-kopere-proctoring=\"messages\"]");
        let $startButton = $container.find("[data-kopere-proctoring=\"start-button\"]");
        let $startBlock = $container.find("[data-kopere-proctoring=\"start\"]");
        let $lockedBlock = $container.find("[data-kopere-proctoring=\"locked\"]");
        let $lockedMessage = $container.find("[data-kopere-proctoring=\"locked-message\"]");
        let $runningBlock = $container.find("[data-kopere-proctoring=\"running\"]");
        let pendingModules = 0;
        let examStarted = false;
        let examLocked = false;
        let startInProgress = false;

        // Gatekeepers registered by policies (ex: contract).
        let gatekeepers = [];
        let requirements = {};

        function syncContainerState() {
            $container.attr("data-kopere-exam-started", examStarted ? "1" : "0");
            $container.attr("data-kopere-exam-locked", examLocked ? "1" : "0");
        }

        function getMissingRequirements() {
            return Object.keys(requirements).map(function (key) {
                return requirements[key];
            }).filter(function (item) {
                return !item.satisfied;
            });
        }

        function renderRequirementsDescription() {
            if ($description.length === 0) {
                return;
            }

            let missing = getMissingRequirements();
            if (missing.length === 0) {
                $description.html(
                    '<div class="alert alert-success mb-0">' +
                    M.util.get_string("description_ready", "local_kopere_proctoring") +
                    "</div>"
                );
                return;
            }

            let items = missing.map(function (requirement) {
                return "<li>" + (requirement.label || "") + "</li>";
            }).join("");

            $description.html(
                '<div class="alert alert-warning mb-0">' +
                '<div class="mb-2"><strong>' +
                M.util.get_string("description_pending", "local_kopere_proctoring") +
                "</strong></div>" +
                '<ul class="mb-0 pl-3">' + items + "</ul>" +
                "</div>"
            );
        }

        function refreshStartState() {
            let ready = !examStarted && !examLocked && getMissingRequirements().length === 0;
            $startButton.prop("disabled", !ready || startInProgress);
            renderRequirementsDescription();
        }

        function showViolationMessage(policyKey, html) {
            let title = M.util.get_string("locked_title", "local_kopere_proctoring");

            ensurePopupStyles();
            if ($messages.length === 0) {
                return;
            }

            if (!html) {
                $messages.empty().hide();
                return;
            }

            $messages.html(
                '<div class="alert alert-danger kopere-proctoring-popup mb-0" data-kopere-violation-key="' +
                (policyKey || "") +
                '">' +
                '<div class="kopere-proctoring-popup__title">' + title + "</div>" +
                '<div class="kopere-proctoring-popup__content">' + html + "</div>" +
                "</div>"
            ).show();
        }

        function hideViolationMessage() {
            if ($messages.length === 0) {
                return;
            }

            $messages.empty().hide();
        }

        function startExam() {
            examStarted = true;
            examLocked = false;
            startInProgress = false;
            syncContainerState();
            hideViolationMessage();

            if ($startBlock.length) {
                $startBlock.hide();
            }
            if ($lockedBlock.length) {
                $lockedBlock.hide();
            }
            if ($runningBlock.length) {
                $runningBlock.show();
            }

            $container.hide();
            refreshStartState();
        }

        function lockExam(policyKey, html) {
            examLocked = true;
            startInProgress = false;
            syncContainerState();
            showViolationMessage(policyKey, html || "");
            $startButton.prop("disabled", true);

            if ($lockedMessage.length) {
                $lockedMessage.html(html || M.util.get_string("locked_default_message", "local_kopere_proctoring"));
            }

            $container.show();

            if ($startBlock.length) {
                $startBlock.hide();
            }
            if ($runningBlock.length) {
                $runningBlock.hide();
            }
            if ($lockedBlock.length) {
                $lockedBlock.show();
            }
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
            let deferred = $.Deferred();
            let index = 0;

            function next() {
                if (index >= gatekeepers.length) {
                    deferred.resolve(true);
                    return;
                }

                let fn = gatekeepers[index++];
                let result = true;

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
        let context = {
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
                return !examStarted && !examLocked && getMissingRequirements().length === 0;
            },
            isExamStarted: function () {
                return examStarted;
            },
            isExamActive: function () {
                return examStarted && !examLocked;
            },
            startExam: startExam,
            showViolationMessage: showViolationMessage,
            hideViolationMessage: hideViolationMessage,
            lockExam: lockExam
        };

        context.sendEvent = function (eventKey, payload) {
            $(document).trigger("local_kopere_proctoring:server_event", [{
                eventkey: eventKey,
                cmid: cmid,
                attemptid: attemptid,
                payload: payload || {}
            }]);
        };

        context.lock = function (policyKey, html) {
            lockExam(policyKey, html || "");
        };

        $startButton.on("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (startInProgress) {
                return;
            }

            if (!context.api.isReady()) {
                refreshStartState();
                return;
            }

            startInProgress = true;
            refreshStartState();

            runGatekeepers().done(function (ok) {
                if (ok === false) {
                    startInProgress = false;
                    refreshStartState();
                    return;
                }

                context.api.startExam();
            }).fail(function () {
                startInProgress = false;
                refreshStartState();
            });
        });

        function markModuleLoaded() {
            pendingModules = pendingModules - 1;
            if (pendingModules <= 0) {
                refreshStartState();
            }
        }

        syncContainerState();

        if (!policies || policies.length === 0) {
            refreshStartState();
            return;
        }

        pendingModules = policies.length;

        policies.forEach(function (p) {
            if (p && p.config && p.config.requirementlabel) {
                context.api.registerRequirement(p.key, {
                    label: p.config.requirementlabel,
                    satisfied: false
                });
            }

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
