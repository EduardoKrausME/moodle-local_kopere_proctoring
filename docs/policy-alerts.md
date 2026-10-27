# Global policy alerts

The parent plugin owns all runtime policy dialogs. Subplugins must never inject a second popup or append banners to the quiz page. Use the shared context supplied to the AMD `init` method:

```javascript
context.api.showPolicyAlert("copy", "<p>Copy/paste is not permitted.</p>");
```

The first argument is the policy key, used for diagnosis; the second is the formatted policy message. Messages can include the HTML configured by the Moodle administrator, but must not include untrusted raw user input. The parent plugin owns display, focus management, dismiss button, screen positioning and queuing simultaneous warnings. The deprecated `showViolationMessage` and `hideViolationMessage` methods remain aliases for existing external subplugins.

Warnings are only presented while a quiz attempt is active. Requirements and readiness feedback, such as entering fullscreen or accepting a contract **before** the quiz starts, belong in the policy card. A permanent attempt lock belongs to the parent's `context.lock(key, message)` flow, not an independently rendered popup. The parent plugin keeps a separate emergency-reload dialog if the initial proctoring interface is removed or hidden; it is a recovery mechanism, not a policy warning.

The four runtime-warning policies covered by regression tests are `copy`, `focus`, `fullscreen` and `securitysignals`. `contract` is a prestart gatekeeper; `notifications` is server-side. External `proctoringpolicy_authorship` currently doesn't create runtime alerts; it can call the same global API if needed.

Run `node tests/global_alerts_test.js` to validate that every runtime policy uses the parent API and that concurrent alerts are delivered in order.
