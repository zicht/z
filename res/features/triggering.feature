Feature: Task triggers
In order to trigger other tasks
As a user
I need to be able to trigger tasks with an @ prefix

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    tasks:
        t1:
            do: echo "2"
        t2:
            pre:    echo "1"
            do:     @t1
            post:   echo "3"
    """

  Scenario: Running t2
    When I run "z t2"
    Then I should see text matching "/1\n.*2\n.*3/"
