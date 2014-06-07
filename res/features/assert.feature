Feature: Asserting task preconditions
In order to be able to fail a task
As a user
I need to be able to define a reason to fail a task

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            args:
                assert_value: ? "yes"
            assert: assert_value != "yes"
            pre:    echo "pre was executed"
            do:     echo "do was executed"
            post:   echo "post was executed"
    """

  Scenario: Running the task with the assert evaluating to true should not run script entirely
    When I run "z t"
    Then I should see text matching "/Assertion failed/"
    And I should see text matching "/pre was executed/"
    But I should not see text matching "/do was executed/"
    And I should not see text matching "/post was executed/"

  Scenario: Running the task with the unless parameter evaluating to false
    When I run "z t no"
    Then I should not see text matching "/Assertion failed/"
    And I should see text matching "/pre was executed/"
    And I should see text matching "/do was executed/"
    And I should see text matching "/post was executed/"
