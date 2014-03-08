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
        t2:
            set:
                param: ? ""
            do: echo "X$(param)X"
        t3:
            set:
                param1: ? ""
                param2: param1 ? param1 : "foo"
            do: echo "X$(param1)X, Y$(param2)Y"
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

  Scenario: Using an empty default parameter
    When I run "z t2"
    Then I should see text matching "/XX/"

  Scenario: Using a dependent default parameter
    When I run "z t3"
    Then I should see text matching "/XX, YfooY/"

  Scenario: Using a dependent default parameter
    When I run "z t3 bar"
    Then I should see text matching "/XbarX, YbarY/"
