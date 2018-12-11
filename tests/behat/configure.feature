@local @local_cohortrole
Feature: An admin can configure Cohort role synchronizations
  In order to configure Cohort role synchronizations
  As an admin
  I need to add and remove Cohort role synchronizations

  Background:
    Given the following "cohorts" exist:
      | name | idnumber |
      | Cohort One | C1 |
    And the following "roles" exist:
      | name | shortname |
      | Super Role | R1 |

  @javascript
  Scenario: Configure Cohort role synchronizations
    When I log in as "admin"
    And I navigate to "Users > Accounts > Cohort role synchronization" in site administration

    # Add new definition.
    And I press "Add"
    And I set the following fields to these values:
      | Cohort | Cohort One |
      | Role | Super Role |
    And I click on "Save changes" "button"
    Then I should see "Created new synchronization"
    And the following should exist in the "local-cohortrole-summary-table" table:
      | Cohort | Role |
      | Cohort One | Super Role |

    # Try to re-add same definition.
    And I press "Add"
    And I set the following fields to these values:
      | Cohort | Cohort One |
      | Role | Super Role |
    And I click on "Save changes" "button"
    Then I should see "Synchronization already defined"
    And I click on "Cancel" "button"

    # Delete it.
    And I click on "Delete" "link" in the "local-cohortrole-summary-table" "table"
    And I should see "Are you sure you want to delete this synchronization?"
    And I click on "Continue" "button"
    Then I should see "Deleted synchronization"
    And the following should not exist in the "local-cohortrole-summary-table" table:
      | Cohort | Role |
      | Cohort One | Super Role |
