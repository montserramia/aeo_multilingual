# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-02

### Initial release

First stable release of AEO Multilingual — a Drupal module to optimize multilingual sites for Answer Engine Optimization (AEO) and AI citations.

### Added

- **Per-language AEO scoring (0–100)** — each translation is scored independently across 7 audit checks.
- **Hreflang validation** — detects missing or incorrect hreflang tags across all language versions.
- **Schema markup audit** — verifies JSON-LD structured data includes the correct `inLanguage` property per translation.
- **Translation completeness check** — identifies untranslated fields affecting AEO performance.
- **Meta description check** — validates length (120–160 characters) per language.
- **Header hierarchy check** — ensures proper H1→H2→H3 structure per translation.
- **Image alt text check** — detects images missing alt text per language version.
- **Content length check** — flags translations below the recommended minimum word count.
- **Visual dashboard** at `/admin/reports/aeo-multilingual` — overview of all multilingual content with scores per language.
- **Node-level audit tab** — detailed checklist accessible directly from the node edit screen.
- **Settings page** at `/admin/config/search/aeo-multilingual` — configure content types, score threshold, and auto-fix mode.
- **Plugin-based architecture** — extensible `@AuditCheck` plugin system for custom audit checks.
- **Drush commands** — `aeo-ml:audit`, `aeo-ml:report`, `aeo-ml:audit-node`, `aeo-ml:clear-cache`.
- **Role-based permissions** — three granular permissions for administering, viewing, and running audits.
- **Drupal 10.2 and Drupal 11 compatibility**.
- **PHP 8.1+ support**.

### Dependencies

- Drupal core: `language`, `content_translation`, `node`

[1.0.0]: https://www.drupal.org/project/aeo_multilingual
