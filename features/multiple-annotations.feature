Feature: Implied parser features
  In order to support undocumented but clearly implied features
  As a maintainer
  I must be sure that those features stay available

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    # @version ">=2.0"

    tasks:
       t:
          args:
              a: ? "b"
              c: ? "d"
          do:
            - @(if a == "b") @(if c == "d") echo "mjup!"
    """

  Scenario: Conditionals are aggregate
    When I run "z t"
    Then I should see text matching "/mjup!/"

  Scenario: Conditionals are aggregate
    When I run "z t x"
    Then I should not see text matching "/mjup!/"

  Scenario: Conditionals are aggregate
    When I run "z t a y"
    Then I should not see text matching "/mjup!/"

  Scenario: Conditionals are aggregate
    When I run "z t b"
    Then I should see text matching "/mjup!/"

  Scenario: Conditionals are aggregate
    When I run "z t b d"
    Then I should see text matching "/mjup!/"

