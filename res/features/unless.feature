Feature: Skipping tasks
In order to be able to skip task
As a user
I need to be able to define a reason to skip a task

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            args:
                unless_value: ? "yes"
            unless: unless_value == "yes"
            pre:    echo "pre was executed"
            do:     echo "do was executed"
            post:   echo "post was executed"
    """

  Scenario: Running the task with the unless evaluating to true should run prerequisites, but not the body and post
    When I run "z t"
    Then I should see text matching "/t skipped/"
    And I should see text matching "/pre was executed/"
    But I should not see text matching "/do was executed/"
    And I should not see text matching "/post was executed/"

  Scenario: Running the task with the force parameter passed
    When I run "z t --force"
    Then I should not see text matching "/t skipped/"
    And I should see text matching "/pre was executed/"
    And I should see text matching "/do was executed/"
    And I should see text matching "/post was executed/"

  Scenario: Running the task with the unless parameter evaluating to false
    When I run "z t no"
    Then I should not see text matching "/t skipped/"
    And I should see text matching "/pre was executed/"
    And I should see text matching "/do was executed/"
    And I should see text matching "/post was executed/"
