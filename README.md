Moodle Cohort Role Synchronization
==================================

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
5. Users will automatically be (un)assigned to the role according to their membership of selected cohort.

Author
------
Paul Holden (pholden@greenhead.ac.uk)

- Updates: https://moodle.org/plugins/view.php?plugin=local_cohortrole
- Latest code: https://github.com/paulholden/moodle-local_cohortrole

Changes
-------
Release 1.3 (build 2014103102):
- Clean up synchronizations when role is deleted.
- Remove cron callback.

Release 1.2.1 (build 2014031302):
- Improved language strings/help.
- API updates.

Release 1.1.0 (build 2013112701):
- Added cron callback.

Release 1.0.0 (build 2013112600):
- First release.
