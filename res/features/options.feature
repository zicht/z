Feature: Task definition
  In order to have flexible tasks
  As a user
  I need to be able to define flags for my task

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=2.0"

    tasks:
        t:
            opts:
                a: "xax"
                b: "xbx"
            do: echo $(a) $(b)
    """

  Scenario: Omitting the options will use defaults
    When I run "z t"
    Then I should see text matching "/xax xbx/"

  Scenario: Passing one of the options
    When I run "z t --a=foo"
    Then I should see text matching "/foo xbx/"

  Scenario: Passing the other of the options
    When I run "z t --b=bar"
    Then I should see text matching "/xax bar/"

  Scenario: Passing both options
    When I run "z t --b=bar --a=foo"
    Then I should see text matching "/foo bar/"
