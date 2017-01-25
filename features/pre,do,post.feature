Feature: Task execution order
In order to be able to have flexible tasks
As a user
I need to be able to define three different sections of execution

  Background:
    Given I am in a test directory

  Scenario: Runnning the task
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            pre:
                - echo "1"
                - echo "2"
            do:
                - echo "3"
                - echo "4"
            post:
                - echo "5"
                - echo "6"
    """
    When I run "z t"
    Then I should see text matching "/1\n2\n3\n4\n5\n6/"

  Scenario: Runnning the task would yield the same results, even if the order of post, pre and do are different
    And there is file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
            post:
                - echo "5"
                - echo "6"
            do:
                - echo "3"
                - echo "4"
            pre:
                - echo "1"
                - echo "2"
    """
    When I run "z t"
    Then I should see text matching "/1\n2\n3\n4\n5\n6/"
