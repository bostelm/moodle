@mod @mod_data
Feature: In a database with approval process, teachers need to approve student entries before they are visible
  In order to see other students entries
  As a student
  The entry first needs to be approved by a teacher

  Scenario: A teacher approves a student entry
    Given the following "users" exist:
      | username   | firstname | lastname | email                |
      | student1   | Student   | 1        | student1@example.com |
      | student2   | Student   | 2        | student2@example.com |
      | teacher1   | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | name               | intro | course | idnumber | approval |
      | data     | Test database name | n     | C1     | data1    | 1        |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I add a "Text input" field to "Test database name" database and I fill the form with:
      | Field name | Test field name |
      | Field description | Test field description |
    # To generate the default templates.
    And I follow "Templates"
    And I log out
    When I log in as "student1"
    And I follow "Course 1"
    And I add an entry to "Test database name" database with:
      | Test field name | Student entry A |
    And I press "Save and view"
    And I add an entry to "Test database name" database with:
      | Test field name | Student entry B |
    And I press "Save and view"
    And I follow "View list"
    Then I should see "Student entry A"
    And I should see "Student entry B"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Test database name"
    And I should see "Student entry A"
    And I should see "Student entry B"
    And I follow "View single"
    And I should see "Student entry A"
    And I follow "Approve"
    And I should see "Entry approved"
    And I should see "Student entry A"
    And "Undo approval" "link" should exist
    And I log out
    And I log in as "student2"
    And I follow "Course 1"
    And I follow "Test database name"
    And I follow "View list"
    And I should see "Student entry A"
    And I should not see "Student entry B"

  Scenario: A nonediting teacher views non-approved entries but cannot approve them
    Given the following "users" exist:
      | username   | firstname         | lastname | email                  |
      | student1   | Student           | 1        | student1@example.com   |
      | edteacher1 | EditingTeacher    | 1        | edteacher1@example.com |
      | neteacher1 | NoneditingTeacher | 1        | neteacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user       | course | role           |
      | edteacher1 | C1     | editingteacher |
      | neteacher1 | C1     | teacher        |
      | student1   | C1     | student        |
    And the following "activities" exist:
      | activity | name               | intro | course | idnumber | approval |
      | data     | Test database name | n     | C1     | data1    | 1        |
    And the following "permission overrides" exist:
      | capability       | permission | role           | contextlevel | reference |
      | mod/data:approve | Prevent    | teacher        | Course       | C1        |
    And I log in as "edteacher1"
    And I follow "Course 1"
    And I add a "Text input" field to "Test database name" database and I fill the form with:
      | Field name | Test field name |
      | Field description | Test field description |      
    # To generate the default templates.
    And I follow "Templates"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I add an entry to "Test database name" database with:
      | Test field name | Student entry A |
    And I press "Save and view"
    And I log out
    When I log in as "neteacher1"
    And I follow "Course 1"
    And I follow "Test database name"
    Then I should see "Student entry A"
    And "Approve" "link" should not exist
    And I log out
    And I log in as "edteacher1"
    And I follow "Course 1"
    And I follow "Test database name"
    And I should see "Student entry A"
    And "Approve" "link" should exist
