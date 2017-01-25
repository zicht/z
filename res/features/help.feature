Feature: Task definition
In order to be able to assess available task and their parameters
As a user
I need to see what tasks are available and print their help

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            args:
                required_param: ?
                unrequired_param: ? "foo"
            help:
                Prints abc

                Shows you some text.
    """

  Scenario: Seeing the task is available
    When I run "z z:list"
    Then I should see text matching "/t\s+Prints abc/"

  Scenario: Seeing the task's help
    When I run "z z:help t"
    Then I should see text matching "/Prints abc/"
    And I should see text matching "/Shows you some text/"

  Scenario: Seeing the available parameters for the task
    When I run "z z:help t"
    Then I should see text matching "/(?!\[).<?required_param/"
    And I should see text matching "/\[<?unrequired_param>?\]/"
