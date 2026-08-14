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

    function init(payload) {
        payload = payload || {};

        $("body").removeClass("proctoring-start");

        setInterval(function () {
            //Function("\"use strict\";(() => { debugger; })()")();
        }, 500);

        let cmid = Number(payload.cmid || 0);
        let attemptid = Number(payload.attemptid || 0);
        let policies = Array.isArray(payload.policies) ? payload.policies : [];

        let $container = $("[data-kopere-proctoring=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        let $description = $container.find('[data-kopere-proctoring="description"]');
        let $messages = $();
        let $startButton = $container.find("[data-kopere-proctoring=\"start-button\"]");
        let $startBlock = $container.find("[data-kopere-proctoring=\"start\"]");
        let $lockedBlock = $container.find("[data-kopere-proctoring=\"locked\"]");
        let $lockedMessage = $container.find("[data-kopere-proctoring=\"locked-message\"]");
        let $runningBlock = $container.find("[data-kopere-proctoring=\"running\"]");
        let pendingModules = 0;
        let examStarted = false;
        let examLocked = false;
        let startInProgress = false;
        let startCallbacksExecuted = false;
        let visibilityGuardStarted = false;
        let visibilityGuardReleased = false;
        let visibilityGuardTimer = null;
        let modalGuardTimer = null;
        let visibilityGuardObserver = null;
        let visibilityGuardScheduled = false;
        let protectedElements = [];

        // Gatekeepers registered by policies (ex: contract).
        let gatekeepers = [];
        let startCallbacks = [];
        let requirements = {};

        function isGuardException(element) {
            if (!element || element.nodeType !== 1) {
                return true;
            }

            return Boolean($(element).closest("#kopere-proctoring-modal, #kopere-proctoring-reload-popup").length);
        }

        function protectElementVisibility(element) {
            if (isGuardException(element)) {
                return;
            }

            let $element = $(element);

            if (typeof $element.data("kopereProctoringOriginalStyle") === "undefined") {
                $element.data("kopereProctoringOriginalStyle", $element.attr("style") || "");
            }

            if (protectedElements.indexOf(element) === -1) {
                protectedElements.push(element);
            }

            $element.attr("data-kopere-proctoring-visibility-guard", "1");
            element.style.setProperty("filter", "blur(8px)", "important");
            element.style.setProperty("pointer-events", "none", "important");
        }

        function restoreElementVisibility(element) {
            if (!element || element.nodeType !== 1) {
                return;
            }

            let $element = $(element);
            let originalStyle = $element.data("kopereProctoringOriginalStyle");

            if (typeof originalStyle === "undefined") {
                return;
            }

            if (originalStyle) {
                $element.attr("style", originalStyle);
            } else {
                $element.removeAttr("style");
            }

            $element.removeAttr("data-kopere-proctoring-visibility-guard");
            $element.removeData("kopereProctoringOriginalStyle");
        }

        function protectChildrenExceptModal(root, modalElement) {
            if (!root || root.nodeType !== 1 || isGuardException(root)) {
                return;
            }

            Array.prototype.forEach.call(root.children, function (child) {
                if (child === modalElement || (modalElement && $.contains(modalElement, child)) || isGuardException(child)) {
                    return;
                }

                if (modalElement && $.contains(child, modalElement)) {
                    protectChildrenExceptModal(child, modalElement);
                    return;
                }

                protectElementVisibility(child);
            });
        }

        function protectPageBeforeStart() {
            if (visibilityGuardReleased || examStarted) {
                return;
            }

            let modalElement = document.getElementById("kopere-proctoring-modal");

            $("[role=\"main\"], #region-main, #region-main-box").first().each(function () {
                protectChildrenExceptModal(this, modalElement);
            });
            $("body *").css({"user-select": "none"});
        }

        function getHiddenReason(element) {
            if (!element) {
                return "removed";
            }

            if (element.hidden || element.getAttribute("aria-hidden") === "true") {
                return "hidden";
            }

            let current = element;
            while (current && current.nodeType === 1) {
                let style = window.getComputedStyle(current);

                if (style.display === "none") {
                    return "display";
                }

                // if (style.visibility === "hidden" || style.visibility === "collapse") {
                //     return "visibility";
                // }

                if (Number(style.opacity) === 0) {
                    return "opacity";
                }

                current = current.parentElement;
            }

            if (element.getClientRects().length === 0) {
                return "rect";
            }

            return "";
        }

        function enforceReloadPopupStyles(popup) {
            popup.style.setProperty("position", "fixed", "important");
            popup.style.setProperty("inset", "0", "important");
            popup.style.setProperty("z-index", "2147483647", "important");
            popup.style.setProperty("display", "flex", "important");
            popup.style.setProperty("align-items", "center", "important");
            popup.style.setProperty("justify-content", "center", "important");
            popup.style.setProperty("background", "rgba(0, 0, 0, 0.72)", "important");
            popup.style.setProperty("padding", "20px", "important");
            popup.style.setProperty("filter", "blur(0)", "important");
            popup.style.setProperty("opacity", "1", "important");
            popup.style.setProperty("pointer-events", "auto", "important");
        }

        function showReloadRequiredPopup() {
            if (visibilityGuardReleased || examStarted) {
                return;
            }

            let popup = document.getElementById("kopere-proctoring-reload-popup");
            if (!popup) {
                return;
            }

            enforceReloadPopupStyles(popup);
            $(popup).find("[data-kopere-proctoring-reload-button]")
                .off("click.local_kopere_proctoring_reload")
                .on("click.local_kopere_proctoring_reload", function () {
                    window.location.reload();
                });
        }

        function checkModalIntegrity() {
            if (visibilityGuardReleased || examStarted) {
                return;
            }

            let modalElement = document.getElementById("kopere-proctoring-modal");
            if (getHiddenReason(modalElement)) {
                showReloadRequiredPopup();
            }
        }

        function scheduleVisibilityGuardCheck() {
            if (visibilityGuardReleased || visibilityGuardScheduled) {
                return;
            }

            visibilityGuardScheduled = true;
            window.setTimeout(function () {
                visibilityGuardScheduled = false;
                protectPageBeforeStart();
                checkModalIntegrity();
            }, 100);
        }

        function startVisibilityGuard() {
            if (visibilityGuardStarted) {
                return;
            }

            visibilityGuardStarted = true;
            protectPageBeforeStart();
            checkModalIntegrity();

            visibilityGuardTimer = window.setInterval(protectPageBeforeStart, 5000);
            modalGuardTimer = window.setInterval(checkModalIntegrity, 1000);

            if (window.MutationObserver && document.body) {
                visibilityGuardObserver = new MutationObserver(scheduleVisibilityGuardCheck);
                visibilityGuardObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ["style", "class", "hidden", "aria-hidden"]
                });
            }
        }

        function releaseVisibilityGuard() {
            visibilityGuardReleased = true;

            if (visibilityGuardTimer) {
                window.clearInterval(visibilityGuardTimer);
                visibilityGuardTimer = null;
            }

            if (modalGuardTimer) {
                window.clearInterval(modalGuardTimer);
                modalGuardTimer = null;
            }

            if (visibilityGuardObserver) {
                visibilityGuardObserver.disconnect();
                visibilityGuardObserver = null;
            }

            protectedElements.forEach(restoreElementVisibility);
            protectedElements = [];
            $("[data-kopere-proctoring-visibility-guard]").each(function () {
                restoreElementVisibility(this);
            });
            $("#kopere-proctoring-reload-popup").remove();
        }

        function getMessagesContainer() {
            return $("#kopere-proctoring-runtime-messages");
        }

        function runStartCallbacks() {
            if (startCallbacksExecuted) {
                return;
            }

            startCallbacksExecuted = true;
            startCallbacks.forEach(function (fn) {
                try {
                    fn(context);
                } catch (e) {
                    // Keep the quiz start flow alive even if one policy fails to start.
                }
            });
        }

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
            let $ready = $description.find("[data-kopere-proctoring-description-ready]");
            let $pending = $description.find("[data-kopere-proctoring-description-pending]");
            let $list = $description.find("[data-kopere-proctoring-description-list]");
            $list.empty();

            if (missing.length === 0) {
                $pending.hide();
                $ready.show();
                return;
            }

            missing.forEach(function (requirement) {
                $("<li>").text(requirement.label || "").appendTo($list);
            });
            $ready.hide();
            $pending.show();
        }

        function refreshStartState() {
            let ready = pendingModules <= 0 && !examStarted && !examLocked && getMissingRequirements().length === 0;
            $startButton.prop("disabled", !ready || startInProgress);
            renderRequirementsDescription();
        }

        function showViolationMessage(policyKey, html) {
            if (!html) {
                hideViolationMessage();
                return;
            }

            $messages = getMessagesContainer();
            $messages.find("[data-kopere-proctoring-violation]")
                .attr("data-kopere-violation-key", policyKey);
            $messages.find("[data-kopere-proctoring-violation-content]").html(html);
            $messages.show();
        }

        function hideViolationMessage() {
            $messages = getMessagesContainer();
            if ($messages.length === 0) {
                return;
            }

            $messages.find("[data-kopere-proctoring-violation]")
                .removeAttr("data-kopere-violation-key");
            $messages.find("[data-kopere-proctoring-violation-content]").empty();
            $messages.hide();
        }

        function startExam() {
            releaseVisibilityGuard();

            examStarted = true;
            examLocked = false;
            startInProgress = false;
            syncContainerState();
            hideViolationMessage();
            runStartCallbacks();

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
            registerStartCallback: function (fn) {
                if (typeof fn === "function") {
                    startCallbacks.push(fn);
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
                return pendingModules <= 0 && !examStarted && !examLocked && getMissingRequirements().length === 0;
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

        function initPolicyAcknowledgements() {
            $container.find("[data-kopere-policy-ack]").each(function () {
                let $checkbox = $(this);
                let key = String($checkbox.attr("data-kopere-policy-ack") || "");
                if (!key) {
                    return;
                }

                let label = String($checkbox.attr("data-kopere-policy-label") || key);
                let $policy = $checkbox.closest("[data-kopere-policy-card]");
                let $footer = $checkbox.closest(".kopere-proctoring-footer");
                let $error = $policy.find("[data-kopere-policy-ack-error]");

                function setSatisfied() {
                    let accepted = $checkbox.is(":checked");

                    context.api.updateRequirement(key, {
                        satisfied: accepted
                    });

                    updatePendingState($footer, $checkbox, $error, accepted);
                    $policy.toggleClass("is-pending", !accepted);
                }

                context.api.registerRequirement(key, {
                    label: label,
                    satisfied: $checkbox.is(":checked")
                });

                setSatisfied();

                $checkbox.on("change.local_kopere_proctoring_ack", function () {
                    setSatisfied();
                });

                context.api.registerGatekeeper(function () {
                    if ($checkbox.is(":checked")) {
                        return true;
                    }

                    $policy.addClass("is-pending");
                    $error.show();
                    $checkbox.trigger("focus");
                    setSatisfied();
                    return false;
                });
            });
        }

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
        startVisibilityGuard();

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

        initPolicyAcknowledgements();
    }

    return {
        init: init
    };
});

function updatePendingState($footer, $control, $error, accepted) {
    if ($footer && $footer.length) {
        $footer.toggleClass("is-pending", !accepted);
    }

    if ($control && $control.length) {
        $control.attr("aria-invalid", accepted ? "false" : "true");
    }

    if (accepted && $error && $error.length) {
        $error.hide();
    }
}
