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

define(["jquery"], function ($) {
        "use strict";

        function getSesskey() {
            if (window.M && window.M.cfg && window.M.cfg.sesskey) {
                return window.M.cfg.sesskey;
            }
            return "";
        }

        function getString(key, component, fallback) {
            try {
                if (window.M && window.M.util && window.M.util.get_string) {
                    return window.M.util.get_string(key, component);
                }
            } catch (e) {
                // ignore
            }
            return fallback || "";
        }

        function hashString(input) {
            // Lightweight non-crypto hash (deterministic).
            let h = 0;
            let i;
            for (i = 0; i < input.length; i++) {
                h = (h * 31 + input.charCodeAt(i)) >>> 0;
            } 
            return h.toString(16);
        }

        function buildBaseline() {
            let fetchSig = "";
            let timeoutSig = "";
            let addEvSig = "";

            try {
                fetchSig = String(window.fetch).slice(0, 80);
            } catch (e) {
                fetchSig = "no_fetch";
            }

            try {
                timeoutSig = String(window.setTimeout).slice(0, 80);
            } catch (e2) {
                timeoutSig = "no_settimeout";
            }

            try {
                addEvSig = String(document.addEventListener).slice(0, 80);
            } catch (e3) {
                addEvSig = "no_addev";
            }

            return {
                fetchSig: fetchSig,
                timeoutSig: timeoutSig,
                addEvSig: addEvSig,
                userAgent: navigator.userAgent || "",
            };
        }

        function postLog(url, payload) {
            if (!url) {
                return;
            }

            $.ajax({
                url: url,
                method: "POST",
                data: JSON.stringify(payload),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                xhrFields: {
                    withCredentials: true
                }
            });
        }

        function init(ctx, cfg) {
            let component = "proctoringpolicy_securitysignals";

            let cmid = Number(ctx.cmid || 0);
            let attemptid = Number(ctx.attemptid || 0);

            let pulseMs = Number(cfg.pulsems || 8) * 1000;

            let baseline = buildBaseline();
            let clientToken = hashString([
                String(Date.now()),
                baseline.userAgent,
                String(screen.width) + "x" + String(screen.height)
            ].join("|"));

            let lastSentAt = 0;

            function shouldSendNow() {
                let now = Date.now();
                if ((now - lastSentAt) < 1500) {
                    return false;
                }
                lastSentAt = now;
                return true;
            }

            function checkIntegrity() {
                let current = buildBaseline();
                if (
                    current.fetchSig !== baseline.fetchSig ||
                    current.timeoutSig !== baseline.timeoutSig ||
                    current.addEvSig !== baseline.addEvSig
                ) {
                    return {
                        integrityok: 0,
                        reason: "integrity_changed"
                    };
                }
                return {
                    integrityok: 1,
                    reason: ""
                };
            }

            function pulse() {
                let integrity = checkIntegrity();

                // Only report when something is suspicious.
                if (integrity.integrityok === 1) {
                    return;
                }

                if (!shouldSendNow()) {
                    return;
                }

                let payload = {
                    cmid: cmid,
                    attemptid: attemptid,
                    action: "securitysignals_pulse",
                    actionvalue: JSON.stringify({
                        integrityok: integrity.integrityok,
                        integrityreason: integrity.reason,
                        token: clientToken
                    }),
                    sesskey: getSesskey(),
                    ts: Date.now()
                };

                postLog(`${M.cfg.wwwroot}/local/kopere_proctoring/save-image.php`, payload);

                // Optional UI hint (lightweight).
                if (integrity.integrityok === 0) {
                    // Avoid blocking UI here; other policies can decide to lock.
                    // This is just a hint.
                    // eslint-disable-next-line no-console
                    console.warn(getString("js_warn_integrity", component, "Security integrity changed."));
                }
            }

            // Start loop.
            setInterval(pulse, pulseMs);
        }

        return {
            init: init
        };
    }
);
