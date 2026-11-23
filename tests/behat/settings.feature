@gradepenalty @gradepenalty_cap
Feature: Set up the cap at the pass mark
  In order to apply our late work policy
  As an administrator
  I need to set the pass mark and the late window

  Scenario: The settings page appears once the plugin is enabled
    Given the following config values are set as admin:
      | gradepenalty_enabled_plugins | cap | core_grades |
    And I log in as "admin"
    When I navigate to "Grades > Grade penalties > Cap late work at the pass mark" in site administration
    And I set the following fields to these values:
      | Pass mark   | 50 |
      | Late window | 3  |
    And I press "Save changes"
    Then the following fields match these values:
      | Pass mark   | 50 |
      | Late window | 3  |
