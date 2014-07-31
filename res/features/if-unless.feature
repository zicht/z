Feature: Skipping tasks
  In order to be able to limit the situations a task is run in
  As a user
  I need to be able to define a reason to run a task and a reason not to run a task.

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            args:
                if_value: ? "yes"
                unless_value: ? "yes"
            if:     if_value == "yes"
            unless: unless_value != "yes"
            do: echo t executed
    """

  Scenario:
    When I run "z t"
    Then I should not see text matching "/t skipped/"
    And  I should see text matching "/t executed/"

  Scenario:
    When I run "z t yes"
    Then I should not see text matching "/t skipped/"
    And  I should see text matching "/t executed/"

  Scenario:
    When I run "z t no"
    Then I should see text matching "/t skipped/"
    And  I should not see text matching "/t executed/"

  Scenario:
    When I run "z t yes no"
    Then I should see text matching "/t skipped/"
    And  I should not see text matching "/t executed/"

  Scenario:
    When I run "z t yes yes"
    Then I should not see text matching "/t skipped/"
    And  I should see text matching "/t executed/"

  Scenario:
    When I run "z t no no"
    Then I should see text matching "/t skipped/"
    And  I should not see text matching "/t executed/"

