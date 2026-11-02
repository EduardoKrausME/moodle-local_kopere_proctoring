# Changelog

This changelog records only the two latest **published** releases of `local_kopere_proctoring`, verified on October 24, 2026.

**Git history note:** The published tags `4.0.14` and `4.0.15` have diverged histories: GitHub reports 11 commits on the `4.0.15` side and 3 on the `4.0.14` side. GitHub's three-dot compare link describes changes from the merge base, **not** an exact direct tree diff between these divergent tags. The `4.0.15` summary below was checked against the actual file trees of both release tags to reflect their net differences. This changelog does not include commits made after `4.0.15`.

## 4.0.18 - 2026-11-01

- Added contract tests that compare proctoring AMD source modules with their minified builds.
- Rebuilt policy AMD assets and normalized language-string ordering and PHP headers.

**Full comparison:** [4.0.17...4.0.18](https://github.com/EduardoKrausME/moodle-local_kopere_proctoring/compare/4.0.17...4.0.18)

## 4.0.17 - 2026-11-01

- Persisted focus and fullscreen monitoring incidents and attempt locks across quiz requests.
- Prevented locked attempts from reopening or submitting after persisted policy violations.
- Restricted proctoring checks to active quiz attempts and exposed persisted incident counts in audit reporting.
- Added regression coverage for attempt integrity, policy activation, AMD failures and attempt-state guards.

**Full comparison:** [4.0.16...4.0.17](https://github.com/EduardoKrausME/moodle-local_kopere_proctoring/compare/4.0.16...4.0.17)

## 4.0.16 - 2026-10-27

- Added MCP tools for reading, validating and explicitly confirming global proctoring policy settings and activation.
- Added quiz-specific policy settings, text inheritance and confirmed MCP editing with permission checks.
- Preserved MCP-managed quiz policy values when Moodle module forms omit the corresponding fields.
- Expanded policy administration, security-boundary and integration tests and documentation.

**Full comparison:** [4.0.15...4.0.16](https://github.com/EduardoKrausME/moodle-local_kopere_proctoring/compare/4.0.15...4.0.16)

## [4.0.15] - 2026-10-23

### Added
- Added an optional Moodle MCP promotional notice for site administrators when `local_mcp` is not installed, including a Mustache template, translated English/Brazilian Portuguese text, administration-settings integration and responsive styling.
- Added a per-user dismissal preference and `dismiss-mcp-banner.php` endpoint, with administrator authorization, POST-method and session-key checks.

### Changed
- Updated the `local_kopere_dashboard` dependency requirement in `version.php` from `2025030300` to `2026101900`.
- Replaced `core_text::strtoupper`, `core_text::strlen` and `core_text::substr` with native PHP string functions in the contract-policy service.
- Removed unused example-context comment blocks from the contract, copy-protection and fullscreen-policy Mustache templates.
- Updated the compiled `amd/build/start.min.js` JavaScript asset.
- Relative to the published `4.0.14` file tree, the PNG plugin icon is no longer present and the SVG icon is present again. This difference is not displayed as such by the divergent three-dot comparison.
- Updated the plugin version metadata to `2026102300` and release label to `4.0.15`.

**Published-tag comparison:** [4.0.14...4.0.15](https://github.com/EduardoKrausME/moodle-local_kopere_proctoring/compare/4.0.14...4.0.15) (diverged history; see note above).

## [4.0.14] - 2026-10-15

### Assets
- Replaced the SVG plugin icon at `pix/icon.svg` with a PNG asset at `pix/icon.png`.

### Release metadata
- Updated the plugin version metadata from `2026101500` to `2026101501` and the release label from `4.0.13` to `4.0.14`.
- No other files changed in the published-tag comparison.

**Full comparison:** [4.0.13...4.0.14](https://github.com/EduardoKrausME/moodle-local_kopere_proctoring/compare/4.0.13...4.0.14)
