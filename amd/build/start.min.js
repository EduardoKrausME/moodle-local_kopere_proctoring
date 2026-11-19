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

define(["jquery", "core/ajax"], function ($, Ajax) {
    "use strict";


    /**
     * Correlate one visible-window blur with a real fullscreen exit.
     *
     * Browser events may arrive in either order. One matched blur is consumed
     * per exit; hidden-tab events and subsequent focus losses remain separate.
     *
     * @returns {Object} Local correlation state for one quiz page.
     */
    function createViolationCorrelation() {
        const windowMs = 350;
        let fullscreenExitAt = null;
        let focusBlurMatched = false;

        return {
            noteFullscreenExit: function () {
                fullscreenExitAt = Date.now();
                focusBlurMatched = false;
            },
            clearFullscreenExit: function () {
                fullscreenExitAt = null;
                focusBlurMatched = false;
            },
            consumeFocusBlur: function (occurredAt, hidden) {
                if (hidden || fullscreenExitAt === null || focusBlurMatched ||
                        typeof occurredAt !== "number" ||
                        Math.abs(fullscreenExitAt - occurredAt) > windowMs) {
                    return false;
                }
                focusBlurMatched = true;
                return true;
            }
        };
    }

    function init(payload) {
        payload = payload || {};

        $("body").removeClass("proctoring-start");

        let cmid = Number(payload.cmid || 0);
        let attemptid = Number(payload.attemptid || 0);
        let policies = Array.isArray(payload.policies) ? payload.policies : [];
        // This is authoritative state from PHP, verified for this quiz attempt and user.
        let accessgranted = payload.accessgranted === true;

        let $container = $("[data-kopere-proctoring=\"container\"]");
        if ($container.length === 0) {
            return;
        }

        let $description = $container.find('[data-kopere-proctoring="description"]');
        let $messages = $();
        let $startButton = $container.find("[data-kopere-proctoring=\"start-button\"]");
        let $startBlock = $container.find("[data-kopere-proctoring=\"start\"]");
        let $startError = $container.find('[data-kopere-proctoring="start-error"]');
        let $resumeBlock = $container.find('[data-kopere-proctoring="resume"]');
        let $resumeButton = $container.find('[data-kopere-proctoring="resume-button"]');
        let $resumeError = $container.find('[data-kopere-proctoring="resume-error"]');
        let $lockedBlock = $container.find("[data-kopere-proctoring=\"locked\"]");
        let $lockedMessage = $container.find("[data-kopere-proctoring=\"locked-message\"]");
        let $runningBlock = $container.find("[data-kopere-proctoring=\"running\"]");
        let $returnButton = $container.find('[data-kopere-proctoring="return-button"]');
        let $dismissViolationButton = $('[data-kopere-proctoring="dismiss-violation"]');
        let previousViolationFocus = null;
        // Runtime policy alerts use a queued shared dispatcher.
        let activePolicyAlert = null;
        let policyAlertQueue = [];
        let pendingModules = 0;
        let moduleFailed = false;
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
        let navigating = false;
        let navigationResetTimer = null;
        let violationCorrelation = createViolationCorrelation();

        function fullscreenActive() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement ||
                document.mozFullScreenElement || document.msFullscreenElement);
        }

        function fullscreenRequired() {
            return policies.some(function (policy) {
                return policy && policy.key === "fullscreen" && !!policy.amd;
            });
        }

        function markQuizNavigation() {
            navigating = true;
            if (navigationResetTimer) {
                window.clearTimeout(navigationResetTimer);
            }
            // A cancelled submit must not disable monitoring for the rest of the attempt.
            navigationResetTimer = window.setTimeout(function () { navigating = false; }, 4000);
        }

        // Suppress only the browser lifecycle signals generated by normal Moodle navigation.
        // This is not a persistent exemption from focus or fullscreen violations.
        $(document).on("submit.kopere_proctoring_navigation", "form", function () {
            let action = this.action || window.location.href;
            try {
                let target = new URL(action, window.location.href);
                if (target.origin === window.location.origin &&
                        target.pathname.indexOf("/mod/quiz/") !== -1) {
                    markQuizNavigation();
                }
            } catch (e) {
                // Invalid or non-quiz form destinations are not trusted navigation.
            }
        });
        $(document).on("click.kopere_proctoring_navigation", "a[href]", function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ||
                    (this.target && this.target !== "_self")) {
                return;
            }
            try {
                let target = new URL(this.href, window.location.href);
                if (target.origin === window.location.origin &&
                        target.pathname.indexOf("/mod/quiz/") !== -1) {
                    markQuizNavigation();
                }
            } catch (e) {
                // Only real quiz links suppress the unload lifecycle signal.
            }
        });

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
            // Keep the overlay outside footer/page stacking contexts.
            let $overlay = $("#kopere-proctoring-runtime-messages");
            if ($overlay.length && !$overlay.parent().is("body")) {
                $overlay.appendTo(document.body);
            }
            return $overlay;
        }

        function runStartCallbacks() {
            if (startCallbacksExecuted) {
                return true;
            }

            // Do not silently release an exam if one mandatory policy fails to start.
            for (let i = 0; i < startCallbacks.length; i++) {
                try {
                    startCallbacks[i](context);
                } catch (e) {
                    return false;
                }
            }
            startCallbacksExecuted = true;
            return true;
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
            let ready = pendingModules <= 0 && !moduleFailed && !examStarted && !examLocked && getMissingRequirements().length === 0;
            $startButton.prop("disabled", !ready || startInProgress);
            renderRequirementsDescription();
        }


        function showPolicyAlert(policyKey, html) {
            if (!examStarted || examLocked || !html) {
                return;
            }
            policyAlertQueue.push({policyKey: String(policyKey || ""), html: html});
            renderNextPolicyAlert();
        }

        function renderNextPolicyAlert() {
            if (activePolicyAlert || !policyAlertQueue.length || examLocked) {
                return;
            }
            $messages = getMessagesContainer();
            if (!$messages.length) {
                return;
            }
            activePolicyAlert = policyAlertQueue.shift();
            previousViolationFocus = previousViolationFocus || document.activeElement;
            $messages.find("[data-kopere-proctoring-violation]")
                .attr("data-kopere-violation-key", activePolicyAlert.policyKey);
            $messages.find("[data-kopere-proctoring-violation-content]")
                .html(activePolicyAlert.html);
            $messages.css("display", "flex");
            $dismissViolationButton.trigger("focus");
        }

        /**
         * Dismiss the current alert, showing the next policy warning if queued.
         * Legacy subplugins can continue to call hideViolationMessage.
         */
        function hidePolicyAlert(discardQueue) {
            if (discardQueue) {
                policyAlertQueue = [];
            }
            $messages = getMessagesContainer();
            if (!$messages.length) {
                activePolicyAlert = null;
                return;
            }
            let visible = $messages.is(":visible");
            $messages.find("[data-kopere-proctoring-violation]")
                .removeAttr("data-kopere-violation-key");
            $messages.find("[data-kopere-proctoring-violation-content]").empty();
            $messages.hide();
            activePolicyAlert = null;
            if (policyAlertQueue.length && !discardQueue) {
                renderNextPolicyAlert();
                return;
            }
            if (visible && previousViolationFocus && document.contains(previousViolationFocus)) {
                $(previousViolationFocus).trigger("focus");
            }
            previousViolationFocus = null;
        }

                function startExam() {
            releaseVisibilityGuard();

            examStarted = true;
            examLocked = false;
            startInProgress = false;
            syncContainerState();
            hidePolicyAlert(true);
            if (!runStartCallbacks()) {
                lockExam("policy", M.util.get_string("policy_load_failed", "local_kopere_proctoring"));
                return;
            }

            if ($startBlock.length) {
                $startBlock.hide();
            }
            $resumeBlock.hide();
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
            hidePolicyAlert(true);
            examLocked = true;
            startInProgress = false;
            syncContainerState();
            $startButton.prop("disabled", true);

            if ($lockedMessage.length) {
                $lockedMessage.html(html || M.util.get_string("locked_default_message", "local_kopere_proctoring"));
            }

            $container.show();
            $resumeBlock.hide();

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
            isNavigating: function () {
                return navigating;
            },
            isFullscreenMonitored: fullscreenRequired,
            noteFullscreenExit: violationCorrelation.noteFullscreenExit,
            clearFullscreenExit: violationCorrelation.clearFullscreenExit,
            consumeRelatedFocusBlur: violationCorrelation.consumeFocusBlur,
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
                return pendingModules <= 0 && !moduleFailed && !examStarted && !examLocked && getMissingRequirements().length === 0;
            },
            isExamStarted: function () {
                return examStarted;
            },
            isExamActive: function () {
                return examStarted && !examLocked;
            },
            startExam: startExam,
            showPolicyAlert: showPolicyAlert,
            hidePolicyAlert: hidePolicyAlert,
            showViolationMessage: showPolicyAlert,
            hideViolationMessage: hidePolicyAlert,
            lockExam: lockExam,
            recordViolation: function (policy, kind) {
                // Each logical incident gets one ID, allowing safe retries at the server.
                let eventid = "k" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 12);
                return Ajax.call([{
                    methodname: "local_kopere_proctoring_record_violation",
                    args: {cmid: cmid, attemptid: attemptid, policy: policy, kind: kind, eventid: eventid}
                }])[0];
            }
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


        // Already approved attempts resume policy monitors on every document load.
        // Browsers exit fullscreen on navigation; reenter with a user gesture before releasing questions.
        function resumeMonitoring() {
            if (!accessgranted || examStarted || examLocked || pendingModules > 0) {
                return;
            }
            if (moduleFailed) {
                lockExam("policy", M.util.get_string("policy_load_failed", "local_kopere_proctoring"));
                return;
            }
            if (fullscreenRequired() && !fullscreenActive()) {
                $resumeBlock.show();
                $container.show();
                return;
            }
            startExam();
        }

        $resumeButton.on("click.local_kopere_proctoring_resume", function (e) {
            e.preventDefault();
            if (pendingModules > 0 || moduleFailed || examLocked) {
                return;
            }
            $resumeError.hide();
            if (!fullscreenRequired() || fullscreenActive()) {
                resumeMonitoring();
                return;
            }
            let element = document.documentElement;
            let enter = element.requestFullscreen || element.webkitRequestFullscreen ||
                element.mozRequestFullScreen || element.msRequestFullscreen;
            if (typeof enter !== "function") {
                $resumeError.show();
                return;
            }
            $resumeButton.prop("disabled", true);
            try {
                let result = enter.call(element);
                Promise.resolve(result).then(function () {
                    $resumeButton.prop("disabled", false);
                    resumeMonitoring();
                    if (!examStarted) {
                        $resumeError.show();
                    }
                }, function () {
                    $resumeButton.prop("disabled", false);
                    $resumeError.show();
                });
            } catch (error) {
                $resumeButton.prop("disabled", false);
                $resumeError.show();
            }
        });
        $(document).on("fullscreenchange.kopere_proctoring_resume " +
            "webkitfullscreenchange.kopere_proctoring_resume " +
            "mozfullscreenchange.kopere_proctoring_resume " +
            "MSFullscreenChange.kopere_proctoring_resume", resumeMonitoring);

        $dismissViolationButton.on("click.local_kopere_proctoring", function (e) {
            e.preventDefault();
            hidePolicyAlert(false);
        });

        $messages = getMessagesContainer();
        $messages.on("keydown.local_kopere_proctoring", function (e) {
            if (e.key === "Tab") {
                e.preventDefault();
                $dismissViolationButton.trigger("focus");
            }
        });

        $returnButton.on("click.local_kopere_proctoring", function (e) {
            e.preventDefault();
            // Returning to the quiz landing page does not clear an exceeded limit in this page.
            if (examLocked && cmid > 0) {
                window.location.assign(M.cfg.wwwroot + "/mod/quiz/view.php?id=" + encodeURIComponent(cmid));
            }
        });

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

                $startError.hide();
                Ajax.call([{
                    methodname: "local_kopere_proctoring_grant_access",
                    args: {cmid: cmid, attemptid: attemptid}
                }])[0].done(function (result) {
                    if (result && result.approved) {
                        accessgranted = true;
                        context.api.startExam();
                        return;
                    }
                    startInProgress = false;
                    $startError.show();
                    refreshStartState();
                }).fail(function () {
                    startInProgress = false;
                    $startError.show();
                    refreshStartState();
                });
            }).fail(function () {
                startInProgress = false;
                refreshStartState();
            });
        });

        function markModuleLoaded(ok, key) {
            if (!ok) {
                moduleFailed = true;
                requirements["policy_load_" + key] = {
                    label: M.util.get_string("policy_load_failed", "local_kopere_proctoring"),
                    satisfied: false
                };
            }
            pendingModules = pendingModules - 1;
            refreshStartState();
            resumeMonitoring();
        }

        syncContainerState();
        startVisibilityGuard();

        if (!policies || policies.length === 0) {
            refreshStartState();
            resumeMonitoring();
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

            if (!p || !p.amd) {
                markModuleLoaded(false, p && p.key || "unknown");
                return;
            }

            requirejs([p.amd], function (mod) {
                try {
                    if (!mod || typeof mod.init !== "function") {
                        throw new Error("Policy module has no init method");
                    }
                    mod.init(context, p.config || {});
                    markModuleLoaded(true, p.key);
                } catch (e) {
                    markModuleLoaded(false, p.key);
                }
            }, function () {
                markModuleLoaded(false, p.key);
            });
        });

        if (!accessgranted) {
            initPolicyAcknowledgements();
        }
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
