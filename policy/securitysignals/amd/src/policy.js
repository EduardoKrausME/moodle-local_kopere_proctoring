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
 * @package   proctoringpolicy_securitysignals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/ajax"], function ($, Ajax) {
    "use strict";

    function hashString(input) {
        let h = 0;
        for (let i = 0; i < input.length; i++) {
            h = (h * 31 + input.charCodeAt(i)) >>> 0;
        }
        return h.toString(16);
    }
    function buildBaseline() {
        let fetchSig = "";
        let timeoutSig = "";
        let addEvSig = "";
        try { fetchSig = String(window.fetch).slice(0, 80); } catch (e) { fetchSig = "no_fetch"; }
        try { timeoutSig = String(window.setTimeout).slice(0, 80); } catch (e2) { timeoutSig = "no_settimeout"; }
        try { addEvSig = String(document.addEventListener).slice(0, 80); } catch (e3) { addEvSig = "no_addev"; }
        return {fetchSig: fetchSig, timeoutSig: timeoutSig, addEvSig: addEvSig, userAgent: navigator.userAgent || ""};
    }
    function postLog(payload) {
        return Ajax.call([{methodname: "local_kopere_proctoring_save_log", args: payload}])[0];
    }
    function startPolicy(ctx, cfg) {
        let cmid = Number(ctx.cmid || 0);
        let attemptid = Number(ctx.attemptid || 0);
        let pulseMs = Number(cfg.pulsems || 8) * 1000;
        let baseline = buildBaseline();
        let clientToken = hashString([String(Date.now()), baseline.userAgent,
            String(screen.width) + "x" + String(screen.height)].join("|"));
        let lastSentAt = 0;

        function shouldSendNow() {
            let now = Date.now();
            if ((now - lastSentAt) < 1500) { return false; }
            lastSentAt = now;
            return true;
        }
        function checkIntegrity() {
            let current = buildBaseline();
            if (current.fetchSig !== baseline.fetchSig ||
                    current.timeoutSig !== baseline.timeoutSig ||
                    current.addEvSig !== baseline.addEvSig) {
                return {integrityok: 0, reason: "integrity_changed"};
            }
            return {integrityok: 1, reason: ""};
        }
        function pulse() {
            let integrity = checkIntegrity();
            if (integrity.integrityok === 1 || !shouldSendNow()) { return; }
            postLog({
                cmid: cmid,
                attemptid: attemptid,
                screenresolution: String(screen.width || 0) + "x" + String(screen.height || 0),
                actionvalue: JSON.stringify({
                    integrityok: integrity.integrityok,
                    integrityreason: integrity.reason,
                    token: clientToken
                }),
                image: ""
            });
            if (integrity.integrityok === 0) {
                console.warn(M.util.get_string("js_warn_integrity", "proctoringpolicy_securitysignals"));
            }
        }
        setInterval(pulse, pulseMs);
    }
    function init(ctx, cfg) {
        ctx = ctx || {};
        cfg = cfg || {};
        if (ctx.api && typeof ctx.api.registerStartCallback === "function") {
            ctx.api.registerStartCallback(function () { startPolicy(ctx, cfg); });
            return;
        }
        startPolicy(ctx, cfg);
    }
    return {init: init};
});
