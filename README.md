# Cohort role synchronization [![Build Status](https://github.com/paulholden/moodle-local_cohortrole/workflows/moodle-plugin-ci/badge.svg)](https://github.com/paulholden/moodle-local_cohortrole/actions)

## Requirements

- Moodle 3.6 or later.

## Installation

Copy the cohortrole folder into your Moodle /local directory and visit your admin notification page to complete the installation.

Once installed, you should see a new option in your site administration:

> Users -> Accounts -> Cohort role synchronization

## Usage

1. Create system-level cohort(s).
2. Create system-level or category-level assignable role(s) - optional, you can also use existing roles.
3. Visit Cohort role synchronization page.
4. Create new link between cohort and role on system context or a specific course category.
5. Users will automatically be (un)assigned to the role within the specified context according to their membership of selected cohort.

## Author

Paul Holden (paulh@moodle.com)

- Updates: https://moodle.org/plugins/view.php?plugin=local_cohortrole
- Latest code: https://github.com/paulholden/moodle-local_cohortrole
