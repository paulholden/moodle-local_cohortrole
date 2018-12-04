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
    Given I log in as "admin"
    And I navigate to "Users > Accounts > Cohort role synchronization" in site administration
    And I press "Add"
    When I set the following fields to these values:
      | Cohort | Cohort One |
      | Role | Super Role |
    And I click on "Save changes" "button"
    Then I should see "Created new synchronization"
    And the following should exist in the "flexible" table:
      | Cohort | Role |
      | Cohort One | Super Role |
    And I click on "Delete" "link" in the "flexible" "table"
    And I should see "Are you sure you want to delete this synchronization?"
    And I click on "Continue" "button"
    And I should see "Deleted synchronization"
    And the following should not exist in the "flexible" table:
      | Cohort | Role |
      | Cohort One | Super Role |
