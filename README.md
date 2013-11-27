Moodle Cohort Role Synchronization Plugin
=========================================

Requirements
------------
Moodle 2.6 (build 2013111800) or later.

Installation
------------
Copy the cohortrole folder into your Moodle /local directory and visit your Admin Notification page to complete the installation.

Once installed, you should see a new option in the Administration Block:

> Users -> Accounts -> Cohort role synchronization

Usage
-----
1. Create system-level cohort(s).
2. Create system-level assignable role(s) - optional, you can also use existing roles.
3. Visit Cohort role synchronization page.
4. Create new link between cohort and role.
5. Users will automatically by (un)assigned to the role according to their membership of selected cohort.

Author
------
Paul Holden (pholden@greenhead.ac.uk)

Changes
-------
Release 2013112701
- Added cron callback.
Release 2013112600
- First release.
