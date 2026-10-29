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
 * @package   proctoringpolicy_copy
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/ajax"], function ($, Ajax) {
    "use strict";

    // Handlers on the Moodle document cannot receive keyboard or clipboard events
    // fired inside the iframe used by TinyMCE's classic editor.
    let unbindPrevious = null;

    function isExamActive(context) {
        return !!(context && context.api &&
            typeof context.api.isExamActive === "function" && context.api.isExamActive());
    }

    function violationMessage() {
        let html = $.trim($("#proctoringpolicy_copy-message").html() || "");
        if (!html) {
            // Custom policy messages can be empty. Still show a usable warning.
            html = M.util.get_string("message_default_text", "proctoringpolicy_copy");
        }
        return html;
    }

    function bindPendingState() {
        let $checkbox = $('[data-kopere-policy-ack="copy"]');
        if (!$checkbox.length) {
            return;
        }

        let $footer = $checkbox.closest(".kopere-proctoring-footer");
        let $error = $footer.find("[data-kopere-policy-ack-error]");
        function refresh() {
            updatePendingState($footer, $checkbox, $error, $checkbox.is(":checked"));
        }
        refresh();
        $checkbox.off("change.kopere_proctoring_copy_pending")
            .on("change.kopere_proctoring_copy_pending", refresh);
    }

    function recordAction(context, action) {
        if (!context.cmid || !context.attemptid) {
            return;
        }

        // The warning itself must not depend on an AJAX round trip.
        try {
            let request = Ajax.call([{
                methodname: "local_kopere_proctoring_save_log",
                args: {
                    cmid: Number(context.cmid),
                    attemptid: Number(context.attemptid),
                    actionvalue: "copy_" + action,
                    screenresolution: "",
                    image: ""
                }
            }])[0];
            if (request && typeof request.catch === "function") {
                request.catch(function () {});
            } else if (request && typeof request.fail === "function") {
                request.fail(function () {});
            }
        } catch (error) {
            // Network failures must never allow the blocked clipboard operation.
        }
    }

    function showWarning(context) {
        if (isExamActive(context) && context.api &&
                typeof context.api.showPolicyAlert === "function") {
            context.api.showPolicyAlert("copy", violationMessage());
        }
    }

    function shortcutAction(event) {
        let key = String(event.key || "").toLowerCase();
        let code = String(event.code || "").toLowerCase();
        if (!key || key === "unidentified") {
            key = code.indexOf("key") === 0 ? code.slice(3) : code;
        }
        if (event.altKey || !(event.ctrlKey || event.metaKey)) {
            return "";
        }
        let actions = {c: "copy", v: "paste", x: "cut", a: "selectall", p: "print"};
        return actions[key] || "";
    }

    function initCopyPolicy(context) {
        if (unbindPrevious) {
            unbindPrevious();
        }
        let registered = new Map();
        let lastShortcut = {action: "", timestamp: 0, ownerDocument: null};
        let observer = null;
        let scanner = null;
        let tinyManager = null;
        let tinyHandler = null;
        let iframeLoads = new Map();

        function intercept(event, action, source) {
            if (!isExamActive(context)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === "function") {
                event.stopImmediatePropagation();
            }

            // A keydown can be followed by a copy/paste/cut event for the same
            // action. Count and warn once, but never drop another keydown.
            let owner = event.target && event.target.ownerDocument || document;
            if (source === "clipboard" && lastShortcut.action === action &&
                    lastShortcut.ownerDocument === owner &&
                    Date.now() - lastShortcut.timestamp < 400) {
                return;
            }
            if (source === "keydown") {
                lastShortcut = {action: action, timestamp: Date.now(), ownerDocument: owner};
                if (event.repeat) {
                    return;
                }
            }
            showWarning(context);
            recordAction(context, action);
        }

        function onKeydown(event) {
            let action = shortcutAction(event);
            if (action) {
                intercept(event, action, "keydown");
            }
        }

        function onClipboard(event) {
            intercept(event, event.type, "clipboard");
        }

        function onContextmenu(event) {
            intercept(event, "contextmenu", "contextmenu");
        }

        function addDocument(doc) {
            if (!doc || registered.has(doc) || typeof doc.addEventListener !== "function") {
                return;
            }
            let listeners = [
                ["keydown", onKeydown],
                ["copy", onClipboard],
                ["paste", onClipboard],
                ["cut", onClipboard],
                ["contextmenu", onContextmenu]
            ];
            listeners.forEach(function (pair) {
                doc.addEventListener(pair[0], pair[1], true);
            });
            registered.set(doc, listeners);
        }

        function bindIframe(iframe) {
            if (!iframe || !iframeLoads.has(iframe)) {
                // Attach a load listener for newly initialised / reloaded editors.
                let reload = function () { scanEditors(); };
                iframe.addEventListener("load", reload);
                iframeLoads.set(iframe, reload);
            }
            try {
                addDocument(iframe.contentDocument);
            } catch (error) {
                // Cross-origin iframes are intentionally not accessible.
            }
        }

        function scanEditors() {
            // TinyMCE may start after the proctoring policy becomes active, or
            // be recreated when question elements are updated by Moodle.
            if (typeof document.querySelectorAll === "function") {
                document.querySelectorAll(
                    ".tox-edit-area iframe, iframe.tox-edit-area__iframe, iframe[id$='_ifr']"
                ).forEach(bindIframe);
            }
            let tiny = window.tinymce || window.tinyMCE;
            if (tiny && Array.isArray(tiny.editors)) {
                tiny.editors.forEach(function (editor) {
                    if (!editor || typeof editor.getDoc !== "function") {
                        return;
                    }
                    try {
                        addDocument(editor.getDoc());
                    } catch (error) {
                        // Editor may not yet have finished initialising.
                    }
                });
            }
            if (tiny && tiny !== tinyManager && typeof tiny.on === "function") {
                if (tinyManager && tinyHandler && typeof tinyManager.off === "function") {
                    tinyManager.off("AddEditor", tinyHandler);
                }
                tinyManager = tiny;
                tinyHandler = function () { window.setTimeout(scanEditors, 0); };
                tinyManager.on("AddEditor", tinyHandler);
            }
        }

        addDocument(document);
        scanEditors();
        if (typeof MutationObserver !== "undefined" && document.body) {
            observer = new MutationObserver(scanEditors);
            observer.observe(document.body, {childList: true, subtree: true});
        }
        scanner = window.setInterval(scanEditors, 1500);

        unbindPrevious = function () {
            if (scanner !== null) {
                window.clearInterval(scanner);
            }
            if (observer) {
                observer.disconnect();
            }
            if (tinyManager && tinyHandler && typeof tinyManager.off === "function") {
                tinyManager.off("AddEditor", tinyHandler);
            }
            iframeLoads.forEach(function (listener, iframe) {
                iframe.removeEventListener("load", listener);
            });
            registered.forEach(function (listeners, doc) {
                listeners.forEach(function (pair) {
                    doc.removeEventListener(pair[0], pair[1], true);
                });
            });
            registered.clear();
            iframeLoads.clear();
        };
    }

    return {
        init: function (context) {
            context = context || {};
            bindPendingState();
            if (context.api && typeof context.api.registerStartCallback === "function") {
                context.api.registerStartCallback(function () { initCopyPolicy(context); });
                return;
            }
            initCopyPolicy(context);
        }
    };
});
