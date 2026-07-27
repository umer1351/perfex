# Changelog

All notable changes to the Deals module after `1.0.3` are documented here.

## [1.1.2]
### Changed
- Updated the module release version to `1.1.2`.
- Removed unfinished integration and inbox-related items from the visible deals navigation.
- Removed remaining visible admin links that pointed users into hidden non-release areas.

### Fixed
- Replaced hard-stop helper failures with logged graceful failure handling for temp file and upload directory creation.
- Replaced raw `die()` JSON endpoint exits with normal controller responses.
- Removed leftover debug `console.log` statements from production views.
- Completed final release cleanup across navigation and runtime behavior.

## [1.1.1]
### Added
- Expanded connector configuration with:
  - HTTP method
  - payload format
  - auth type
  - auth header name
  - username and token fields
  - custom headers
  - timeout
  - retry limit
  - retry backoff
  - signature header and secret
  - richer dispatch policy controls

### Improved
- Redesigned connector setup pages into a denser enterprise-style setup workspace.
- Improved connector operational visibility with clearer health, policy, and setup states.

## [1.1.0]
### Fixed
- Added a recovery migration for enterprise deal columns that could be missed on partially upgraded installations.
- Backfilled missing `tbl_deals` enterprise columns such as `last_contacted_at`, `last_activity_at`, and `next_follow_up_at`.

### Improved
- Improved installer upgrade safety by adding enterprise deal columns independently instead of relying on a single gating field.

## [1.0.8]
### Added
- Added `tbl_deals_approval_policies` for rule-driven approval governance.
- Added automatic approval creation logic based on policy conditions.
- Added richer governance tooling for approval, webhook oversight, and operational attention handling.

### Improved
- Redesigned the governance, diagnostics, dashboard, and kanban/list shell for a more professional admin experience.
- Improved deal overview presentation and operational visibility.

## [1.0.7]
### Added
- Added `tbl_deals_approvals` for approval requests.
- Added `tbl_deals_webhooks` and webhook delivery logging.
- Added approval workflows, webhook registry, webhook retry/testing, and governance foundations.

### Improved
- Added support for approval trails and external event delivery foundations.

## [1.0.6]
### Added
- Added `tbl_deals_campaign_messages` for campaign delivery history.
- Added `tbl_deals_email_preferences` for unsubscribe handling and email preferences.
- Added email open tracking, click tracking, unsubscribe handling, and delivery metrics.
- Added public tracking endpoints for opens, clicks, and unsubscribes.

### Improved
- Added campaign delivery analytics and message history to the campaign management UI.

## [1.0.5]
### Added
- Added `tbl_deals_campaign_steps` for multi-step campaign sequencing.
- Added `tbl_deals_automation_rules` for trigger/action automation rules.
- Added automation queue processing and cron-driven execution.
- Added event-driven automation for deal changes, follow-up activity, and campaign execution.

### Improved
- Added dedicated automation management screens and campaign sequencing controls.
- Strengthened the module’s CRM workflow automation foundations.

## [1.0.4]
### Added
- Added enterprise deal fields:
  - `probability`
  - `expected_revenue`
  - `next_follow_up_at`
  - `last_contacted_at`
  - `last_activity_at`
  - `priority`
  - `forecast_category`
  - `health_status`
  - `campaign_id`
- Added `tbl_deals_followups` for structured follow-up management.

### Improved
- Improved deal metrics syncing for activity/contact timestamps.
- Improved deal forms to support probability, forecast, priority, health, campaign attribution, and next follow-up scheduling.
- Added stronger reporting surfaces and early enterprise CRM foundations.

### Fixed
- Fixed broken helper, route, relation, and attachment inconsistencies in the original deal module.
- Fixed deal detail stability issues caused by invalid assignee data and related SQL edge cases.
