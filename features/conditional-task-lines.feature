Feature: Using conditional task lines
  In order to be able to skip task lines
  As a user
  I need to be able to use expressions to skip lines


Background:
  Given I am in a test directory
  Given there is a file "z.yml"
  """
  # @version ">=1.0"

  tasks:
      t:
        args:
          a: ? "no"
        do:
          - @(if a == "yes") echo Yep
          - @(if a == "no")  echo Nope
  """

  Scenario:
    When I run "z t"
    Then I should see text matching "/Nope/"
    And I should not see text matching "/Yep/"

  Scenario:
    When I run "z t yes"
    Then I should not see text matching "/Nope/"
    And I should see text matching "/Yep/"

  Scenario:
    When I run "z t no"
    Then I should see text matching "/Nope/"
    And I should not see text matching "/Yep/"

