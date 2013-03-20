Feature: Task definition
In order to have flexible tasks
As a user
I need to be able to define required and non-required parameters

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    tasks:
        t:
            set:
                param1: ?
                param2: ? "default"
            do: echo "the parameters contain '$(param1)' and '$(param2)'"
    """

  Scenario: Omitting a required value shows error message
    When I run "z t"
    Then I should see text matching "/Not enough arguments/"

  Scenario: Using the default value for a parameter
    When I run "z t first"
    Then I should see text matching "/the parameters contain 'first' and 'default'/"

  Scenario: Using an overridden parameter
    When I run "z t first second"
    Then I should see text matching "/the parameters contain 'first' and 'second'/"
