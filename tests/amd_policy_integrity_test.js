// Lightweight AMD policy regression tests; no browser, server or database required.
"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");

function read(relative) {
    return fs.readFileSync(path.join(root, relative), "utf8");
}

function testPolicy(relative, policy) {
    const source = read(relative);
    assert.equal(source, read(relative.replace("/src/", "/build/").replace("/policy.js", "/policy.min.js")));
    const win = {};
    const doc = {hidden: false, fullscreenElement: {}, hasFocus: () => true};
    const handlers = {};
    const empty = {
        length: 0,
        on() { return this; },
        off() { return this; },
        closest() { return this; },
        find() { return this; },
        attr() { return this; },
        is() { return false; },
        prop() { return this; },
        toggleClass() { return this; },
        html() { return "<p>Violation</p>"; },
        show() { return this; },
        hide() { return this; },
        empty() { return this; }
    };
    function jquery(selector) {
        if (selector === win || selector === doc) {
            return {
                ...empty,
                on(events, callback) {
                    const target = selector === win ? "window" : "document";
                    handlers[target] = handlers[target] || {};
                    events.split(" ").forEach(name => { handlers[target][name] = callback; });
                    return this;
                }
            };
        }
        if (selector === "#proctoringpolicy_" + policy + "-message") {
            return {...empty, length: 1};
        }
        return empty;
    }
    jquery.trim = value => value.trim();
    let module;
    const define = (dependencies, factory) => { module = factory(jquery); };
    const M = {util: {get_string: () => "message"}};
    new Function("define", "window", "document", "M", "updatePendingState", source)(
        define, win, doc, M, () => {}
    );
    const reports = [];
    const locks = [];
    const ctx = {
        api: {
            isExamActive: () => true,
            recordViolation(kind, action) {
                reports.push({kind, action});
                return {
                    done(callback) { callback({blocked: false}); return this; },
                    fail() { return this; }
                };
            },
            registerStartCallback: callback => callback(),
            registerRequirement: () => {},
            updateRequirement: () => {},
            registerGatekeeper: () => {},
            showViolationMessage: () => {}
        },
        lock(reason) { locks.push(reason); }
    };
    module.init(ctx, {limit: 2});
    if (policy === "focus") {
        handlers.window["blur.kopere_proctoring_focus"]();
        doc.hidden = true;
        handlers.document["visibilitychange.kopere_proctoring_focus"]();
        assert.equal(reports.length, 1, "blur and visibilitychange must count one incident");
        doc.hidden = false;
        handlers.window["focus.kopere_proctoring_focus"]();
        handlers.window["blur.kopere_proctoring_focus"]();
        assert.equal(reports.length, 2, "a second real departure must be counted");
    } else {
        const callback = handlers.document["fullscreenchange.kopere_proctoring_fullscreen"];
        assert.equal(typeof callback, "function");
        doc.fullscreenElement = null;
        callback();
        callback();
        assert.equal(reports.length, 1, "duplicated fullscreen events must count once");
    }
    assert.deepEqual(locks, []);
}

assert.equal(read("amd/src/start.js"), read("amd/build/start.min.js"));
assert.match(read("amd/src/start.js"), /!moduleFailed/);
assert.doesNotMatch(read("amd/src/start.js"), /\$\("body \*"\)\.css\(\{"user-select": "none"\}\)/);
testPolicy("policy/focus/amd/src/policy.js", "focus");
testPolicy("policy/fullscreen/amd/src/policy.js", "fullscreen");
console.log("Proctoring AMD regression tests passed.");
