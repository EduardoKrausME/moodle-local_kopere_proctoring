# MCP integration (optional)

Kopere Proctoring registers its MCP tools through `classes/mcp/provider.php`, using the
same convention as LearnLingo. When `local_mcp` is installed and this plugin is enabled,
the MCP extension manager automatically finds the provider. There is no dedicated endpoint,
`lib.php` callback, or hard dependency on `local_mcp`. Authentication and READ/WRITE
token scopes are enforced by `local_mcp`.

## Tools

| Tool | Scope | Moodle capability | Purpose |
| --- | --- | --- | --- |
| `kopere_proctoring_list_policies` | READ | `moodle/site:config` | Installed policy plugins, global status and order |
| `kopere_proctoring_get_quiz` | READ | `mod/quiz:viewreports` | Per-quiz enabled flags and concurrency state hash |
| `kopere_proctoring_list_attempts` | READ | `mod/quiz:viewreports` | Paginated monitored quiz attempts and event totals |
| `kopere_proctoring_get_attempt_logs` | READ | `mod/quiz:viewreports` | Paginated events for a monitored attempt |
| `kopere_proctoring_set_quiz_enabled` | WRITE | `mod/quiz:manage` | Enable/disable quiz proctoring, with dry-run and confirmation |

Use the quiz **course module ID** as `cmid` (not the ID in the `quiz` table).
For example, query `kopere_proctoring_get_quiz` with `{"cmid":123}`, then
query `kopere_proctoring_list_attempts` with `{"cmid":123,"limit":25}`.
A returned `attemptid` is the Moodle `quiz_attempts.id`; pass it with
`cmid` to `kopere_proctoring_get_attempt_logs`. Limits are capped at 100.

To enable monitoring, call `kopere_proctoring_set_quiz_enabled` with
`{"cmid":123,"enabled":true,"expected_hash":"<state_hash from get_quiz>"}`.
As in the existing quiz settings form, enabling the plugin sets the Moodle
quiz `questionsperpage` setting to 50. Disabling does not restore a previous
question pagination value and does not delete recorded events or policy settings.

Permission checks are made against the actual quiz module context, not just
the user's site-wide role. Events and attempts are always scoped to the quiz
specified by `cmid`. IP addresses, user agents and screen resolutions are
omitted from the log response unless `include_technical:true` is supplied.
Reports contain behavioral observations, **not proof of cheating**. Handle
these records according to institutional privacy and retention policies.

The provider is only loaded when requested by an installed `local_mcp`;
Kopere Proctoring continues to work normally without MCP.
