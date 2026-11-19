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

// Execute the real policy AMD modules against a deterministic browser clock.
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = p => fs.readFileSync(path.join(root, p), "utf8");

function makeHarness(variant, options = {}) {
    const start = read("amd/" + variant + "/start" + (variant === "build" ? ".min" : "") + ".js");
    const begin = start.indexOf("    function createViolationCorrelation() {");
    const end = start.indexOf("    function init(payload)", begin);
    assert.ok(begin >= 0 && end > begin, "The shared correlation state must be available");
    const clock = {now: 1000};
    const fakeDate = {now: () => clock.now};
    const create = new Function("Date", start.slice(begin, end) + "\nreturn createViolationCorrelation;")(fakeDate);
    const correlation = create();

    const handlers = {window: {}, document: {}};
    const timers = new Map();
    let timerid = 0;
    let focused = true;
    let navigating = false;
    const window = {
        setTimeout(fn, delay) {
            const id = ++timerid;
            timers.set(id, {fn, at: clock.now + delay});
            return id;
        },
        clearTimeout(id) { timers.delete(id); }
    };
    const document = {
        fullscreenElement: {},
        hidden: false,
        documentElement: {},
        hasFocus: () => focused
    };
    const empty = {
        length: 0,
        on() { return this; },
        off() { return this; },
        find() { return this; },
        closest() { return this; },
        html() { return "<p>Reported policy violation</p>"; },
        empty() { return this; },
        show() { return this; },
        hide() { return this; },
        attr() { return this; },
        toggleClass() { return this; },
        trigger() { return this; },
        is() { return false; }
    };
    function jquery(element) {
        if (element === window || element === document) {
            const target = element === window ? "window" : "document";
            return {
                ...empty,
                on(events, callback) {
                    events.trim().split(/\s+/).forEach(name => {
                        handlers[target][name] = callback;
                    });
                    return this;
                },
                off(namespace) {
                    for (const key of Object.keys(handlers[target])) {
                        if (key.endsWith(namespace)) {
                            delete handlers[target][key];
                        }
                    }
                    return this;
                }
            };
        }
        return empty;
    }
    jquery.trim = value => value.trim();

    const reports = [];
    const locks = [];
    const startCallbacks = [];
    const hasFullscreenPolicy = options.fullscreen !== false;
    const hasFocusPolicy = options.focus !== false;
    const ctx = {
        cmid: 17,
        attemptid: 42,
        api: {
            isExamActive: () => true,
            isNavigating: () => navigating,
            isFullscreenMonitored: () => hasFullscreenPolicy,
            noteFullscreenExit: correlation.noteFullscreenExit,
            clearFullscreenExit: correlation.clearFullscreenExit,
            consumeRelatedFocusBlur: correlation.consumeFocusBlur,
            registerStartCallback(fn) { startCallbacks.push(fn); },
            registerRequirement() {},
            updateRequirement() {},
            registerGatekeeper() {},
            showPolicyAlert() {},
            hidePolicyAlert() {},
            recordViolation(policy, kind) {
                reports.push(policy + ":" + kind);
                return {
                    done(fn) { fn({blocked: false}); return this; },
                    fail() { return this; }
                };
            }
        },
        lock(reason) { locks.push(reason); }
    };
    function load(policy) {
        const filename = "policy/" + policy + "/amd/" + variant + "/policy" +
            (variant === "build" ? ".min" : "") + ".js";
        let module;
        const define = (dependencies, callback) => { module = callback(jquery); };
        const M = {util: {get_string: () => "Monitoring error"}};
        new Function("define", "window", "document", "M", "updatePendingState", "Date",
            read(filename))(define, window, document, M, () => {}, fakeDate);
        assert.ok(module && typeof module.init === "function");
        module.init(ctx, {limit: 3});
    }
    if (hasFullscreenPolicy) {
        load("fullscreen");
    }
    if (hasFocusPolicy) {
        load("focus");
    }
    startCallbacks.forEach(fn => fn(ctx));

    function fire(target, name) {
        const handler = handlers[target][name];
        assert.equal(typeof handler, "function", "Expected " + target + "/" + name);
        handler();
    }
    function advance(ms) {
        const until = clock.now + ms;
        for (;;) {
            const due = [...timers.entries()].filter(([, t]) => t.at <= until)
                .sort((a, b) => a[1].at - b[1].at)[0];
            if (!due) {
                break;
            }
            clock.now = due[1].at;
            timers.delete(due[0]);
            due[1].fn();
        }
        clock.now = until;
    }

    return {
        reports, locks, document,
        advance,
        blur() { fire("window", "blur.kopere_proctoring_focus"); },
        focus() {
            focused = true;
            fire("window", "focus.kopere_proctoring_focus");
        },
        fullscreen(active) {
            document.fullscreenElement = active ? {} : null;
            fire("document", "fullscreenchange.kopere_proctoring_fullscreen");
        },
        vendorFullscreenChange() {
            fire("document", "webkitfullscreenchange.kopere_proctoring_fullscreen");
        },
        hideTab() {
            document.hidden = true;
            focused = false;
            fire("document", "visibilitychange.kopere_proctoring_focus");
        },
        showTab() {
            document.hidden = false;
            focused = true;
            fire("document", "visibilitychange.kopere_proctoring_focus");
        },
        navigate(enabled) { navigating = enabled; }
    };
}

for (const variant of ["src", "build"]) {
    const escBlurFirst = makeHarness(variant);
    escBlurFirst.blur();
    assert.deepEqual(escBlurFirst.reports, [], "Blur must wait for fullscreenchange");
    escBlurFirst.advance(40);
    escBlurFirst.fullscreen(false);
    escBlurFirst.vendorFullscreenChange();
    escBlurFirst.advance(410);
    assert.deepEqual(escBlurFirst.reports, ["fullscreen:exit"],
        "Esc blur before fullscreenchange must not consume the focus limit");

    const escFullscreenFirst = makeHarness(variant);
    escFullscreenFirst.fullscreen(false);
    escFullscreenFirst.advance(40);
    escFullscreenFirst.blur();
    escFullscreenFirst.advance(410);
    assert.deepEqual(escFullscreenFirst.reports, ["fullscreen:exit"],
        "Esc blur after fullscreenchange must not consume the focus limit");

    const noExit = makeHarness(variant);
    noExit.blur();
    noExit.advance(410);
    assert.deepEqual(noExit.reports, ["focus:blur"],
        "Without an actual fullscreen exit, blur is independently counted");

    const hidden = makeHarness(variant);
    hidden.blur();
    hidden.hideTab();
    hidden.advance(410);
    assert.deepEqual(hidden.reports, ["focus:visibilitychange"],
        "Blur followed by tab hiding counts one real focus departure");
    hidden.showTab();
    hidden.blur();
    hidden.advance(410);
    assert.deepEqual(hidden.reports, ["focus:visibilitychange", "focus:blur"],
        "A later separate departure is independently counted");

    const hiddenAfterExit = makeHarness(variant);
    hiddenAfterExit.fullscreen(false);
    hiddenAfterExit.blur();
    hiddenAfterExit.hideTab();
    hiddenAfterExit.advance(410);
    assert.deepEqual(hiddenAfterExit.reports, ["fullscreen:exit", "focus:visibilitychange"],
        "Actual hidden tab evidence is not suppressed by an earlier fullscreen exit");

    const lateBlur = makeHarness(variant);
    lateBlur.fullscreen(false);
    lateBlur.advance(500);
    lateBlur.blur();
    lateBlur.advance(410);
    assert.deepEqual(lateBlur.reports, ["fullscreen:exit", "focus:blur"],
        "An independent blur outside the correlation interval must be counted");

    const separateBlurs = makeHarness(variant);
    separateBlurs.fullscreen(false);
    separateBlurs.blur();
    separateBlurs.advance(100);
    separateBlurs.focus();
    separateBlurs.blur();
    separateBlurs.advance(410);
    assert.deepEqual(separateBlurs.reports, ["fullscreen:exit", "focus:blur"],
        "Only one nearby blur may correlate with one fullscreen exit");

    const resetOnReentry = makeHarness(variant);
    resetOnReentry.fullscreen(false);
    resetOnReentry.advance(30);
    resetOnReentry.fullscreen(true);
    resetOnReentry.blur();
    resetOnReentry.advance(410);
    assert.deepEqual(resetOnReentry.reports, ["fullscreen:exit", "focus:blur"],
        "Reentering fullscreen must invalidate an earlier exit marker");

    const navigation = makeHarness(variant);
    navigation.navigate(true);
    navigation.blur();
    navigation.fullscreen(false);
    navigation.advance(410);
    assert.deepEqual(navigation.reports, [], "Normal quiz navigation must remain exempt");
    navigation.navigate(false);

    const focusOnly = makeHarness(variant, {fullscreen: false});
    focusOnly.blur();
    assert.deepEqual(focusOnly.reports, ["focus:blur"],
        "Without a fullscreen policy, focus detection should report immediately");

    const fullscreenOnly = makeHarness(variant, {focus: false});
    fullscreenOnly.fullscreen(false);
    assert.deepEqual(fullscreenOnly.reports, ["fullscreen:exit"],
        "Fullscreen-only attempts still count a fullscreen exit");
    assert.deepEqual(fullscreenOnly.locks, [], "No service failures expected");
}
assert.equal(read("amd/src/start.js"), read("amd/build/start.min.js"));
assert.equal(read("policy/focus/amd/src/policy.js"), read("policy/focus/amd/build/policy.min.js"));
assert.equal(read("policy/fullscreen/amd/src/policy.js"), read("policy/fullscreen/amd/build/policy.min.js"));
console.log("Fullscreen/focus ordering, independent incidents and evidence regressions passed.");
