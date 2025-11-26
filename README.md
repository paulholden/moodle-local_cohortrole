# Cohort Role Synchronization - Extended for Category Cohorts

[![Moodle Plugin](https://img.shields.io/badge/Moodle%20Plugin-local__cohortrole-blue)](https://moodle.org/plugins/local_cohortrole)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Moodle 4.1+](https://img.shields.io/badge/Moodle-4.1%2B-orange)](https://moodle.org)

A Moodle local plugin to synchronize cohorts with system roles. **This fork extends the original plugin to support cohorts at the course category level**, not just system-level cohorts.

## About This Fork

This is a fork of [paulholden/moodle-local_cohortrole](https://github.com/paulholden/moodle-local_cohortrole).

### What's New in This Fork

The original plugin only supported cohorts defined at the **system level**. This fork adds:

- **Category-level cohort support** - Select cohorts from any course category
- **Grouped dropdown** - Cohorts are displayed grouped by context (System / Category)
- **Category path display** - Nested categories show full path (e.g., "Parent / Child / Subcategory")
- **Custom role support** - Improved role detection including custom roles
- **Context column** - Overview table shows where each cohort is located
- **German translation** - Full German language pack included

### Important Design Decision

**Roles are always assigned in the system context**, regardless of where the cohort is located. This ensures that system-level capabilities (like `moodle/site:uploadusers` or `moodle/user:create`) work correctly.

## Requirements

- Moodle 4.1 or later

## Installation

1. Download the plugin
2. Extract to your Moodle `/local/cohortrole` directory
3. Visit Site Administration → Notifications to complete the installation
4. Clear caches

## Usage

Once installed, you will find a new option in Site Administration:

**Users → Accounts → Cohort role synchronization**

### Setup Steps

1. Create cohorts at system level OR in course categories
2. Create or use existing roles that are assignable at system level
3. Visit the Cohort role synchronization page
4. Create a new synchronization by selecting:
   - A cohort (grouped by System / Category)
   - A role to assign
5. Save changes

Users will automatically be assigned/unassigned the role based on their cohort membership.

## Screenshots

### Cohort Selection (Grouped by Context)

The cohort dropdown now shows cohorts grouped by their context:

```
┌─────────────────────────────────┐
│ ▼ Cohort                        │
├─────────────────────────────────┤
│ System                          │
│   ├─ All Staff                  │
│   └─ Administrators             │
│ Category: Faculty of Science    │
│   ├─ Science Teachers           │
│   └─ Lab Assistants             │
│ Category: Faculty of Arts       │
│   └─ Arts Department            │
└─────────────────────────────────┘
```

### Overview Table with Context Column

| Cohort | Cohort Context | Role | Modified |
|--------|---------------|------|----------|
| Science Teachers | Category: Faculty of Science | Manager | 25 Nov 2025 |
| All Staff | System | Custom Role | 24 Nov 2025 |

## Changes from Original

### Modified Files

| File | Changes |
|------|---------|
| `locallib.php` | Role assignment always in system context |
| `classes/form/edit.php` | Load cohorts from system + categories, grouped display |
| `classes/persistent.php` | Extended validation for category cohorts |
| `classes/observers.php` | Event handlers respond to category-level events |
| `classes/output/summary_table.php` | New "Cohort context" column |
| `lang/en/local_cohortrole.php` | New language strings |
| `lang/de/local_cohortrole.php` | German translation (new) |
| `version.php` | Version updated to 2025112501 |

### New Language Strings

| Key | English | German |
|-----|---------|--------|
| `categorycontext` | Category: {$a} | Kategorie: {$a} |
| `cohortcontext` | Cohort context | Kontext der Gruppe |
| `systemcontext` | System | System |

## Technical Details

### How It Works

1. **Cohort Selection**: The form queries all cohorts from:
   - System context (`context_system`)
   - All course category contexts (`context_coursecat`)

2. **Role Assignment**: When a user is added to a cohort:
   - The plugin detects the cohort membership change via event observers
   - The role is assigned in the **system context** (always)
   - This ensures system-wide capabilities work correctly

3. **Role Removal**: When a user is removed from a cohort:
   - The corresponding role assignment is removed from the system context

### Event Observers

The plugin listens for these events (now in both system and category contexts):

- `\core\event\cohort_deleted`
- `\core\event\cohort_member_added`
- `\core\event\cohort_member_removed`
- `\core\event\role_deleted`

## Compatibility

- **Moodle**: 4.1 and later
- **PHP**: 7.4 and later (as required by Moodle)
- **Database**: No schema changes required (fully compatible with original)

## Credits

- **Original Author**: Paul Holden (paulh@moodle.com)
- **Original Plugin**: [moodle.org/plugins/local_cohortrole](https://moodle.org/plugins/local_cohortrole)

## License

This plugin is licensed under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).

## Changelog

### Version 5.1 (2025112501) - This Fork

- Added support for category-level cohorts
- Cohorts displayed in grouped dropdown by context
- New "Cohort context" column in overview table
- Added German translation
- Improved custom role detection
- Role assignment always in system context

### Previous Versions

See the [original repository](https://github.com/paulholden/moodle-local_cohortrole) for the complete changelog.

## Support

- **Issues**: Please report issues on GitHub
- **Original Plugin Support**: [Moodle Plugin Directory](https://moodle.org/plugins/local_cohortrole)

---
