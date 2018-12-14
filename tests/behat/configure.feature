@local @local_cohortrole
Feature: An admin can configure Cohort role synchronizations
  In order to configure Cohort role synchronizations
  As an admin
  I need to add and remove Cohort role synchronizations

  Background:
    Given the following "cohorts" exist:
      | name | idnumber |
      | Cohort One | C1 |
      | Cohort Two | C2 |
    And the following "roles" exist:
      | name | shortname |
      | Super Role | R1 |
    And the following Cohort role definitions exist:
      | cohort | role |
      | C1 | R1 |
    And I log in as "admin"
    And I navigate to "Users > Accounts > Cohort role synchronization" in site administration

  @javascript
  Scenario: Add new Cohort role definition
    When I press "Add"
    And I set the following fields to these values:
      | Cohort | Cohort Two |
      | Role | Super Role |
    And I click on "Save changes" "button"
    Then I should see "Created new synchronization"
    And the following should exist in the "local-cohortrole-summary-table" table:
      | Cohort | Role |
      | Cohort One | Super Role |
      | Cohort Two | Super Role |

  @javascript
  Scenario: Add duplicate Cohort role definition
    When I press "Add"
    And I set the following fields to these values:
      | Cohort | Cohort One |
      | Role | Super Role |
    And I click on "Save changes" "button"
    Then I should see "Synchronization already defined"
    And I click on "Cancel" "button"

  @javascript
  Scenario: Delete Cohort role definition
    When I click on "Delete" "link" in the "local-cohortrole-summary-table" "table"
    Then I should see "Are you sure you want to delete this synchronization?"
    And I click on "Continue" "button"
    And I should see "Deleted synchronization"
    And the following should not exist in the "local-cohortrole-summary-table" table:
      | Cohort | Role |
      | Cohort One | Super Role |
