# AEO Multilingual

![Drupal 10](https://img.shields.io/badge/Drupal-10-blue)
![Drupal 11](https://img.shields.io/badge/Drupal-11-blue)
![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-GPL--2.0-green)

**Optimize your multilingual Drupal site for Answer Engine Optimization (AEO) and AI citations.**

Most AEO modules treat multilingual content as an afterthought. **AEO Multilingual** gives each language version independent AEO scoring and recommendations, ensuring AI systems can properly cite your content in the right language.

---

## 🎯 What This Module Does

This module solves a critical gap: existing AEO tools don't account for multilingual sites. Each translation of your content needs independent optimization for answer engines like Google AI Overviews, Perplexity, and ChatGPT.

### Key Features

- ✅ **Per-language AEO audit** - Each translation gets scored independently (0-100)
- ✅ **Hreflang validation** - Detect and fix missing hreflang tags automatically
- ✅ **Schema markup per language** - Ensure proper JSON-LD with correct `inLanguage` property
- ✅ **Visual dashboard** - See AEO scores across all languages at a glance
- ✅ **Translation completeness** - Identify untranslated content affecting AEO
- ✅ **Node-level checklist** - Detailed recommendations for each language version
- ✅ **Auto-fix capabilities** - Safely generate missing tags (drafts only)

---

## 📦 Requirements

### Core Dependencies
- Drupal 10.2+ or Drupal 11.x
- PHP 8.1+
- Core modules: `language`, `content_translation`, `node`

### Recommended Modules
- [`metatag`](https://www.drupal.org/project/metatag) - For meta tag management
- [`schema_metatag`](https://www.drupal.org/project/schema_metatag) - For JSON-LD schema
- [`pathauto`](https://www.drupal.org/project/pathauto) - For clean URL aliases
- [`hreflang`](https://www.drupal.org/project/hreflang) - Optional (module can generate hreflang directly)

---

## 🚀 Installation

### Via Composer (Recommended)

```bash
composer require drupal/aeo_multilingual
drush en aeo_multilingual -y
```

### Manual Installation

1. Download the module from [Drupal.org](https://www.drupal.org/project/aeo_multilingual)
2. Extract to `modules/contrib/aeo_multilingual` (or `modules/custom/` for development)
3. Enable via Drupal admin or Drush:
   ```bash
   drush en aeo_multilingual -y
   ```

### Post-Installation

1. Navigate to **Configuration → Search → AEO Multilingual** (`/admin/config/search/aeo-multilingual`)
2. Select content types to audit
3. Configure AEO score thresholds (default: 70)
4. Run your first audit

---

## ⚙️ Configuration

### Settings Page

**Path:** `/admin/config/search/aeo-multilingual`

| Setting | Description | Default |
|---------|-------------|---------|
| Enabled content types | Select which node types to audit | All types |
| Minimum score threshold | Nodes below this score are highlighted | 70 |
| Auto-fix mode | Enable automatic fixes (drafts only) | Disabled |

### Permissions

| Permission | Description |
|------------|-------------|
| `Administer AEO Multilingual settings` | Access configuration page |
| `View AEO Multilingual reports` | Access dashboard and node audits |
| `Run AEO Multilingual audits` | Trigger audits manually |

---

## 📊 Usage

### Dashboard

**Path:** `/admin/reports/aeo-multilingual`

View all multilingual content with AEO scores per language:

| Node | ES | CA | EN | Status |
|------|----|----|----|--------|
| Article 1 | 85 | 78 | ❌ | Translate EN |
| Article 2 | 92 | 90 | 88 | ✅ Optimal |
| Article 3 | 65 | ❌ | ❌ | Improve |

**Features:**
- Filter by content type, language, or score
- Export to CSV/PDF
- Identify translation gaps
- Compare scores across languages

### Per-Node Audit

**Path:** Edit any node → **AEO Multilingual** tab

For each language, see a detailed checklist:

```
🇪🇸 Español (85/100)
✅ Content: 2,500 words (optimal)
✅ Schema: Article JSON-LD present
⚠️  Hreflang: missing link to EN
❌ Meta description: too short (80 chars, min 120)
✅ Headers: correct hierarchy
✅ Images: 5/5 with alt text

Actions:
[Improve Meta Description] [Add Hreflang EN]
```

### Audit Checks

Each language is evaluated on:

| Check | Weight | Description |
|-------|--------|-------------|
| Content Length | High | Minimum 1000 words recommended |
| Schema Markup | High | Valid JSON-LD with `inLanguage` |
| Hreflang Tags | High | All translations linked correctly |
| Meta Description | Medium | 120-160 characters |
| Header Hierarchy | Medium | Proper H1→H2→H3 structure |
| Image Alt Text | Medium | All images have alt text |
| URL Alias | Low | Clean, translated URL |
| Translation Completeness | High | No untranslated fields |

### Scoring System

- **90-100:** Excellent - Ready for AEO
- **70-89:** Good - Minor improvements needed
- **50-69:** Fair - Significant improvements recommended
- **0-49:** Poor - Critical issues to address

---

## 🛠️ Drush Commands

```bash
# Audit all multilingual content
drush aeo-ml:audit

# Generate CSV report
drush aeo-ml:report

# Audit specific node
drush aeo-ml:audit-node 123

# Clear audit cache
drush aeo-ml:clear-cache
```

---

## 🔧 Developer Information

### Plugin System

Audit checks are implemented as plugins, making it easy to extend:

```php
<?php

namespace Drupal\aeo_multilingual\Plugin\AuditCheck;

/**
 * Checks content length for AEO optimization.
 *
 * @AuditCheck(
 *   id = "content_length",
 *   label = @Translation("Content Length"),
 *   description = @Translation("Verifies content meets minimum word count for AEO."),
 *   weight = 1
 * )
 */
class ContentLengthCheck extends AuditCheckBase {

  public function audit(NodeInterface $node, string $langcode): array {
    // Your audit logic here
    return [
      'score' => 85,
      'message' => $this->t('Good: @count words', ['@count' => $word_count]),
      'status' => 'pass',
    ];
  }

}
```

### Custom Audit Checks

To add your own audit check:

1. Create a class in `src/Plugin/AuditCheck/YourCheck.php`
2. Extend `AuditCheckBase`
3. Implement the `audit()` method
4. Add the `@AuditCheck` annotation

### Services

```yaml
# aeo_multilingual.services.yml
services:
  aeo_multilingual.audit:
    class: Drupal\aeo_multilingual\Service\AuditService
    arguments: ['@language_manager']
```

### Theming

Override templates in your theme:

```
templates/
├── aeo-multilingual-dashboard.html.twig
├── aeo-multilingual-node-tab.html.twig
└── aeo-multilingual-report.html.twig
```

---

## 🗺️ Roadmap

### Version 1.0 (Current)
- ✅ Core audit features
- ✅ Per-language scoring
- ✅ Dashboard and reporting
- ✅ Node-level checklist

### Version 1.1 (May 2026)
- [ ] Auto-fix capabilities
- [ ] Hreflang auto-generation
- [ ] Meta description suggestions
- [ ] Safe mode (drafts only)

### Version 2.0 (Q3 2026)
- [ ] AI-powered suggestions via `drupal/ai`
- [ ] Semantic analysis
- [ ] Translation improvement suggestions
- [ ] Integration with AI tools

### Version 2.1 (Q4 2026)
- [ ] TMGMT integration
- [ ] Subdomain distribution support
- [ ] REST API for external integration
- [ ] Continuous monitoring with Drush

---

## 🧪 Testing

### Run Tests

```bash
# PHPUnit tests
vendor/bin/phpunit modules/contrib/aeo_multilingual/tests

# Functional tests
drush test-run aeo_multilingual
```

### Manual Testing Setup

The module is designed to work with [DDEV](https://ddev.com):

```bash
# Create test site
ddev config --project-type=drupal10
ddev start
ddev composer install

# Enable module
ddev drush en aeo_multilingual -y

# Add test languages
ddev drush language:add ca
ddev drush language:add es
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

### How to Contribute

1. **Fork** the repository
2. **Create a feature branch** (`git checkout -b feature/your-feature`)
3. **Write tests** for new functionality
4. **Follow Drupal coding standards** (PHPCS will be run on PRs)
5. **Submit a pull request**

### Issue Priority

- 🔴 **Critical** - Security issues, data loss
- 🟠 **Major** - Core functionality broken
- 🟡 **Normal** - Feature requests, minor bugs
- 🟢 **Minor** - Cosmetic issues, documentation

### Reporting Issues

Please include:
- Drupal version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

[Report an issue](https://www.drupal.org/project/issues/aeo_multilingual)

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later** (GPL-2.0-or-later).

See [LICENSE.txt](LICENSE.txt) for the full license text.

---

## 👤 Maintainer

**Montse Ramia** ([@montserramia](https://www.drupal.org/u/montserramia))

- CEO at [newWweb](https://newwweb.net)
- Founder at [AURA IA](https://aura-ia.eu)
- Drupal contributor since 2011
- Specialized in multilingual sites and AI integration

### Support

- **Issues:** [Drupal.org issue queue](https://www.drupal.org/project/issues/aeo_multilingual)
- **Source:** [GitLab repository](https://git.drupalcode.org/project/aeo_multilingual)
- **Documentation:** [Drupal.org project page](https://www.drupal.org/project/aeo_multilingual)

---

## 🙏 Credits

This module was created to fill the gap in AEO optimization for multilingual Drupal sites, inspired by real-world needs at:

- **newWweb** - Professional Drupal development agency
- **AURA IA** - AI-powered content optimization

Special thanks to the Drupal AI Initiative and the Multilingual community for their support and feedback.

---

## 📈 Badges

![Drupal 10](https://img.shields.io/badge/Drupal-10-blue)
![Drupal 11](https://img.shields.io/badge/Drupal-11-blue)
![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-GPL--2.0-green)

> **Note:** The first stable release is `1.0.0`. The `1.0.x-dev` branch was used for initial development and is not recommended for production. Always use a tagged stable release for live sites.

---

## 🆕 What’s new in 1.1

- **Improved content length audit:** Now counts all text fields (title, body, summary, custom fields…), not just the body.
- **Meta description is always accurate:** The meta description check detects the real value seen by Google, even if it comes from global Metatag config, tokens (like `{node:summary}`), or defaults.
- **Real Schema Markup validation:** The JSON-LD check analyzes the actual rendered code, regardless of where it’s configured (global, per type, per node…).
- **More precise audits:** Results now always reflect the real frontend output, not just what’s in the node fields.

---

*Last updated: March 2026*
