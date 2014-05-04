Feature: Task definition
In order to have flexible tasks
As a user
I need to be able to define flags for my task

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    tasks:
        t:
            flags:
                foo: true
                bar: false
            do:
              - ?(foo) echo "foo is true"
              - ?(!foo) echo "foo is false"
              - ?(bar) echo "bar is true"
              - ?(!bar) echo "bar is false"
    """

  Scenario: Omitting the flags will use defaults
    When I run "z t"
    Then I should see text matching "/foo is true/"
    And  I should see text matching "/bar is false/"

  Scenario: Passing --no-* as flags will set to false
    When I run "z t --no-foo --no-bar"
    Then I should see text matching "/foo is false/"
    And  I should see text matching "/bar is false/"

  Scenario: Passing flags will set to true
    When I run "z t --foo --bar"
    Then I should see text matching "/foo is true/"
    And  I should see text matching "/bar is true/"

#  Scenario: Using the default value for a parameter
#    When I run "z t first"
#    Then I should see text matching "/the parameters contain 'first' and 'default'/"
#
#  Scenario: Using an overridden parameter
#    When I run "z t first second"
#    Then I should see text matching "/the parameters contain 'first' and 'second'/"
